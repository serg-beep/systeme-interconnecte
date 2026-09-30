<?php

namespace App\Http\Controllers;

use App\Models\{Conversation, Message, Notification, User};
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $id = auth()->user()->entreprise_id;
        $query = Conversation::whereHas('entreprises', fn ($q) => $q->where('entreprise_id', $id))
            ->select('id','sujet','type','created_at','updated_at')
            ->with(['entreprises:id,nom', 'dernierMessage' => fn($q) => $q->select('id','conversation_id','user_id','contenu','created_at')->with('user:id,prenom,nom')])
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('q'), fn ($builder) => $builder->where('sujet', 'like', '%'.$request->q.'%'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sujet' => 'nullable|string|max:255',
            'type' => 'required|in:prive,groupe',
            'entreprises' => 'required|array|min:1',
            'entreprises.*' => 'exists:entreprises,id',
        ]);

        $conversation = Conversation::create([
            'sujet' => $data['sujet'] ?? null,
            'type' => $data['type'],
        ]);

        $ids = array_unique(array_merge($data['entreprises'], [auth()->user()->entreprise_id]));
        $conversation->entreprises()->attach($ids);

        return response()->json($conversation->load('entreprises', 'dernierMessage.user'), 201);
    }

    public function show($id)
    {
        return response()->json(
            $this->visibleConversations()
                ->with([
                    'entreprises:id,nom',
                    'messages' => fn ($q) => $q->select('id','conversation_id','user_id','contenu','created_at')
                        ->with([
                            'user:id,prenom,nom',
                            'piecesJointes:id,message_id,fichier,type,taille',
                        ]),
                ])
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $conv = $this->visibleConversations()->findOrFail($id);
        $conv->update($request->validate(['sujet' => 'nullable|string|max:255']));

        return response()->json($conv);
    }

    public function destroy($id)
    {
        $this->visibleConversations()->findOrFail($id)->delete();

        return response()->json(['message' => 'Conversation supprimee']);
    }

    public function messages(Request $request, $id)
    {
        $this->visibleConversations()->findOrFail($id);

        $query = Message::where('conversation_id', $id)
            ->select('id','conversation_id','user_id','contenu','created_at')
            ->with(['user:id,prenom,nom', 'piecesJointes:id,message_id,fichier,type,taille'])
            ->orderBy('created_at');

        return $this->listResponse($query, $request, 50, 200);
    }

    public function envoyerMessage(Request $request, $id)
    {
        $conv = $this->visibleConversations()->with('entreprises')->findOrFail($id);
        $data = $request->validate(['contenu' => 'required|string|max:5000']);

        $message = Message::create([
            'conversation_id' => $id,
            'user_id' => auth()->id(),
            'contenu' => $data['contenu'],
        ]);

        $autresIds = $conv->entreprises
            ->where('id', '!=', auth()->user()->entreprise_id)
            ->pluck('id');

        $destinataireIds = User::whereIn('entreprise_id', $autresIds)
            ->where('actif', true)
            ->pluck('id');

        if ($destinataireIds->isNotEmpty()) {
            $now = now();
            Notification::insert($destinataireIds->map(fn ($userId) => [
                'user_id'    => $userId,
                'titre'      => 'Nouveau message',
                'message'    => auth()->user()->prenom.' : '.substr($data['contenu'], 0, 80),
                'type'       => 'message',
                'lien'       => '/app/conversations?id='.$id,
                'lu'         => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray());
        }

        return response()->json($message->load('user'), 201);
    }

    public function ajouterMembre(Request $request, $id)
    {
        $data = $request->validate(['entreprise_id' => 'required|exists:entreprises,id']);
        $this->visibleConversations()->findOrFail($id)->entreprises()->syncWithoutDetaching([$data['entreprise_id']]);

        return response()->json(['message' => 'Membre ajoute']);
    }

    public function retirerMembre($id, $eid)
    {
        $conv = $this->visibleConversations()->findOrFail($id);

        if ((int) $eid === (int) auth()->user()->entreprise_id) {
            return response()->json(['message' => 'Impossible de retirer votre propre entreprise'], 422);
        }

        $conv->entreprises()->detach($eid);

        return response()->json(['message' => 'Membre retire']);
    }

    private function visibleConversations()
    {
        $id = auth()->user()->entreprise_id;

        return Conversation::whereHas('entreprises', fn ($query) => $query->where('entreprise_id', $id));
    }
}
