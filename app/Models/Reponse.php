<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entreprise_id',
        'requete_id',
        'demande_id',
        'contenu',
        'piece_jointe',
        'statut',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }
}
