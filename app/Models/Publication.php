<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titre',
        'type_publication',
        'contenu',
        'image',
        'statut',
        'visible_site',
    ];

    protected $casts = [
        'visible_site' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────

    // Seulement les publications visibles sur le site
    public function scopePublic($query)
    {
        return $query->where('statut', 'publie')
                     ->where('visible_site', true);
    }

    // Seulement les brouillons
    public function scopeBrouillon($query)
    {
        return $query->where('statut', 'brouillon');
    }

    // ── Relations ────────────────────────────────────

    // L'utilisateur (du logiciel) qui a publié
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Commentaires approuvés (pour le site public)
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function commentairesApprouves()
    {
        return $this->hasMany(Commentaire::class)
                    ->where('statut', 'approuve');
    }

    public function commentairesEnAttente()
    {
        return $this->hasMany(Commentaire::class)
                    ->where('statut', 'en_attente');
    }

    // ── Accesseurs ───────────────────────────────────

    // URL complète de l'image
    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return Storage::disk('public')->url($this->image);
    }

    // Résumé court du contenu (pour les previews)
    public function getExtraitAttribute()
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->contenu ?? ''), 150);
    }

    protected $appends = ['image_url', 'extrait'];
}
