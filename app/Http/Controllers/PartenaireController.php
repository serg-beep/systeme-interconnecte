<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Historique_Status, Partenaire, Notification, User};

class PartenaireController extends Controller {

    public function index(Request $request) {
        $id = auth()->user()->entreprise_id;
        $query = Partenaire::with('entreprise','entreprisePartenaire')
            ->select('id','entreprise_id','entreprise_partenaire_id','statut','date_partenariat','created_at')
            ->where(function ($builder) use ($id) {
                $builder->where('entreprise_id', $id)
                    ->orWhere('entreprise_partenaire_id', $id);
            })
            ->when($request->filled('statut'), fn ($builder) => $builder->where('statut', $request->statut))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'entreprise_partenaire_id' => 'required|exists:entreprises,id',
        ]);
        $data['entreprise_id'] = auth()->user()->entreprise_id;

        // Vérifier si partenariat déjà existant
        $existe = Partenaire::where('entreprise_id', $data['entreprise_id'])
                            ->where('entreprise_partenaire_id', $data['entreprise_partenaire_id'])
                            ->exists();
        if ($existe) {
            return response()->json(['message' => 'Partenariat déjà existant'], 422);
        }

        $partenaire = Partenaire::create($data);

        // Notifier l'autre entreprise
        $adminIds = User::where('entreprise_id', $data['entreprise_partenaire_id'])->pluck('id');
        if ($adminIds->isNotEmpty()) {
            $now = now();
            Notification::insert($adminIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Demande de partenariat',
                'message'    => 'Vous avez reçu une demande de partenariat.',
                'type'       => 'partenariat',
                'lien'       => '/partenaires/'.$partenaire->id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }
        return response()->json($partenaire->load('entreprise','entreprisePartenaire'), 201);
    }

    public function show($id) {
        return response()->json(
            Partenaire::with('entreprise','entreprisePartenaire','historique.user')->findOrFail($id)
        );
    }

    public function update(Request $request, $id) {
        $partenaire = Partenaire::findOrFail($id);
        $partenaire->update($request->validate(['date_partenariat' => 'nullable|date']));
        return response()->json($partenaire);
    }

    public function destroy($id) {
        Partenaire::findOrFail($id)->delete();
        return response()->json(['message' => 'Partenariat supprimé']);
    }

    public function repondre(Request $request, $id) {
        $partenaire = Partenaire::findOrFail($id);
        $nouveau    = $request->validate(['statut' => 'required|in:accepte,refuse'])['statut'];

        Historique_Status::create([
            'user_id'        => auth()->id(),
            'partenaire_id'  => $partenaire->id,
            'ancien_statut'  => $partenaire->statut,
            'nouveau_statut' => $nouveau,
        ]);
        $partenaire->update([
            'statut'           => $nouveau,
            'date_partenariat' => $nouveau === 'accepte' ? now() : null,
        ]);

        // Notifier l'entreprise demandeuse
        $adminIds = User::where('entreprise_id', $partenaire->entreprise_id)->pluck('id');
        if ($adminIds->isNotEmpty()) {
            $now = now();
            Notification::insert($adminIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Partenariat '.$nouveau,
                'message'    => 'Votre demande de partenariat a été '.$nouveau,
                'type'       => 'partenariat',
                'lien'       => '/partenaires/'.$partenaire->id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }
        return response()->json(['message' => 'Partenariat '.$nouveau]);
    }

    public function historique($id) {
        return response()->json(
            Historique_Status::with('user')->where('partenaire_id', $id)->latest()->get()
        );
    }
}
