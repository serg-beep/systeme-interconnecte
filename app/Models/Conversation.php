<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'sujet',
        'message',
        'type',

        ];
        public function entreprises()
        {
            return $this->belongsToMany(Entreprise::class, 'conversation_entreprise')
            ->withTimestamps();
        }
        public function messages()
    {
        return $this->hasMany(Message::class);
    }
     public function dernierMessage() { return $this->hasOne(Message::class)->latest(); }
    use HasFactory;
}
