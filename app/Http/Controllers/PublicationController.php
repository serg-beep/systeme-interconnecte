<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Partenaire;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    // ── Site public : statistiques en temps réel ──
    public function siteStats()
    {
        return response()->json([
            [
                'icon'  => '📄',
                'label' => 'Publications',
                'value' => Publication::where('statut', 'publie')->where('visible_site', true)->count(),
            ],
            [
                'icon'  => '🏢',
                'label' => 'Etablissements inscrits',
                'value' => Entreprise::where('statut', 'actif')->count(),
            ],
            [
                'icon'  => '👥',
                'label' => 'Utilisateurs actifs',
                'value' => User::where('actif', true)->count(),
            ],
            [
                'icon'  => '🤝',
                'label' => 'Partenaires',
                'value' => Partenaire::count(),
            ],
        ]);
    }

    // ── Site public : fil de toutes les publications ──
    public function indexPublic(Request $request)
    {
        $query = Publication::with('user')
            ->where('statut', 'publie')
            ->where('visible_site', true);

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('titre',   'like', "%{$q}%")
                    ->orWhere('contenu', 'like', "%{$q}%");
            });
        }

        $tri = $request->query('tri', 'recent');
        if ($tri === 'alpha') {
            $query->orderBy('titre');
        } else {
            $query->latest();
        }

        $publications = $query->paginate((int) $request->query('per_page', 12));

        return response()->json($publications);
    }

    // ── Site public : une seule publication ──
    public function showPublic(int $id)
    {
        $publication = Publication::with([
            'user.entreprise',
            'commentairesApprouves',
        ])
        ->where('statut', 'publie')
        ->findOrFail($id);

        return response()->json($publication);
    }

    // ── Site public : profil d'un utilisateur + ses publications ──
    public function profilPublic(int $userId)
    {
        $user = User::findOrFail($userId);

        $publications = Publication::where('user_id', $userId)
            ->where('statut', 'publie')
            ->where('visible_site', true)
            ->latest()
            ->paginate(10);

        return response()->json([
            'user'         => $user->only('id', 'nom', 'prenom', 'email'),
            'publications' => $publications,
        ]);
    }

    // ── Logiciel : mes publications ──
    public function index(Request $request)
    {
        $query = Publication::where('user_id', auth()->id())->latest();

        return $this->listResponse($query, $request, 20, 100);
    }

    // ── Logiciel : créer une publication (auth requise) ──
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'            => 'required|string|max:255',
            'type_publication' => 'nullable|string|max:100',
            'contenu'          => 'required|string',
            'image'            => 'nullable|image|max:2048',
            'statut'           => 'nullable|in:publie,brouillon',
            'visible_site'     => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::storeResized($request->file('image'), 'publications');
        }

        $data['user_id']      = auth()->id();
        $data['statut']       = $data['statut'] ?? 'publie';
        $data['visible_site'] = $data['visible_site'] ?? false;

        $publication = Publication::create($data);

        return response()->json($publication->fresh(), 201);
    }

    // ── Logiciel : modifier une publication ──
    public function update(Request $request, int $id)
    {
        $publication = Publication::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'titre'        => 'sometimes|string|max:255',
            'contenu'      => 'sometimes|string',
            'image'        => 'nullable|image|max:2048',
            'statut'       => 'sometimes|in:publie,brouillon,archive',
            'visible_site' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::storeResized($request->file('image'), 'publications');
        }

        $publication->update($data);

        return response()->json($publication);
    }

    // ── Logiciel : supprimer une publication ──
    public function destroy(int $id)
    {
        $publication = Publication::where('user_id', auth()->id())->findOrFail($id);
        $publication->delete();

        return response()->json(['message' => 'Publication supprimée']);
    }
}
