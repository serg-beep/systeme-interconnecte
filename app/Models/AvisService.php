<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisService extends Model
{
    protected $table = 'avis_services';

    protected $fillable = ['user_id', 'annuaire_id', 'note', 'commentaire', 'statut'];

    protected $casts = ['note' => 'integer'];

    public function scopeApprouves($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annuaire()
    {
        return $this->belongsTo(Annuaire::class);
    }
}
