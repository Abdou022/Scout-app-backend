<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\GuideResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Lister toutes les catégories.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage    = min($request->query('per_page', 15), 50);
        $categories = Category::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json(CategoryResource::collection($categories));
    }

    /**
     * Créer une catégorie (admin uniquement).
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json(new CategoryResource($category), 201);
    }

    /**
     * Afficher une catégorie.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json(new CategoryResource($category));
    }

    /**
     * Mettre à jour une catégorie (admin uniquement).
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json(new CategoryResource($category));
    }

    /**
     * Supprimer une catégorie (admin uniquement).
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Catégorie supprimée.']);
    }

    /**
     * Lister les guides d'une catégorie.
     */
    public function guides(Request $request, Category $category): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $guides  = $category->guides()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(GuideResource::collection($guides));
    }
}
