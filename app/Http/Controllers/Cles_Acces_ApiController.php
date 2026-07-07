<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Cles_acces_api;

class Cles_Acces_ApiController extends Controller {

    public function index(Request $request) {
        $query = Cles_acces_api::where('entreprise_id', auth()->user()->entreprise_id)
            ->select('id','entreprise_id','nom','actif','expire_le','created_at')
            ->when($request->has('actif'), fn ($builder) => $builder->where('actif', $request->boolean('actif')))
            ->when($request->filled('q'), fn ($builder) => $builder->where('nom', 'like', '%'.$request->q.'%'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nom'       => 'required|string',
            'expire_le' => 'nullable|date',
        ]);
        $data['entreprise_id'] = auth()->user()->entreprise_id;
        $data['user_id']       = auth()->id();
        $data['token']         = Str::random(64);
        return response()->json(Cles_acces_api::create($data), 201);
    }

    public function show($id) {
        return response()->json(Cles_acces_api::with('entreprise','user')->where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id));
    }

    public function update(Request $request, $id) {
        $token = Cles_acces_api::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $token->update($request->validate([
            'nom'       => 'sometimes|string',
            'expire_le' => 'nullable|date',
            'actif'     => 'sometimes|boolean',
        ]));
        return response()->json($token);
    }

    public function destroy($id) {
        Cles_acces_api::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Token supprimé']);
    }

    public function desactiver($id) {
        Cles_acces_api::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id)->update(['actif' => false]);
        return response()->json(['message' => 'Token désactivé']);
    }

    public function regenerer($id) {
        $token = Cles_acces_api::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $token->update(['token' => Str::random(64), 'actif' => true]);
        return response()->json(['message' => 'Token régénéré', 'token' => $token->token]);
    }
}
