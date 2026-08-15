<?php

namespace App\Http\Controllers;

use App\Models\Annuaire;
use App\Models\Commentaire;
use App\Models\Publication;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    // ── Site : commenter une publication ou un service (compte requis) ──
    public function store(Request $request)
    {
        $data = $request->validate([
            'publication_id' => 'nullable|exists:publications,id',
            'annuaire_id'    => 'nullable|exists:annuaires,id',
            'contenu'        => 'required|string|max:1000',
        ]);

        if (empty($data['publication_id']) && empty($data['annuaire_id'])) {
            return response()->json(['message' => 'publication_id ou annuaire_id requis'], 422);
        }

        $user = auth()->user();
        $data += [
            'user_id' => $user->id,
            'nom_visiteur' => trim($user->prenom.' '.$user->nom),
            'email_visiteur' => $user->email,
            'statut' => 'approuve',
        ];

        $commentaire = Commentaire::create($data);

        if (!empty($data['annuaire_id'])) {
            $annuaire = Annuaire::with('entreprise.users')->find($data['annuaire_id']);
            foreach ($annuaire?->entreprise?->users ?? [] as $destinataire) {
                $this->notifier(
                    $destinataire,
                    'commentaire',
                    'Nouveau commentaire',
                    "{$data['nom_visiteur']} a commenté \"{$annuaire->service}\".",
                    "/service/{$annuaire->id}"
                );
            }
        } else {
            $publication = Publication::find($data['publication_id']);
            if ($publication) {
                $this->notifier(
                    $publication->user,
                    'commentaire',
                    'Nouveau commentaire',
                    "{$data['nom_visiteur']} a commenté votre publication \"{$publication->titre}\".",
                    "/publication/{$publication->id}"
                );
            }
        }

        return response()->json(['message' => 'Commentaire publié.', 'commentaire' => $commentaire], 201);
    }

    public function index(Request $request)
    {
        return response()->json(Commentaire::with(['user:id,prenom,nom', 'annuaire:id,service', 'publication:id,titre'])
            ->when($request->statut, fn ($q) => $q->where('statut', $request->statut))
            ->latest()->paginate(20));
    }

    public function approuver($id) { Commentaire::findOrFail($id)->update(['statut' => 'approuve']); return response()->json(['message' => 'Commentaire approuvé']); }
    public function rejeter($id) { Commentaire::findOrFail($id)->update(['statut' => 'rejete']); return response()->json(['message' => 'Commentaire rejeté']); }
    public function destroy($id) { Commentaire::findOrFail($id)->delete(); return response()->json(['message' => 'Commentaire supprimé']); }
}
