<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueAction extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id','entreprise_id','action','table_cible',
        'enregistrement_id','anciennes_valeurs','nouvelles_valeurs','ip_address'
    ];
    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
