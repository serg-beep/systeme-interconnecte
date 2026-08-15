<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Annuaire;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Publication;
use App\Models\Alerte;
use App\Models\Requete;
use App\Models\Demande;
use App\Models\Partenaire;
use App\Models\Document;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Relation::enforceMorphMap([
            'annuaire'    => Annuaire::class,
            'user'        => User::class,
            'entreprise'  => Entreprise::class,
            'publication' => Publication::class,
        ]);

        foreach ([Entreprise::class, User::class, Alerte::class, Requete::class, Demande::class, Annuaire::class, Partenaire::class, Document::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
