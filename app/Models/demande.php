<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_source_id',
        'entreprise_cible_id',
        'user_id',
        'titre',
        'description',
        'type',
        'statut',
    ];

    public function entrepriseSource()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_source_id');
    }

    public function entrepriseCible()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_cible_id');
    }

    public function entrepriseDestinataire()
    {
        return $this->entrepriseCible();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class, 'demande_id');
    }

    public function historique()
    {
        return $this->hasMany(Historique_Status::class, 'demande_id');
    }
}
