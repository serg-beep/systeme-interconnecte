<?php

namespace App\Console\Commands;

use App\Models\Actualite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchActualitesSante extends Command
{
    protected $signature   = 'actualites:fetch {--force : Forcer même si récemment exécuté}';
    protected $description = 'Récupère les actualités santé depuis les sources RSS (OMS, UNICEF, Africa CDC, etc.)';

    // Image locale par source (différente pour chaque source)
    private array $sources = [
        // ── Burkina Faso (Google News temps réel) ──
        [
            'url'       => 'https://news.google.com/rss/search?q=sant%C3%A9+Burkina+Faso&hl=fr&gl=BF&ceid=BF:fr',
            'source'    => 'Actualités Santé — Burkina Faso',
            'categorie' => 'burkina',
            'image'     => 'actualites/src-burkina1.jpg',
        ],
        [
            'url'       => 'https://news.google.com/rss/search?q=minist%C3%A8re+sant%C3%A9+Burkina&hl=fr&gl=BF&ceid=BF:fr',
            'source'    => 'Ministère de la Santé — Burkina Faso',
            'categorie' => 'burkina',
            'image'     => 'actualites/src-burkina2.jpg',
        ],
        [
            'url'       => 'https://news.google.com/rss/search?q=h%C3%B4pital+m%C3%A9decine+Ouagadougou&hl=fr&gl=BF&ceid=BF:fr',
            'source'    => 'Santé Ouagadougou',
            'categorie' => 'burkina',
            'image'     => 'actualites/src-burkina3.jpg',
        ],
        // ── Afrique ──
        [
            'url'       => 'https://news.google.com/rss/search?q=sant%C3%A9+Afrique+OMS&hl=fr&gl=FR&ceid=FR:fr',
            'source'    => 'Santé Afrique',
            'categorie' => 'afrique',
            'image'     => 'actualites/src-afrique1.jpg',
        ],
        [
            'url'       => 'https://news.google.com/rss/search?q=paludisme+vaccination+Afrique&hl=fr&gl=FR&ceid=FR:fr',
            'source'    => 'Santé Afrique — Vaccination',
            'categorie' => 'afrique',
            'image'     => 'actualites/src-afrique2.jpg',
        ],
        // ── Monde ──
        [
            'url'       => 'https://www.who.int/rss-feeds/news-english.xml',
            'source'    => 'OMS — Organisation Mondiale de la Santé',
            'categorie' => 'monde',
            'image'     => 'actualites/src-oms.jpg',
        ],
        [
            'url'       => 'https://news.google.com/rss/search?q=UNICEF+sant%C3%A9+enfants+Afrique&hl=fr&gl=FR&ceid=FR:fr',
            'source'    => 'UNICEF',
            'categorie' => 'monde',
            'image'     => 'actualites/src-unicef.jpg',
        ],
    ];

    public function handle(): int
    {
        $this->info('Démarrage de la récupération des actualités santé...');

        $total    = 0;
        $nouveaux = 0;
        $erreurs  = 0;

        // Les sources sont interrogées en parallèle (au lieu d'une boucle séquentielle) :
        // avec 7 sources externes et jusqu'à 15s de timeout chacune, un enchaînement
        // séquentiel pouvait bloquer le serveur de dev (mono-thread) pendant plus de 20s.
        $reponses = Http::pool(function ($pool) {
            foreach ($this->sources as $i => $source) {
                $pool->as((string) $i)
                    ->timeout(15)
                    ->withHeaders([
                        'User-Agent'      => 'Mozilla/5.0 (compatible; SIS-HealthBot/1.0)',
                        'Accept'          => 'application/rss+xml, application/xml, text/xml, */*',
                        'Accept-Language' => 'fr-FR,fr;q=0.9',
                    ])
                    ->get($source['url']);
            }
        });

        foreach ($this->sources as $i => $source) {
            $this->line("  → {$source['source']}");

            try {
                $reponse = $reponses[(string) $i];

                if ($reponse instanceof \Throwable) {
                    throw $reponse;
                }
                if (!$reponse->successful()) {
                    throw new \RuntimeException("HTTP {$reponse->status()} pour {$source['url']}");
                }

                $articles = $this->parserRss($reponse->body());

                foreach ($articles as $article) {
                    $total++;
                    $hash = Actualite::makeHash($article['titre'], $source['source']);

                    if (Actualite::where('hash_dedup', $hash)->exists()) {
                        continue;
                    }

                    $imagePath = $source['image'];

                    Actualite::create([
                        'titre'            => $article['titre'],
                        'resume'           => $article['resume'],
                        'image'            => $imagePath,
                        'categorie'        => $source['categorie'],
                        'source'           => $source['source'],
                        'url_source'       => $source['url'],
                        'url_externe'      => $article['lien'],
                        'publie'           => true,
                        'auto_fetched'     => true,
                        'hash_dedup'       => $hash,
                        'date_publication' => $article['date'] ?? now(),
                    ]);

                    $nouveaux++;
                }

                $this->line("    ✓ " . count($articles) . " articles traités");
            } catch (\Throwable $e) {
                $erreurs++;
                $this->warn("    ✗ Erreur : " . $e->getMessage());
                Log::warning("FetchActualitesSante [{$source['source']}] : " . $e->getMessage());
            }
        }

        $this->info("Terminé — {$nouveaux} nouvelles / {$total} traitées / {$erreurs} erreur(s)");
        return Command::SUCCESS;
    }

    private function telechargerImage(?string $url, string $categorie = 'monde'): ?string
    {
        if (!$url || !str_starts_with($url, 'http')) {
            return $this->defaultImages[$categorie] ?? $this->defaultImages['monde'];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SIS-HealthBot/1.0)'])
                ->get($url);

            if (!$response->successful()) {
                return $this->defaultImages[$categorie] ?? $this->defaultImages['monde'];
            }

            $contentType = $response->header('Content-Type') ?? '';
            $ext = match (true) {
                str_contains($contentType, 'png')  => 'png',
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'gif')  => 'gif',
                default                            => 'jpg',
            };

            $nom = 'actualites/' . Str::random(32) . '.' . $ext;
            Storage::disk('public')->put($nom, $response->body());

            return $nom;
        } catch (\Throwable) {
            return $this->defaultImages[$categorie] ?? $this->defaultImages['monde'];
        }
    }

    private function parserRss(string $body): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

        if ($xml === false) {
            throw new \RuntimeException('XML invalide');
        }

        // Namespace media pour les images
        $media = $xml->getNamespaces(true);

        $articles = [];
        $items    = $xml->channel->item ?? $xml->entry ?? [];

        foreach ($items as $item) {
            $titre = trim(strip_tags(html_entity_decode((string) ($item->title ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (!$titre) continue;
            // Supprimer le "- Source" que Google News ajoute en fin de titre
            $titre = preg_replace('/\s+-\s+[^-]{3,60}$/', '', $titre);
            $titre = mb_substr(trim($titre), 0, 250);

            // Résumé — nettoyage HTML + entités
            $descRaw = (string) ($item->description ?? $item->summary ?? $item->content ?? '');
            $resume  = trim(strip_tags(html_entity_decode($descRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            // Google News met le titre + source dans description — on prend juste la partie utile
            if (str_contains($resume, '  ')) {
                $resume = trim(explode('  ', $resume)[0]);
            }
            if (strlen($resume) < 20) $resume = $titre;
            $resume = Str::limit($resume, 400);

            // Lien
            $lien = trim((string) ($item->link ?? $item->id ?? ''));
            if (is_object($item->link) && isset($item->link['href'])) {
                $lien = (string) $item->link['href'];
            }
            // Google News : l'URL réelle est dans le lien direct
            if (str_contains($lien, 'news.google.com') && str_contains($lien, 'articles/')) {
                // Garder l'URL Google News comme url_externe
            }

            // Date
            $dateStr = (string) ($item->pubDate ?? $item->published ?? $item->updated ?? '');
            try {
                $date = $dateStr ? new \DateTime($dateStr) : new \DateTime();
            } catch (\Throwable) {
                $date = new \DateTime();
            }

            // Image (enclosure, media:content, media:thumbnail)
            $image = null;
            if (isset($item->enclosure) && str_starts_with((string) ($item->enclosure['type'] ?? ''), 'image')) {
                $image = (string) $item->enclosure['url'];
            }
            if (!$image && isset($media['media'])) {
                $mediaEl = $item->children($media['media']);
                if (isset($mediaEl->content['url'])) {
                    $image = (string) $mediaEl->content['url'];
                } elseif (isset($mediaEl->thumbnail['url'])) {
                    $image = (string) $mediaEl->thumbnail['url'];
                }
            }

            $articles[] = [
                'titre'  => Str::limit($titre, 255),
                'resume' => $resume,
                'lien'   => $lien,
                'image'  => $image,
                'date'   => $date,
            ];

            if (count($articles) >= 15) break;
        }

        return $articles;
    }
}
