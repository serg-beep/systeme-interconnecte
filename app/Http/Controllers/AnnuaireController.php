<?php

namespace App\Http\Controllers;

use App\Models\Annuaire;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AnnuaireController extends Controller
{
    // ── App (authentifié) ─────────────────────

    public function index(Request $request)
    {
        $query = Annuaire::with('entreprise')
            ->select('id','entreprise_id','service','description','disponible','categorie','cover_image','visit_url','tags','ville','etat_publication','vues','created_at')
            ->where('entreprise_id', auth()->user()->entreprise_id)
            ->when($request->has('disponible'), fn ($b) => $b->where('disponible', $request->boolean('disponible')))
            ->when($request->filled('q'), function ($b) use ($request) {
                $q = $request->q;
                $b->where(fn ($i) => $i->where('service', 'like', "%$q%")->orWhere('description', 'like', "%$q%"));
            })
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    // ── Site public ───────────────────────────

    public function indexPublic(Request $request)
    {
        $query = Annuaire::with('entreprise:id,nom,type,logo,ville,description')
            ->select('id','entreprise_id','service','description','disponible','categorie','cover_image','visit_url','tags','ville','etat_publication','vues','created_at')
            ->withCount('likes')
            ->where('etat_publication', 'publie');

        // Recherche textuelle
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($inner) use ($q) {
                $inner->where('service', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%")
                      ->orWhere('categorie', 'like', "%$q%")
                      ->orWhereJsonContains('tags', $q)
                      ->orWhereHas('entreprise', fn ($e) => $e->where('nom', 'like', "%$q%")->orWhere('ville', 'like', "%$q%"));
            });
        }

        // Filtre catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        // Filtre disponibilité (true par défaut sur le site public)
        if ($request->boolean('disponible', true)) {
            $query->where('disponible', true);
        }

        // Filtre ville
        if ($request->filled('ville')) {
            $query->where(fn ($b) => $b->where('ville', 'like', "%{$request->ville}%")
                ->orWhereHas('entreprise', fn ($e) => $e->where('ville', 'like', "%{$request->ville}%")));
        }

        // Tri
        match ($request->get('tri', 'recent')) {
            'vues'   => $query->orderByDesc('vues'),
            'alpha'  => $query->orderBy('service'),
            default  => $query->latest(),
        };

        // Pagination forcée pour le site public
        $perPage = min((int) $request->integer('per_page', 12), 50);
        $paginated = $query->paginate($perPage);

        $this->attacherLikedByMe($paginated->getCollection(), 'annuaire');

        return response()->json($paginated);
    }

    public function showPublic(Request $request, $id)
    {
        $service = Annuaire::with([
            'entreprise:id,nom,type,logo,ville,description,email,telephone,adresse',
            'commentaires.user:id,prenom,nom',
            'avis.user:id,nom,prenom',
        ])
            ->withCount('likes')
            ->where('etat_publication', 'publie')
            ->findOrFail($id);

        $service->note_moyenne = round($service->avis->avg('note'), 1) ?: null;

        $this->attacherLikedByMe(collect([$service]), 'annuaire');

        if ($service->entreprise) {
            $service->entreprise->suivi_par_moi = $service->entreprise->estSuiviPar(auth('sanctum')->user());
        }

        // Incrémenter les vues
        $service->increment('vues');

        // Autres services de la même entreprise
        $autresServices = Annuaire::where('entreprise_id', $service->entreprise_id)
            ->where('id', '!=', $service->id)
            ->where('etat_publication', 'publie')
            ->where('disponible', true)
            ->select('id', 'service', 'categorie', 'disponible')
            ->limit(5)
            ->get();

        return response()->json([
            'service'         => $service,
            'autres_services' => $autresServices,
        ]);
    }

    public function categories()
    {
        $cats = Cache::remember('annuaire:categories', 300, function () {
            return Annuaire::where('etat_publication', 'publie')
                ->whereNotNull('categorie')
                ->distinct()
                ->pluck('categorie')
                ->sort()
                ->values();
        });

        return response()->json($cats);
    }

    // ── App (authentifié) ─────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'service'          => 'required|string',
            'description'      => 'nullable|string',
            'disponible'       => 'boolean',
            'categorie'        => 'nullable|string|max:100',
            'cover_image'      => 'nullable|image|max:5120',
            'visit_url'        => 'nullable|url',
            'tags'             => 'nullable|string',
            'ville'            => 'nullable|string|max:100',
            'etat_publication' => 'in:publie,brouillon,archive',
        ]);

        if ($request->hasFile('cover_image')) {
            $path = ImageOptimizer::storeResized($request->file('cover_image'), 'annuaire/covers');
            $data['cover_image'] = Storage::url($path);
        }

        // Tags envoyés en JSON string depuis FormData
        if (isset($data['tags']) && is_string($data['tags'])) {
            $decoded = json_decode($data['tags'], true);
            $data['tags'] = is_array($decoded) ? $decoded : [];
        }

        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $annuaire = Annuaire::create($data);
        Cache::forget('annuaire:categories');

        return response()->json($annuaire->load('entreprise'), 201);
    }

    public function uploadImage(Request $request, $id)
    {
        $annuaire = $this->findOwnedAnnuaire($id);

        $request->validate(['image' => 'required|image|max:5120']);

        // Supprimer l'ancienne image
        if ($annuaire->cover_image) {
            $old = ltrim(str_replace('/storage', '', $annuaire->cover_image), '/');
            Storage::disk('public')->delete($old);
        }

        $path = ImageOptimizer::storeResized($request->file('image'), 'annuaire/covers');
        $url  = Storage::url($path);
        $annuaire->update(['cover_image' => $url]);

        return response()->json(['cover_image' => $url]);
    }

    public function update(Request $request, $id)
    {
        $annuaire = $this->findOwnedAnnuaire($id);

        $annuaire->update($request->validate([
            'service'          => 'sometimes|string',
            'description'      => 'nullable|string',
            'disponible'       => 'sometimes|boolean',
            'categorie'        => 'nullable|string|max:100',
            'cover_image'      => 'nullable|string',
            'visit_url'        => 'nullable|url',
            'tags'             => 'nullable|array',
            'tags.*'           => 'string|max:50',
            'ville'            => 'nullable|string|max:100',
            'etat_publication' => 'sometimes|in:publie,brouillon,archive',
        ]));
        Cache::forget('annuaire:categories');

        return response()->json($annuaire->load('entreprise'));
    }

    public function destroy($id)
    {
        $this->findOwnedAnnuaire($id)->delete();
        Cache::forget('annuaire:categories');

        return response()->json(['message' => 'Service supprimé']);
    }

    public function toggleDisponible($id)
    {
        $annuaire = $this->findOwnedAnnuaire($id);
        $annuaire->update(['disponible' => !$annuaire->disponible]);

        return response()->json([
            'message' => $annuaire->disponible ? 'Service disponible' : 'Service indisponible',
            'service' => $annuaire->load('entreprise'),
        ]);
    }

    private function findOwnedAnnuaire($id): Annuaire
    {
        $annuaire = Annuaire::findOrFail($id);

        abort_unless(
            (int) $annuaire->entreprise_id === (int) auth()->user()->entreprise_id,
            404
        );

        return $annuaire;
    }
}
