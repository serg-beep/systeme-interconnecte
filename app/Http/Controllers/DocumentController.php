<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Partenaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::where('entreprise_id', auth()->user()->entreprise_id)
            ->select('id', 'entreprise_id', 'user_id', 'titre', 'type', 'taille', 'visibilite', 'created_at')
            ->with(['user:id,prenom,nom', 'entreprise:id,nom'])
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('visibilite'), fn ($builder) => $builder->where('visibilite', $request->visibilite))
            ->when($request->filled('q'), fn ($builder) => $builder->where('titre', 'like', '%'.$request->q.'%'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'fichier' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webp'])
                    ->max(10 * 1024),
            ],
            'type' => 'required|in:rapport,certificat,ordonnance,analyse,autre,note',
            'visibilite' => 'required|in:prive,partenaires,public',
        ]);

        $this->validateUploadedDocument($request->file('fichier'));

        $disk = $data['visibilite'] === 'public' ? 'public' : 'local';
        $directory = $disk === 'public' ? 'documents/public' : 'documents/private';
        $path = $request->file('fichier')->store($directory, $disk);
        $size = $request->file('fichier')->getSize();

        $document = Document::create([
            'entreprise_id' => auth()->user()->entreprise_id,
            'user_id' => auth()->id(),
            'titre' => $data['titre'],
            'fichier' => $path,
            'disque' => $disk,
            'type' => $data['type'],
            'taille' => round($size / 1024 / 1024, 2).' MB',
            'visibilite' => $data['visibilite'],
        ]);

        $auteur = trim(auth()->user()->prenom.' '.auth()->user()->nom);
        $lien   = '/app/documents?id='.$document->id;

        $collegues = User::where('entreprise_id', auth()->user()->entreprise_id)
            ->where('id', '!=', auth()->id())
            ->get();
        foreach ($collegues as $destinataire) {
            $this->notifier($destinataire, 'document', 'Nouveau document', "{$auteur} a ajouté le document \"{$data['titre']}\".", $lien);
        }

        if ($data['visibilite'] === 'partenaires') {
            $entrepriseId = auth()->user()->entreprise_id;
            $partenaireEntrepriseIds = Partenaire::where('statut', 'accepte')
                ->where(fn ($q) => $q->where('entreprise_id', $entrepriseId)->orWhere('entreprise_partenaire_id', $entrepriseId))
                ->get()
                ->map(fn ($p) => $p->entreprise_id == $entrepriseId ? $p->entreprise_partenaire_id : $p->entreprise_id);

            $partenaireUsers = User::whereIn('entreprise_id', $partenaireEntrepriseIds)->get();
            foreach ($partenaireUsers as $destinataire) {
                $this->notifier($destinataire, 'document', 'Document partagé', "{$auteur} a partagé le document \"{$data['titre']}\" avec votre entreprise.", $lien);
            }
        }

        return response()->json($document->load('user', 'entreprise'), 201);
    }

    public function show($id)
    {
        return response()->json($this->findVisibleDocument($id)->load(['entreprise:id,nom', 'user:id,prenom,nom']));
    }

    public function update(Request $request, $id)
    {
        $doc = Document::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        $doc->update($request->validate([
            'titre' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:rapport,certificat,ordonnance,analyse,autre,note',
            'visibilite' => 'sometimes|in:prive,partenaires,public',
        ]));

        return response()->json($doc->load('user', 'entreprise'));
    }

    public function destroy($id)
    {
        $doc = Document::where('entreprise_id', auth()->user()->entreprise_id)->findOrFail($id);
        Storage::disk($doc->disque ?: 'public')->delete($doc->fichier);
        $doc->delete();

        return response()->json(['message' => 'Document supprime']);
    }

    public function partages(Request $request)
    {
        $query = Document::with('entreprise')
            ->where(function ($builder) {
                $builder->where('visibilite', 'public')
                    ->orWhere('visibilite', 'partenaires');
            })
            ->select('id', 'entreprise_id', 'titre', 'type', 'taille', 'visibilite', 'created_at')
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('q'), fn ($builder) => $builder->where('titre', 'like', '%'.$request->q.'%'))
            ->latest();

        return $this->listResponse($query, $request, 30, 100);
    }

    public function telecharger($id)
    {
        $doc = $this->findVisibleDocument($id);

        if (($doc->disque ?: 'public') === 'public') {
            return response()->json([
                'url' => asset('storage/'.$doc->fichier),
                'titre' => $doc->titre,
            ]);
        }

        return Storage::download($doc->fichier, $doc->titre);
    }

    private function findVisibleDocument($id): Document
    {
        $entrepriseId = auth()->user()->entreprise_id;

        return Document::where(function ($query) use ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId)
                ->orWhere('visibilite', 'public')
                ->orWhere('visibilite', 'partenaires');
        })->findOrFail($id);
    }

    private function validateUploadedDocument(UploadedFile $file): void
    {
        $allowed = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (!isset($allowed[$extension]) || !in_array($mime, $allowed[$extension], true)) {
            abort(response()->json([
                'message' => 'Type de fichier refuse. Verifiez extension et contenu reel du fichier.',
            ], 422));
        }
    }
}
