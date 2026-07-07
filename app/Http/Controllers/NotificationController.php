<?php
namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller {

    public function index(Request $request) {
        $query = Notification::where('user_id', auth()->id())
            ->select('id','user_id','titre','message','type','lien','lu','created_at')
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->has('lu'), fn ($builder) => $builder->where('lu', $request->boolean('lu')))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function nonLues() {
        return response()->json(
            Notification::where('user_id', auth()->id())->where('lu', false)
                ->select('id','user_id','titre','message','type','lien','lu','created_at')
                ->latest()->limit(100)->get()
        );
    }

    public function marquerLue($id) {
        Notification::where('id', $id)->where('user_id', auth()->id())->update(['lu' => true]);
        return response()->json(['message' => 'Notification lue']);
    }

    public function toutMarquerLues() {
        Notification::where('user_id', auth()->id())->update(['lu' => true]);
        return response()->json(['message' => 'Toutes les notifications lues']);
    }

    public function destroy($id) {
        Notification::where('id', $id)->where('user_id', auth()->id())->delete();
        return response()->json(['message' => 'Notification supprimée']);
    }
}
