<?php

namespace App\Http\Controllers;

use App\Models\Annuaire;
use App\Models\AvisService;
use Illuminate\Http\Request;

class AvisServiceController extends Controller
{
    // ── Poster un avis sur un service (auth requis, un seul avis par user/service) ──
    public function store(Request $request, int $annuaireId)
    {
        $annuaire = Annuaire::findOrFail($annuaireId);

        $data = $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        // Compte obligatoire pour laisser un avis (cf. auth:sanctum sur cette route) :
        // publication immédiate, la modération sert à retirer un avis a posteriori.
        $avis = AvisService::updateOrCreate(
            ['user_id' => auth()->id(), 'annuaire_id' => $annuaire->id],
            [
                'note'        => $data['note'],
                'commentaire' => $data['commentaire'] ?? null,
                'statut'      => 'approuve',
            ]
        );

        $auteur = trim(auth()->user()->prenom.' '.auth()->user()->nom);
        foreach ($annuaire->entreprise?->users ?? [] as $destinataire) {
            $this->notifier(
                $destinataire,
                'avis',
                'Nouvel avis',
                "{$auteur} a laissé un avis ({$data['note']}/5) sur \"{$annuaire->service}\".",
                "/service/{$annuaire->id}"
            );
        }

        return response()->json([
            'message' => 'Avis publié.',
            'avis'    => $avis,
        ], 201);
    }

    // ── Modération : file d'attente ──
    public function index(Request $request)
    {
        $avis = AvisService::with(['user:id,nom,prenom', 'annuaire:id,service'])
            ->when($request->statut, fn ($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);

        return response()->json($avis);
    }

    public function approuver($id)
    {
        AvisService::findOrFail($id)->update(['statut' => 'approuve']);
        return response()->json(['message' => 'Avis approuvé']);
    }

    public function rejeter($id)
    {
        AvisService::findOrFail($id)->update(['statut' => 'rejete']);
        return response()->json(['message' => 'Avis rejeté']);
    }

    public function destroy($id)
    {
        AvisService::findOrFail($id)->delete();
        return response()->json(['message' => 'Avis supprimé']);
    }
}
