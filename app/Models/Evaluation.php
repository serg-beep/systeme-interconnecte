<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
        protected $fillable = [
            'entreprise_evaluee_id',
            'entreprise_id',
            'user_id',
            'note',
            'commentaire',
        ];
        protected $casts = ['note' => 'integer'];
        public function user()
        {
            return $this->belongsTo(User::class);
        }
        public function entrepriseEvaluee()
        {
            return $this->belongsTo(Entreprise::class, 'entreprise_evaluee_id');
        }
        public function entreprise()
        {
            return $this->belongsTo(Entreprise::class);
        }
    use HasFactory;
}
