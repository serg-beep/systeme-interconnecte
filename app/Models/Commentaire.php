<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'publication_id',
        'annuaire_id',
        'nom_visiteur',
        'email_visiteur',
        'contenu',
        'statut',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────

    public function scopeApprouves($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeRejetes($query)
    {
        return $query->where('statut', 'rejete');
    }

    // ── Relations ────────────────────────────────────

    public function annuaire()
    {
        return $this->belongsTo(Annuaire::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
}
