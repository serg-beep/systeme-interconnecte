<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Historique_Status;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemandeController extends Controller
{
    public function index(Request $request)
    {
        $id = auth()->user()->entreprise_id;
        $query = $this->visibleDemandes()
            ->select('id', 'entreprise_source_id', 'entreprise_cible_id', 'user_id', 'titre', 'description', 'type', 'statut', 'created_at')
            ->with(['entrepriseSource:id,nom', 'entrepriseCible:id,nom'])
            ->when($request->filled('statut'), fn ($builder) => $builder->where('statut', $request->statut))
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('sens'), function ($builder) use ($request, $id) {
                if ($request->sens === 'recues') {
                    $builder->where('entreprise_cible_id', $id);
                }

                if ($request->sens === 'envoyees') {
                    $builder->where('entreprise_source_id', $id);
                }
            })
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = $request->q;

                $builder->where(function ($inner) use ($search) {
                    $inner->where('titre', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entreprise_cible_id' => [
                'required',
                'exists:entreprises,id',
                Rule::notIn([auth()->user()->entreprise_id]),
            ],
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'type' => 'required|in:stock,service,partenariat,donnees,rapport',
        ]);

        $data['entreprise_source_id'] = auth()->user()->entreprise_id;
        $data['user_id'] = auth()->id();
        $demande = Demande::create($data);

        $destinataireIds = User::where('entreprise_id', $data['entreprise_cible_id'])
            ->where('actif', true)
            ->pluck('id');

        if ($destinataireIds->isNotEmpty()) {
            $now = now();
            Notification::insert($destinataireIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Nouvelle demande recue',
                'message'    => 'Vous avez recu une demande : '.$demande->titre,
                'type'       => 'demande',
                'lien'       => '/demandes/'.$demande->id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }

        return response()->json($demande->load('entrepriseSource', 'entrepriseCible', 'user'), 201);
    }

    public function show($id)
    {
        return response()->json(
            $this->visibleDemandes()
                ->with([
                    'entrepriseSource:id,nom',
                    'entrepriseCible:id,nom',
                    'user:id,prenom,nom',
                    'reponses.user:id,prenom,nom',
                    'historique.user:id,prenom,nom',
                ])
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $demande = Demande::where('entreprise_source_id', auth()->user()->entreprise_id)->findOrFail($id);
        $demande->update($request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'type' => 'sometimes|in:stock,service,partenariat,donnees,rapport',
        ]));

        return response()->json($demande->load('entrepriseSource', 'entrepriseCible', 'user'));
    }

    public function destroy($id)
    {
        Demande::where('entreprise_source_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Demande supprimee']);
    }

    public function repondre(Request $request, $id)
    {
        $demande = Demande::where('entreprise_cible_id', auth()->user()->entreprise_id)->findOrFail($id);
        $nouveau = $request->validate(['statut' => 'required|in:acceptee,refusee'])['statut'];

        Historique_Status::create([
            'user_id' => auth()->id(),
            'demande_id' => $demande->id,
            'ancien_statut' => $demande->statut,
            'nouveau_statut' => $nouveau,
        ]);

        $demande->update(['statut' => $nouveau]);

        if ($user = User::find($demande->user_id)) {
            Notification::create([
                'user_id' => $user->id,
                'titre' => 'Demande '.$nouveau,
                'message' => 'Votre demande "'.$demande->titre.'" a ete '.$nouveau,
                'type' => 'demande',
                'lien' => '/demandes/'.$demande->id,
            ]);
        }

        return response()->json(['message' => 'Demande mise a jour']);
    }

    public function annuler($id)
    {
        $demande = Demande::where('entreprise_source_id', auth()->user()->entreprise_id)->findOrFail($id);

        Historique_Status::create([
            'user_id' => auth()->id(),
            'demande_id' => $demande->id,
            'ancien_statut' => $demande->statut,
            'nouveau_statut' => 'annulee',
        ]);

        $demande->update(['statut' => 'annulee']);

        return response()->json(['message' => 'Demande annulee']);
    }

    public function historique($id)
    {
        $this->visibleDemandes()->findOrFail($id);

        return response()->json(
            Historique_Status::with('user')->where('demande_id', $id)->latest()->get()
        );
    }

    private function visibleDemandes()
    {
        $id = auth()->user()->entreprise_id;

        return Demande::where(function ($builder) use ($id) {
            $builder->where('entreprise_source_id', $id)
                ->orWhere('entreprise_cible_id', $id);
        });
    }
}
