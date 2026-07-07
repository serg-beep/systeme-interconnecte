<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\user;



class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'type',
        'description',
        'statut',
        'logo',
    ];
    public function users()
    { return $this->hasMany(User::class); }
    public function partenaires()
    { return $this->hasMany(Partenaire::class); }
    public function alertes()
    { return $this->hasMany(Alerte::class); }
    public function requetes()
    { return $this->hasMany(Requete::class); }
    public function demandesEnvoyees()
    { return $this->hasMany(Demande::class, 'entreprise_source_id'); }
    public function demandesRecues()
     { return $this->hasMany(Demande::class, 'entreprise_cible_id'); }
    public function conversations()
    { return $this->belongsToMany(Conversation::class, 'conversation_entreprise'); }
    public function documents()
    { return $this->hasMany(Document::class); }
    public function annuaires()
    { return $this->hasMany(Annuaire::class); }
    public function evaluationsDonnees()
     { return $this->hasMany(Evaluation::class, 'entreprise_id'); }
    public function evaluationsRecues()
     { return $this->hasMany(Evaluation::class, 'entreprise_evaluee_id'); }
    public function Cles_acces_api()
     { return $this->hasMany(Cles_acces_api::class); }
    public function invitations() { return $this->hasMany(Invitation::class); }
    public function rapports()
    { return $this->hasMany(Rapport::class); }
    public function notifications()
     { return $this->hasManyThrough(Notification::class, User::class); }
    public function alertesRecues()
     {
        return $this->belongsToMany(Alerte::class, 'alerte_entreprise')
                    ->withPivot('vue','date_vue','accuse_reception');
    }


}
