<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\Demande;
use App\Models\Partenaire;
use App\Models\Requete;

class DashboardController extends Controller
{
    public function index()
    {
        $entrepriseId = auth()->user()->entreprise_id;

        $alertes = Alerte::select('id','entreprise_id','priorite','created_at')
            ->with('entreprise:id,nom')
            ->where(function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId)
                    ->orWhereHas('entreprisesDestinaires', fn ($q) => $q->where('entreprise_id', $entrepriseId));
            });

        $alertesUrgentes = (clone $alertes)
            ->whereIn('priorite', ['critique', 'haute']);

        $demandesRecues = Demande::select('id', 'entreprise_source_id', 'titre', 'statut', 'created_at')
            ->with('entrepriseSource:id,nom')
            ->where('entreprise_cible_id', $entrepriseId)
            ->where('statut', 'en_attente');

        $requetesOuvertes = Requete::select('id', 'entreprise_id', 'user_id', 'titre', 'type', 'statut', 'created_at')
            ->with('entreprise:id,nom')
            ->where('statut', 'ouverte')
            ->doesntHave('reponses');

        $partenaires = Partenaire::select('id', 'entreprise_id', 'entreprise_partenaire_id', 'statut', 'created_at')
            ->with(['entreprise:id,nom', 'entreprisePartenaire:id,nom'])
            ->where(function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId)
                    ->orWhere('entreprise_partenaire_id', $entrepriseId);
            });

        $partenairesAcceptes = (clone $partenaires)->where('statut', 'accepte');
        $partenairesAttente = (clone $partenaires)->where('statut', 'en_attente');

        return response()->json([
            'stats' => [
                'alertes_critiques' => (clone $alertes)->where('priorite', 'critique')->count(),
                'total_alertes' => (clone $alertes)->count(),
                'demandes_en_attente' => (clone $demandesRecues)->count(),
                'requetes_ouvertes' => (clone $requetesOuvertes)->count(),
                'partenaires_actifs' => (clone $partenairesAcceptes)->count(),
                'partenaires_attente' => (clone $partenairesAttente)->count(),
            ],
            'alertes_urgentes' => (clone $alertesUrgentes)->latest()->limit(4)->get(),
            'demandes_recues' => (clone $demandesRecues)->latest()->limit(4)->get(),
            'requetes_ouvertes' => (clone $requetesOuvertes)->latest()->limit(4)->get(),
            'partenaires_acceptes' => (clone $partenairesAcceptes)->latest()->limit(6)->get(),
        ]);
    }
}
