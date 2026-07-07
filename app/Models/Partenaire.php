<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'entreprise_partenaire_id',
        'statut',
        'date_partenariat',
    ];
    protected $casts = ['date_partenariat' => 'date'];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
    public function entreprisePartenaire()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_partenaire_id');
    }
    public function historique()
    {
        return $this->hasMany(Historique_Status::class);
    }
}
