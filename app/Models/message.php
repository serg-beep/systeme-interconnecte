<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class message extends Model

{
    protected $fillable = [
        'lu',
        'user_id',
        'conversation_id',
        'contenu',
    ];
     protected $casts = ['lu' => 'boolean'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    public function piecesJointes()
    {
        return $this->hasMany(MessagePieceJointe::class);
    }

    public function piece_Jointe()
    {
        return $this->piecesJointes();
    }

    use HasFactory;
}
