<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\HistoriqueAction;

class HistoriqueActionController extends Controller {

    public function index() {
        return response()->json(
            HistoriqueAction::select('id','user_id','entreprise_id','action','table_cible','ip_address','created_at')
                ->with(['user:id,prenom,nom','entreprise:id,nom'])
                ->latest()->paginate(50)
        );
    }

    public function parEntreprise() {
        return response()->json(
            HistoriqueAction::select('id','user_id','entreprise_id','action','table_cible','ip_address','created_at')
                ->with(['user:id,prenom,nom','entreprise:id,nom'])
                ->where('entreprise_id', auth()->user()->entreprise_id)
                ->latest()->paginate(50)
        );
    }

    public function parUser($id) {
        return response()->json(
            HistoriqueAction::select('id','user_id','entreprise_id','action','table_cible','ip_address','created_at')
                ->with(['entreprise:id,nom'])
                ->where('user_id', $id)
                ->latest()->paginate(50)
        );
    }
}
