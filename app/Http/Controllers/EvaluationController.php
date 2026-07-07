<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Evaluation;

class EvaluationController extends Controller {

    public function index(Request $request) {
        $query = Evaluation::with('entreprise','entrepriseEvaluee','user')
            ->select('id','entreprise_id','entreprise_evaluee_id','user_id','note','commentaire','created_at')
            ->where('entreprise_id', auth()->user()->entreprise_id)
            ->when($request->filled('note'), fn ($builder) => $builder->where('note', $request->note))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'entreprise_evaluee_id' => 'required|exists:entreprises,id',
            'note'                  => 'required|integer|min:1|max:5',
            'commentaire'           => 'nullable|string',
        ]);
        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['user_id'] = auth()->id();
        return response()->json(Evaluation::create($data), 201);
    }

    public function show($id) {
        return response()->json(Evaluation::with('entreprise','entrepriseEvaluee','user')->where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id));
    }

    public function update(Request $request, $id) {
        $eval = Evaluation::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $eval->update($request->validate([
            'note'        => 'sometimes|integer|min:1|max:5',
            'commentaire' => 'nullable|string',
        ]));
        return response()->json($eval);
    }

    public function destroy($id) {
        Evaluation::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Évaluation supprimée']);
    }

    public function parEntreprise($id) {
        $evaluations = Evaluation::where('entreprise_evaluee_id', $id)
            ->select('id','entreprise_id','entreprise_evaluee_id','user_id','note','commentaire','created_at')
            ->with(['entreprise:id,nom', 'user:id,prenom,nom'])
            ->latest()->get();
        $moyenne = $evaluations->avg('note');
        return response()->json(['moyenne' => round($moyenne, 1), 'evaluations' => $evaluations]);
    }
}
