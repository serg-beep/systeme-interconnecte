<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
        protected $fillable = [
            'entreprise_id',
            'user_id',
            'email',
            'token',
            'statut',
            'date_expiration',
        ];
        protected $casts = ['date_expiration' => 'datetime'];

        public function entreprise()
        {
            return $this->belongsTo(Entreprise::class);
        }
        public function estExpiree()
        {
            return $this->date_expiration->isPast();
        }
    use HasFactory;
}
