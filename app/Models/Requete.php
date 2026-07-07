<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requete extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'titre',
        'statut',
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

    public function reponses()
    {
        return $this->hasMany(Reponse::class, 'requete_id');
    }

    public function historique()
    {
        return $this->hasMany(Historique_Status::class, 'requete_id');
    }
}
