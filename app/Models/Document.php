<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
        protected $fillable = [
            'user_id',
            'entreprise_id',
            'titre',
            'fichier',
            'disque',
            'taille',
            'type',
            'visibilite',
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
