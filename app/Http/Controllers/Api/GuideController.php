<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuideRequest;
use App\Http\Requests\UpdateGuideRequest;
use App\Http\Resources\GuideResource;
use App\Models\Guide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuideController extends Controller
{
    /**
     * Lister tous les guides.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $guides  = Guide::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(GuideResource::collection($guides));
    }

    /**
     * Créer un guide (admin uniquement).
     * La cover_image est stockée sur le disque 'public'.
     */
    public function store(StoreGuideRequest $request): JsonResponse
    {
        $data               = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('guides/covers', 'public');
        }

        $guide = Guide::create($data);

        return response()->json(new GuideResource($guide->load('category')), 201);
    }

    /**
     * Afficher un guide.
     */
    public function show(Guide $guide): JsonResponse
    {
        $guide->load('category');

        return response()->json(new GuideResource($guide));
    }

    /**
     * Mettre à jour un guide (admin uniquement).
     */
    public function update(UpdateGuideRequest $request, Guide $guide): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($guide->cover_image) {
                Storage::disk('public')->delete($guide->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('guides/covers', 'public');
        }

        $guide->update($data);

        return response()->json(new GuideResource($guide->fresh('category')));
    }

    /**
     * Supprimer un guide (admin uniquement).
     */
    public function destroy(Guide $guide): JsonResponse
    {
        if ($guide->cover_image) {
            Storage::disk('public')->delete($guide->cover_image);
        }

        $guide->delete();

        return response()->json(['message' => 'Guide supprimé.']);
    }

    /**
     * Uploader une image intégrée dans le contenu HTML du guide (éditeur rich text).
     * Retourne l'URL publique de l'image pour insertion dans le HTML.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $path = $request->file('image')->store('guides/images', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Uploader une vidéo intégrée dans le contenu HTML du guide.
     * Retourne l'URL publique pour insertion dans le HTML.
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        $path = $request->file('video')->store('guides/videos', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
