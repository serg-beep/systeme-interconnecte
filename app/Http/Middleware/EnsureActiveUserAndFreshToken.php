<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUserAndFreshToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        if (!$user->actif) {
            optional($user->currentAccessToken())->delete();

            return response()->json(['message' => 'Compte desactive. Contactez votre administrateur.'], 403);
        }

        $expiration = config('sanctum.expiration');
        $token = $user->currentAccessToken();

        if ($expiration && $token && $token->created_at && $token->created_at->lte(now()->subMinutes($expiration))) {
            $token->delete();

            return response()->json(['message' => 'Session expiree. Veuillez vous reconnecter.'], 401);
        }

        return $next($request);
    }
}
