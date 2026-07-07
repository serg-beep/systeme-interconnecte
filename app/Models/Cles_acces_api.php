<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cles_acces_api extends Model
{
     protected $fillable = [
        'entreprise_id',
        'user_id',
        'nom',
        'token',
        'derniere_utilisation',
        'expire_le',
        'actif',

    ];
    protected $hidden = ['token'];

    protected $casts = [
        'derniere_utilisation' => 'datetime',
        'expire_le'            => 'datetime',
        'actif'                => 'boolean',
    ];
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function estExpiree()
    {
        return $this->expire_le->isPast();
    }

    use HasFactory;
}
