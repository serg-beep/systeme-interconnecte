<?php

namespace App\Http\Controllers;

use App\Models\Annuaire;
use App\Models\Like;
use App\Models\Publication;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'likeable_type' => 'required|in:annuaire,publication',
            'likeable_id' => 'required|integer',
        ]);

        $target = $data['likeable_type'] === 'publication'
            ? Publication::findOrFail($data['likeable_id'])
            : Annuaire::findOrFail($data['likeable_id']);

        $existing = Like::where('user_id', auth()->id())
            ->where('likeable_type', $data['likeable_type'])
            ->where('likeable_id', $target->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false, 'total' => $target->likes()->count()]);
        }

        $like = new Like(['user_id' => auth()->id()]);
        $like->likeable()->associate($target);
        $like->save();

        if ($data['likeable_type'] === 'publication') {
            $this->notifier(
                $target->user,
                'like',
                'Nouveau like',
                trim(auth()->user()->prenom.' '.auth()->user()->nom)." a aimé votre publication \"{$target->titre}\".",
                "/publication/{$target->id}"
            );
        }

        return response()->json(['liked' => true, 'total' => $target->likes()->count()]);
    }
}
