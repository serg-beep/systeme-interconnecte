<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\Historique_Status;
use App\Models\User;
use App\Models\Notification;

class RequeteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Requete::with(['entreprise:id,nom'])
            ->select('id', 'entreprise_id', 'user_id', 'titre', 'description', 'type', 'statut', 'created_at')
            ->when($request->filled('statut'), fn ($builder) => $builder->where('statut', $request->statut), fn ($builder) => $builder->where('statut', 'ouverte'))
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = $request->q;

                $builder->where(function ($inner) use ($search) {
                    $inner->where('titre', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($request->boolean('sans_reponse', true), fn ($builder) => $builder->doesntHave('reponses'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $data = $request->validate([
            'titre'       => 'required|string',
            'description' => 'nullable|string',
            'type'        => 'required|in:information,ressource,collaboration,autre',
        ]);
        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['user_id']       = auth()->id();
        $requete = Requete::create($data);

        $destinataireIds = User::where('entreprise_id', '!=', $data['entreprise_id'])
            ->where('actif', true)
            ->pluck('id');

        if ($destinataireIds->isNotEmpty()) {
            $now = now();
            Notification::insert($destinataireIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Nouvelle requete publiee',
                'message'    => 'Une nouvelle requete est disponible : '.$requete->titre,
                'type'       => 'demande',
                'lien'       => '/requetes/'.$requete->id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }

        return response()->json($requete->load('entreprise','user')->loadCount('reponses'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         return response()->json(
            Requete::with([
                'entreprise:id,nom',
                'user:id,prenom,nom',
                'reponses.user:id,prenom,nom',
                'historique.user:id,prenom,nom',
            ])->findOrFail($id)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
          $requete = Requete::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $requete->update($request->validate([
            'titre'       => 'sometimes|string',
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:information,ressource,collaboration,autre',
        ]));
        return response()->json($requete->load('entreprise','user')->loadCount('reponses'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Requete::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Requête supprimée']);
    }
    public function fermer($id) {
        $requete = Requete::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        Historique_Status::create([
            'user_id'        => auth()->id(),
            'requete_id'     => $requete->id,
            'ancien_statut'  => $requete->statut,
            'nouveau_statut' => 'fermee',
        ]);
        $requete->update(['statut' => 'fermee']);
        return response()->json(['message' => 'Requête fermée']);
    }

    public function expirer($id) {
        $requete = Requete::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        Historique_Status::create([
            'user_id'        => auth()->id(),
            'requete_id'     => $requete->id,
            'ancien_statut'  => $requete->statut,
            'nouveau_statut' => 'expiree',
        ]);
        $requete->update(['statut' => 'expiree']);
        return response()->json(['message' => 'Requête expirée']);
    }

    public function historique($id) {
        Requete::findOrFail($id);

        return response()->json(
            Historique_Status::with('user')->where('requete_id', $id)->latest()->get()
        );
    }
}
