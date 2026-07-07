<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use App\Models\Publication;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    // ── Site public : poster un commentaire (publication ou service) ──
    public function store(Request $request)
    {
        $data = $request->validate([
            'publication_id' => 'nullable|exists:publications,id',
            'annuaire_id'    => 'nullable|exists:annuaires,id',
            'nom_visiteur'   => 'required|string|max:100',
            'email_visiteur' => 'nullable|email|max:255',
            'contenu'        => 'required|string|max:1000',
        ]);

        if (empty($data['publication_id']) && empty($data['annuaire_id'])) {
            return response()->json(['message' => 'publication_id ou annuaire_id requis'], 422);
        }

        $data['statut'] = 'approuve';
        $commentaire = Commentaire::create($data);

        return response()->json([
            'message'     => 'Commentaire publié.',
            'commentaire' => $commentaire,
        ], 201);
    }

    // ── Logiciel : liste des commentaires à modérer ──
    public function index(Request $request)
    {
        $commentaires = Commentaire::with('publication:id,titre')
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);

        return response()->json($commentaires);
    }

    // ── Logiciel : approuver un commentaire ──
    public function approuver($id)
    {
        $commentaire = Commentaire::findOrFail($id);
        $commentaire->update(['statut' => 'approuve']);

        return response()->json(['message' => 'Commentaire approuvé']);
    }

    // ── Logiciel : rejeter un commentaire ──
    public function rejeter($id)
    {
        $commentaire = Commentaire::findOrFail($id);
        $commentaire->update(['statut' => 'rejete']);

        return response()->json(['message' => 'Commentaire rejeté']);
    }

    // ── Logiciel : supprimer un commentaire ──
    public function destroy($id)
    {
        Commentaire::findOrFail($id)->delete();

        return response()->json(['message' => 'Commentaire supprimé']);
    }
}
