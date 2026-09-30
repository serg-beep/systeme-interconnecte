<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ActualiteController extends Controller
{
    // ── Admin : liste ──────────────────────────────
    public function index(Request $request)
    {
        $query = Actualite::latest('date_publication');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($b) => $b->where('titre', 'like', "%$q%")
                ->orWhere('source', 'like', "%$q%")
                ->orWhere('resume', 'like', "%$q%"));
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('publie')) {
            $query->where('publie', $request->boolean('publie'));
        }

        $actualites = $query->paginate((int) $request->get('per_page', 15));

        return response()->json($actualites);
    }

    // ── Admin : créer ──────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'            => 'required|string|max:255',
            'resume'           => 'required|string',
            'contenu'          => 'nullable|string',
            'image'            => 'nullable|image|max:3072',
            'categorie'        => 'required|in:burkina,afrique,monde',
            'source'           => 'required|string|max:100',
            'url_source'       => 'nullable|url',
            'url_externe'      => 'nullable|url',
            'publie'           => 'nullable|boolean',
            'date_publication' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::storeResized($request->file('image'), 'actualites');
        }

        $data['publie']       = $request->boolean('publie', false);
        $data['auto_fetched'] = false;
        $data['hash_dedup']   = Actualite::makeHash($data['titre'], $data['source']);
        $data['date_publication'] = $data['date_publication'] ?? now();

        $actualite = Actualite::create($data);

        return response()->json($actualite->fresh(), 201);
    }

    // ── Admin : modifier ───────────────────────────
    public function update(Request $request, int $id)
    {
        $actualite = Actualite::findOrFail($id);

        $data = $request->validate([
            'titre'            => 'sometimes|string|max:255',
            'resume'           => 'sometimes|string',
            'contenu'          => 'nullable|string',
            'image'            => 'nullable|image|max:3072',
            'categorie'        => 'sometimes|in:burkina,afrique,monde',
            'source'           => 'sometimes|string|max:100',
            'url_source'       => 'nullable|url',
            'url_externe'      => 'nullable|url',
            'publie'           => 'nullable|boolean',
            'date_publication' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            if ($actualite->image && !str_starts_with($actualite->image, 'http')) {
                Storage::disk('public')->delete($actualite->image);
            }
            $data['image'] = ImageOptimizer::storeResized($request->file('image'), 'actualites');
        }

        if (isset($data['publie'])) {
            $data['publie'] = $request->boolean('publie');
        }

        if (isset($data['titre']) || isset($data['source'])) {
            $data['hash_dedup'] = Actualite::makeHash(
                $data['titre']  ?? $actualite->titre,
                $data['source'] ?? $actualite->source
            );
        }

        $actualite->update($data);

        return response()->json($actualite->fresh());
    }

    // ── Admin : supprimer ──────────────────────────
    public function destroy($id)
    {
        $actualite = Actualite::findOrFail($id);

        if ($actualite->image && !str_starts_with($actualite->image, 'http')) {
            Storage::disk('public')->delete($actualite->image);
        }

        $actualite->delete();

        return response()->json(['message' => 'Actualité supprimée']);
    }

    // ── Admin : toggle publier/dépublier ───────────
    public function togglePublie($id)
    {
        $actualite = Actualite::findOrFail($id);
        $actualite->update(['publie' => !$actualite->publie]);

        return response()->json([
            'publie'  => $actualite->publie,
            'message' => $actualite->publie ? 'Actualité publiée' : 'Actualité dépubliée',
        ]);
    }

    // ── Admin : déclencher le fetch RSS ───────────
    public function fetchRss()
    {
        Artisan::call('actualites:fetch');
        $output = Artisan::output();
        return response()->json(['message' => 'Fetch terminé', 'output' => $output]);
    }

    // ── Public : liste paginée ─────────────────────
    public function indexPublic(Request $request)
    {
        $query = Actualite::where('publie', true)->latest('date_publication');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($b) => $b->where('titre', 'like', "%$q%")
                ->orWhere('resume', 'like', "%$q%")
                ->orWhere('source', 'like', "%$q%"));
        }

        if ($request->filled('categorie') && $request->categorie !== 'toutes') {
            $query->where('categorie', $request->categorie);
        }

        $perPage = min((int) $request->get('per_page', 12), 50);
        return response()->json($query->paginate($perPage));
    }

    // ── Public : détail ────────────────────────────
    public function showPublic($id)
    {
        $actualite = Actualite::where('publie', true)->findOrFail($id);
        return response()->json($actualite);
    }
}
