<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\Demande;
use App\Models\Notification;
use App\Models\Partenaire;
use App\Models\Requete;
use App\Models\User;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function stream(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'dashboard'     => $this->dashboardPayload($user),
            'alerts'        => $this->alertsPayload($user),
            'notifications' => $this->notificationsPayload($user),
            'requetes'      => $this->requetesPayload($user),
            'heartbeat'     => ['time' => now()->toISOString()],
        ]);
    }

    private function dashboardPayload(User $user): array
    {
        $entrepriseId = $user->entreprise_id;

        $alertesQuery = Alerte::with('entreprise')
            ->where(function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId)
                    ->orWhereHas('entreprisesDestinaires', function ($destinataires) use ($entrepriseId) {
                        $destinataires->where('entreprise_id', $entrepriseId);
                    });
            })
            ->latest();

        $demandesEnAttente = Demande::where(function ($query) use ($entrepriseId) {
            $query->where('entreprise_source_id', $entrepriseId)
                ->orWhere('entreprise_cible_id', $entrepriseId);
        })
            ->where('statut', 'en_attente')
            ->count();

        $partenairesActifs = Partenaire::where(function ($query) use ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId)
                ->orWhere('entreprise_partenaire_id', $entrepriseId);
        })
            ->where('statut', 'accepte')
            ->count();

        return [
            'stats' => [
                'alertes' => (clone $alertesQuery)->count(),
                'alertes_critiques' => (clone $alertesQuery)->where('priorite', 'critique')->count(),
                'demandes' => $demandesEnAttente,
                'requetes' => Requete::where('statut', 'ouverte')
                    ->doesntHave('reponses')
                    ->count(),
                'partenaires' => $partenairesActifs,
            ],
            'alertes' => (clone $alertesQuery)->limit(5)->get(),
            'meta' => [
                'generated_at' => now()->toISOString(),
                'source' => 'sse',
            ],
        ];
    }

    private function alertsPayload(User $user): array
    {
        $entrepriseId = $user->entreprise_id;

        return [
            'items' => Alerte::with('entreprise', 'user', 'entreprisesDestinaires')
                ->where(function ($query) use ($entrepriseId) {
                    $query->where('entreprise_id', $entrepriseId)
                        ->orWhereHas('entreprisesDestinaires', function ($destinataires) use ($entrepriseId) {
                            $destinataires->where('entreprise_id', $entrepriseId);
                        });
                })
                ->latest()
                ->get(),
            'meta' => [
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    private function notificationsPayload(User $user): array
    {
        $query = Notification::where('user_id', $user->id)->latest();

        return [
            'items' => (clone $query)->limit(8)->get(),
            'unread_count' => (clone $query)->where('lu', false)->count(),
            'meta' => [
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    private function requetesPayload(User $user): array
    {
        return [
            'items' => Requete::with('entreprise', 'user')
                ->withCount('reponses')
                ->where('statut', 'ouverte')
                ->doesntHave('reponses')
                ->latest()
                ->get(),
            'meta' => [
                'generated_at' => now()->toISOString(),
                'entreprise_id' => $user->entreprise_id,
            ],
        ];
    }

}
