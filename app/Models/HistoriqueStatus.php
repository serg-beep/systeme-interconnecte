<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueStatus extends Model
{
    use HasFactory;

    protected $table = 'historique_statuts';

    protected $fillable = [
        'user_id',
        'demande_id',
        'requete_id',
        'partenaire_id',
        'ancien_statut',
        'nouveau_statut',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }
    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }
    public function partenaire()
    {
        return $this->belongsTo(Partenaire::class);
    }
}
