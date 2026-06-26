<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GradeController extends Controller
{
    /**
     * Lister tous les grades.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $grades  = Grade::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json(GradeResource::collection($grades));
    }

    /**
     * Créer un grade (admin uniquement).
     * L'image du grade est stockée sur le disque 'public'.
     */
    public function store(StoreGradeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Stocker l'image si fournie (storage/app/public/grades/)
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('grades', 'public');
        }

        $grade = Grade::create($data);

        return response()->json(new GradeResource($grade), 201);
    }

    /**
     * Afficher un grade.
     */
    public function show(Grade $grade): JsonResponse
    {
        return response()->json(new GradeResource($grade));
    }

    /**
     * Mettre à jour un grade (admin uniquement).
     */
    public function update(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($grade->image) {
                Storage::disk('public')->delete($grade->image);
            }
            $data['image'] = $request->file('image')->store('grades', 'public');
        }

        $grade->update($data);

        return response()->json(new GradeResource($grade));
    }

    /**
     * Supprimer un grade (admin uniquement).
     */
    public function destroy(Grade $grade): JsonResponse
    {
        if ($grade->image) {
            Storage::disk('public')->delete($grade->image);
        }

        $grade->delete();

        return response()->json(['message' => 'Grade supprimé.']);
    }
}
