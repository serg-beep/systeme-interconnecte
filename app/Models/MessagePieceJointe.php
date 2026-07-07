<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagePieceJointe extends Model
{
    use HasFactory;

    protected $table = 'message_pieces_jointes';

    protected $fillable = [
        'message_id',
        'type',
        'fichier',
    ];

    protected $appends = ['nom'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function getNomAttribute()
    {
        return basename($this->fichier);
    }
}
