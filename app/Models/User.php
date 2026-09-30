<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Entreprise;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Notification;
use App\Models\Message;
use App\Models\Reponse;
use App\Models\HistoriqueAction;
use App\Models\Cles_acces_api;
use App\Models\Invitation;
use App\Models\Historique_Status;

class User extends Authenticatable {
    use HasApiTokens,
    Notifiable;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'photo',
        'poste',
        'bio',
        'date_naissance',
        'actif',
        'verifie',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance'    => 'date',
        'actif'             => 'boolean',
        'verifie'           => 'boolean',
    ];

    public function entreprise() {
        return $this->belongsTo(Entreprise::class);
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function notifications() {
        return $this->hasMany(Notification::class);
    }

    public function messages() {
        return $this->hasMany(Message::class);
    }

    public function reponses() {
        return $this->hasMany(Reponse::class);
    }

    public function Historique_Actions() {
        return $this->hasMany(HistoriqueAction::class);
    }

    public function documents() {
        return $this->hasMany(Document::class);
    }


    public function historique_Status() {
        return $this->hasMany(Historique_Status::class);
    }

    public function hasRole($role) {
        return $this->roles()->where('nom', $role)->exists();
    }

    public function hasPermission($permission) {
        return $this->permissions()->where('nom', $permission)->exists()
            || $this->roles()
                    ->whereHas('permissions', fn($q) => $q->where('nom', $permission))
                    ->exists();
    }

    public function notificationsNonLues() {
        return $this->notifications()->where('lu', false)->count();
    }
    // Dans App\Models\User.php, ajouter dans les relations existantes :

// Comptes/entreprises que CE user suit
public function abonnements()
{
    return $this->hasMany(Abonnement::class, 'follower_id');
}

// Utilisateurs qui suivent CE user
public function abonnes()
{
    return $this->morphMany(Abonnement::class, 'followable');
}

public function estSuiviPar(?User $user): bool
{
    if (!$user) return false;
    return $this->abonnes()->where('follower_id', $user->id)->exists();
}
}
