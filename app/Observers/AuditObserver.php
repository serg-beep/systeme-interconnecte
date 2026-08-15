<?php

namespace App\Observers;

use App\Models\HistoriqueAction;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('creation', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log('modification', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->log('suppression', $model, $model->getOriginal(), null);
    }

    protected function log(string $action, Model $model, ?array $anciennesValeurs, ?array $nouvellesValeurs): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        HistoriqueAction::create([
            'user_id'           => $user->id,
            'entreprise_id'     => $user->entreprise_id,
            'action'            => $action,
            'table_cible'       => $model->getTable(),
            'enregistrement_id' => $model->getKey(),
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $nouvellesValeurs,
            'ip_address'        => request()?->ip(),
        ]);
    }
}
