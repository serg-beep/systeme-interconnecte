<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    private const TYPES = [
        'user'       => User::class,
        'entreprise' => Entreprise::class,
    ];

    // ── Basculer un abonnement (auth requis) ──
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'followable_type' => 'required|in:user,entreprise',
            'followable_id'   => 'required|integer',
        ]);

        $modelClass = self::TYPES[$data['followable_type']];
        $target     = $modelClass::findOrFail($data['followable_id']);

        if ($data['followable_type'] === 'user' && $target->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suivre vous-même'], 422);
        }

        $existant = Abonnement::where('follower_id', auth()->id())
            ->where('followable_type', $data['followable_type'])
            ->where('followable_id', $data['followable_id'])
            ->first();

        if ($existant) {
            $existant->delete();
            return response()->json(['suivi' => false, 'total' => $target->abonnes()->count()]);
        }

        $abonnement = new Abonnement(['follower_id' => auth()->id()]);
        $abonnement->followable()->associate($target);
        $abonnement->save();

        $suiveur = auth()->user()->prenom . ' ' . auth()->user()->nom;
        $estUser = $data['followable_type'] === 'user';
        $message = $estUser
            ? "{$suiveur} s'est abonné à votre profil."
            : "{$suiveur} s'est abonné à votre établissement.";

        foreach ($estUser ? [$target] : $target->users as $destinataire) {
            $this->notifier(
                $destinataire,
                'nouvel_abonne',
                'Nouvel abonné',
                $message,
                $estUser ? "/profil-public/{$target->id}" : '/app/annuaire'
            );
        }

        return response()->json(['suivi' => true, 'total' => $target->abonnes()->count()]);
    }

    // ── Fil "Mes abonnements" (liste des comptes/entreprises suivis) ──
    public function mesAbonnements()
    {
        $abonnements = auth()->user()->abonnements()->with('followable')->latest()->get();

        return response()->json($abonnements);
    }
}
