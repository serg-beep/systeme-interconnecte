<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Rapport;

class RapportController extends Controller {

    public function index(Request $request) {
        $query = Rapport::where('entreprise_id', auth()->user()->entreprise_id)
            ->select('id','entreprise_id','user_id','titre','type','created_at')
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('q'), fn ($builder) => $builder->where('titre', 'like', '%'.$request->q.'%'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'titre'         => 'required|string',
            'type'          => 'required|in:stock,activite,echange,statistique',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
            'contenu'       => 'nullable|string',
            'fichier'       => 'nullable|string',
        ]);
        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['user_id'] = auth()->id();
        return response()->json(Rapport::create($data), 201);
    }

    public function show($id) {
        return response()->json(Rapport::with('entreprise','user')->where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id));
    }

    public function update(Request $request, $id) {
        $rapport = Rapport::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $rapport->update($request->validate([
            'titre'   => 'sometimes|string',
            'contenu' => 'nullable|string',
            'fichier' => 'nullable|string',
        ]));
        return response()->json($rapport);
    }

    public function destroy($id) {
        Rapport::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Rapport supprimé']);
    }

    public function telecharger($id) {
        $rapport = Rapport::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        return response()->json(['fichier' => $rapport->fichier]);
    }
}
