<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Reponse, Notification, Requete, Demande};

class ReponseController extends Controller {

    public function store(Request $request) {
        $data = $request->validate([
            'requete_id'   => 'nullable|exists:requetes,id',
            'demande_id'   => 'nullable|exists:demandes,id',
            'contenu'      => 'required|string',
            'piece_jointe' => 'nullable|string',
        ]);

        if (empty($data['requete_id']) && empty($data['demande_id'])) {
            return response()->json([
                'message' => 'Une reponse doit etre liee a une requete ou une demande.'
            ], 422);
        }

        $data['user_id']       = auth()->id();
        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $reponse = Reponse::create($data);

        if (!empty($data['requete_id'])) {
            $requete = Requete::find($data['requete_id']);

            if ($requete && $requete->user_id !== auth()->id()) {
                Notification::create([
                    'user_id' => $requete->user_id,
                    'titre'   => 'Nouvelle reponse a votre requete',
                    'message' => 'Une entreprise a repondu a votre requete "'.$requete->titre.'".',
                    'type'    => 'reponse',
                    'lien'    => '/app/requetes?id='.$requete->id,
                ]);
            }
        }

        if (!empty($data['demande_id'])) {
            $demande = Demande::find($data['demande_id']);

            if ($demande && $demande->user_id !== auth()->id()) {
                Notification::create([
                    'user_id' => $demande->user_id,
                    'titre'   => 'Nouvelle reponse a votre demande',
                    'message' => 'Une entreprise a repondu a votre demande "'.$demande->titre.'".',
                    'type'    => 'reponse',
                    'lien'    => '/app/demandes?id='.$demande->id,
                ]);
            }
        }

        return response()->json($reponse->load('user','entreprise'), 201);
    }

    public function show($id) {
        return response()->json(Reponse::with('user','entreprise')->findOrFail($id));
    }

    public function marquerLue($id) {
        Reponse::findOrFail($id)->update(['statut' => 'lue']);
        return response()->json(['message' => 'Réponse marquée comme lue']);
    }

    public function archiver($id) {
        Reponse::findOrFail($id)->update(['statut' => 'archivee']);
        return response()->json(['message' => 'Réponse archivée']);
    }
}
