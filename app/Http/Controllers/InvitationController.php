<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Invitation;

class InvitationController extends Controller {

  public function index(Request $request) {
    $query = Invitation::where('entreprise_id', auth()->user()->entreprise_id)
                ->select('id','entreprise_id','email_invite','statut','date_expiration','created_at')
        ->when($request->filled('statut'), fn ($builder) => $builder->where('statut', $request->statut))
        ->when($request->filled('q'), fn ($builder) => $builder->where('email_invite', 'like', '%'.$request->q.'%'))
        ->latest();

    return $this->listResponse($query, $request, 30, 100);
}

    public function store(Request $request) {
        $data = $request->validate(['email_invite' => 'required|email']);


        $invitation = Invitation::create([
            'entreprise_id'   => auth()->user()->entreprise_id,
            'email_invite'    => $data['email_invite'],
            'token'           => Str::random(40),
            'statut'          => 'en_attente',
            'date_expiration' => now()->addDays(7)
        ]);
        return response()->json($invitation, 201);
    }

   public function show($id) {
    $invitation = Invitation::where('entreprise_id', auth()->user()->entreprise_id)
        ->with('entreprise')
        ->findOrFail($id);

    return response()->json($invitation);
}

  public function update(Request $request, $id) {
    $inv = Invitation::where('entreprise_id', auth()->user()->entreprise_id)
        ->findOrFail($id);

    $inv->update($request->validate([
        'statut' => 'required|in:en_attente,acceptee,expiree'
    ]));

    return response()->json($inv);
}

  public function destroy($id) {
    $invitation = Invitation::where('entreprise_id', auth()->user()->entreprise_id)
        ->findOrFail($id);

    $invitation->delete();

    return response()->json(['message' => 'Invitation supprimée']);
}

    public function accepter(Request $request) {
        $data  = $request->validate(['token' => 'required|string']);
        $invitation = Invitation::where('token', $data['token'])
        ->where('statut', 'en_attente')
        ->where('date_expiration', '>', now())
       ->first();
        if (!$invitation || $invitation->estExpiree()) {
            return response()->json(['message' => 'Invitation invalide ou expirée'], 422);
        }
        return response()->json(['message' => 'Token valide', 'invitation' => $invitation->load('entreprise')]);
    }

   public function renvoyer($id) {
    $invitation = Invitation::where('entreprise_id', auth()->user()->entreprise_id)
        ->findOrFail($id);

    $invitation->update([
        'token'           => Str::random(40),
        'statut'          => 'en_attente',
        'date_expiration' => now()->addDays(7)
    ]);

    return response()->json([
        'message' => 'Invitation renvoyée',
        'invitation' => $invitation
    ]);
}
}
