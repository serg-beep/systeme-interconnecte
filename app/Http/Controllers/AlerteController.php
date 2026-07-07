<?php

namespace App\Http\Controllers;

use App\Models\{Alerte, Entreprise, Notification, User};
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    public function index(Request $request)
    {
        $entrepriseId = auth()->user()->entreprise_id;

        $query = $this->visibleAlertes()
            ->select('alertes.id', 'alertes.entreprise_id', 'alertes.titre', 'alertes.message', 'alertes.type', 'alertes.priorite', 'alertes.created_at')
            ->with([
                'entreprise:id,nom',
                // Ne charger que le pivot de l'entreprise courante, pas toutes les destinataires
                'entreprisesDestinaires' => fn ($q) => $q
                    ->select('entreprises.id')
                    ->where('entreprises.id', $entrepriseId)
                    ->withPivot('accuse_reception'),
            ])
            ->when($request->filled('type'), fn ($builder) => $builder->where('alertes.type', $request->type))
            ->when($request->filled('priorite'), fn ($builder) => $builder->where('alertes.priorite', $request->priorite))
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = $request->q;
                $builder->where(function ($inner) use ($search) {
                    $inner->where('alertes.titre', 'like', '%'.$search.'%')
                        ->orWhere('alertes.message', 'like', '%'.$search.'%');
                });
            })
            ->latest('alertes.created_at');

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|in:rupture_stock,urgence,information,rappel',
            'priorite' => 'required|in:faible,normale,haute,critique',
        ]);

        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['user_id'] = auth()->id();

        $alerte = Alerte::create($data);
        $autres = Entreprise::where('id', '!=', $data['entreprise_id'])->select('id')->get();

        $alerte->entreprisesDestinaires()->attach(
            $autres->pluck('id')->mapWithKeys(fn ($id) => [
                $id => ['vue' => false, 'accuse_reception' => false],
            ])->toArray()
        );

        $destinataireIds = User::whereIn('entreprise_id', $autres->pluck('id'))
            ->where('actif', true)
            ->pluck('id');

        if ($destinataireIds->isNotEmpty()) {
            $now = now();
            Notification::insert($destinataireIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Nouvelle alerte : '.$alerte->titre,
                'message'    => $alerte->message,
                'type'       => 'alerte',
                'lien'       => '/alertes/'.$alerte->id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }

        return response()->json($alerte->load('entreprise', 'user', 'entreprisesDestinaires'), 201);
    }

    public function show($id)
    {
        return response()->json(
            $this->visibleAlertes()->with([
                'entreprise:id,nom',
                'user:id,prenom,nom',
                'entreprisesDestinaires:id,nom',
            ])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $alerte = Alerte::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $alerte->update($request->validate([
            'titre' => 'sometimes|string|max:255',
            'message' => 'sometimes|string|max:5000',
            'type' => 'sometimes|in:rupture_stock,urgence,information,rappel',
            'priorite' => 'sometimes|in:faible,normale,haute,critique',
        ]));

        return response()->json($alerte->load('entreprise', 'user', 'entreprisesDestinaires'));
    }

    public function destroy($id)
    {
        Alerte::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Alerte supprimee']);
    }

    public function accuser($id)
    {
        $alerte = Alerte::whereHas('entreprisesDestinaires', function ($query) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        })->findOrFail($id);

        $alerte->entreprisesDestinaires()->updateExistingPivot(
            auth()->user()->entreprise_id,
            ['vue' => true, 'date_vue' => now(), 'accuse_reception' => true]
        );

        return response()->json(['message' => 'Accuse de reception enregistre']);
    }

    public function destinaires($id)
    {
        $alerte = Alerte::where('entreprise_id', auth()->user()->entreprise_id)
            ->with('entreprisesDestinaires')
            ->findOrFail($id);

        return response()->json($alerte->entreprisesDestinaires);
    }

    private function visibleAlertes()
    {
        $id = auth()->user()->entreprise_id;

        // whereIn avec sous-requête scalaire = évaluée une seule fois (bien plus rapide que orWhereHas corrélatif)
        return Alerte::where(function ($q) use ($id) {
            $q->where('alertes.entreprise_id', $id)
              ->orWhereIn('alertes.id', function ($sub) use ($id) {
                  $sub->select('alerte_id')
                      ->from('alerte_entreprise')
                      ->where('entreprise_id', $id);
              });
        });
    }
}
