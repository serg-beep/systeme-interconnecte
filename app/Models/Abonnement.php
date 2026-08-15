<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = ['follower_id', 'followable_id', 'followable_type'];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function followable()
    {
        return $this->morphTo();
    }
}
