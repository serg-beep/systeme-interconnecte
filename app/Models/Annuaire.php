<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annuaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'service',
        'description',
        'disponible',
        'categorie',
        'cover_image',
        'visit_url',
        'tags',
        'ville',
        'etat_publication',
        'vues',
    ];

    protected $casts = [
        'disponible'       => 'boolean',
        'tags'             => 'array',
        'vues'             => 'integer',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class)->where('statut', 'approuve')->latest();
    }
}
