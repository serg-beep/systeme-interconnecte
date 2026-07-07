<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
        protected $fillable = [
            'user_id',
            'entreprise_id',
            'titre',
            'ficher',
            'type',
            'contenu',
            'periode_debut',
            'periode_fin',

        ];
        protected $casts = [
        'periode_debut' => 'date',
        'periode_fin'   => 'date',
    ];
        public function user()
        {
            return $this->belongsTo(User::class);
        }
        public function entreprise()
        {
            return $this->belongsTo(Entreprise::class);
        }
    use HasFactory;
}
