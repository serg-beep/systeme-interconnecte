<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'type',
        'titre',
        'priorite',
        'user_id',
        'entreprise_id',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entreprisesDestinaires()
    {
        return $this->belongsToMany(Entreprise::class, 'alerte_entreprise')
            ->withPivot('vue', 'date_vue', 'accuse_reception')
            ->withTimestamps();
    }
}
