<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    /**
     * Lister toutes les chansons.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $songs   = Song::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json(SongResource::collection($songs));
    }

    /**
     * Créer une chanson (admin uniquement).
     * L'audio peut être fourni directement lors de la création.
     */
    public function store(StoreSongRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        // Stocker l'audio si fourni (storage/app/public/songs/)
        if ($request->hasFile('audio')) {
            $data['audio_url'] = $request->file('audio')->store('songs', 'public');
        }

        $song = Song::create($data);

        return response()->json(new SongResource($song), 201);
    }

    /**
     * Afficher une chanson.
     */
    public function show(Song $song): JsonResponse
    {
        return response()->json(new SongResource($song));
    }

    /**
     * Mettre à jour une chanson (admin uniquement).
     */
    public function update(UpdateSongRequest $request, Song $song): JsonResponse
    {
        $song->update($request->validated());

        return response()->json(new SongResource($song));
    }

    /**
     * Supprimer une chanson (admin uniquement).
     */
    public function destroy(Song $song): JsonResponse
    {
        if ($song->audio_url) {
            Storage::disk('public')->delete($song->audio_url);
        }

        $song->delete();

        return response()->json(['message' => 'Chanson supprimée.']);
    }

    /**
     * Uploader le fichier audio d'une chanson existante.
     * Remplace l'ancien fichier si présent.
     */
    public function uploadAudio(Request $request, Song $song): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        // Supprimer l'ancien fichier audio
        if ($song->audio_url) {
            Storage::disk('public')->delete($song->audio_url);
        }

        $path = $request->file('audio')->store('songs', 'public');
        $song->update(['audio_url' => $path]);

        return response()->json([
            'message'   => 'Audio mis à jour.',
            'audio_url' => asset('storage/' . $path),
        ]);
    }
}
