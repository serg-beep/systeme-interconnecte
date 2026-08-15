<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Like;
use App\Models\Notification;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function listResponse(Builder $query, Request $request, int $defaultPerPage = 30, int $maxPerPage = 100)
    {
        if ($request->boolean('paginate')) {
            $perPage = min(
                max((int) $request->integer('per_page', $defaultPerPage), 1),
                $maxPerPage
            );

            return response()->json($query->paginate($perPage));
        }

        $limit = $request->integer('limit');
        $query->limit($limit > 0 ? min($limit, $maxPerPage) : $defaultPerPage);

        return response()->json($query->get());
    }

    // Attache "liked_by_me" à une collection de modèles likables, sans N+1,
    // pour un utilisateur potentiellement anonyme (routes publiques).
    protected function attacherLikedByMe(Collection $items, string $likeableType): void
    {
        $user = auth('sanctum')->user();

        if (!$user || $items->isEmpty()) {
            $items->each(fn ($item) => $item->liked_by_me = false);
            return;
        }

        $likedIds = Like::where('user_id', $user->id)
            ->where('likeable_type', $likeableType)
            ->whereIn('likeable_id', $items->pluck('id'))
            ->pluck('likeable_id')
            ->all();

        $items->each(fn ($item) => $item->liked_by_me = in_array($item->id, $likedIds));
    }

    // Notifie un utilisateur d'un événement social (sans jamais se notifier soi-même).
    protected function notifier(User $destinataire, string $type, string $titre, string $message, ?string $lien = null): void
    {
        if ($destinataire->id === auth()->id()) {
            return;
        }

        Notification::create([
            'user_id' => $destinataire->id,
            'type'    => $type,
            'titre'   => $titre,
            'message' => $message,
            'lien'    => $lien,
            'lu'      => false,
        ]);
    }
}
