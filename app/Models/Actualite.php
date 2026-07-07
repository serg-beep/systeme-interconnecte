<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Actualite extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'resume',
        'contenu',
        'image',
        'categorie',
        'source',
        'url_source',
        'url_externe',
        'publie',
        'auto_fetched',
        'hash_dedup',
        'date_publication',
    ];

    protected $casts = [
        'publie'           => 'boolean',
        'auto_fetched'     => 'boolean',
        'date_publication' => 'datetime',
    ];

    protected $appends = ['image_url', 'extrait'];

    public function scopePublie($query)
    {
        return $query->where('publie', true);
    }

    public function scopeCategorie($query, string $cat)
    {
        return $query->where('categorie', $cat);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return Storage::disk('public')->url($this->image);
    }

    public function getExtraitAttribute(): string
    {
        return Str::limit(strip_tags($this->resume ?? ''), 160);
    }

    public static function makeHash(string $titre, string $source): string
    {
        return md5(Str::lower(trim($titre)) . '|' . Str::lower(trim($source)));
    }
}
