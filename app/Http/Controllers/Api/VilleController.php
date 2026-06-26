<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVilleRequest;
use App\Http\Requests\UpdateVilleRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\RegimentResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VilleResource;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    /**
     * Lister toutes les villes (paginé, du plus récent au plus ancien).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $villes  = Ville::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json(VilleResource::collection($villes));
    }

    /**
     * Créer une ville (admin uniquement).
     */
    public function store(StoreVilleRequest $request): JsonResponse
    {
        $ville = Ville::create($request->validated());

        return response()->json(new VilleResource($ville), 201);
    }

    /**
     * Afficher une ville.
     */
    public function show(Ville $ville): JsonResponse
    {
        return response()->json(new VilleResource($ville));
    }

    /**
     * Mettre à jour une ville (admin uniquement).
     */
    public function update(UpdateVilleRequest $request, Ville $ville): JsonResponse
    {
        $ville->update($request->validated());

        return response()->json(new VilleResource($ville));
    }

    /**
     * Supprimer une ville (admin uniquement).
     */
    public function destroy(Ville $ville): JsonResponse
    {
        $ville->delete();

        return response()->json(['message' => 'Ville supprimée.']);
    }

    /**
     * Lister les régiments d'une ville.
     */
    public function regiments(Request $request, Ville $ville): JsonResponse
    {
        $perPage   = min($request->query('per_page', 15), 50);
        $regiments = $ville->regiments()
            ->with('chef')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(RegimentResource::collection($regiments));
    }

    /**
     * Lister les événements d'une ville.
     * Filtrés automatiquement sur la ville de l'utilisateur connecté si applicable.
     */
    public function events(Request $request, Ville $ville): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $events  = $ville->events()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(EventResource::collection($events));
    }

    /**
     * Lister les utilisateurs d'une ville (admin uniquement).
     */
    public function users(Request $request, Ville $ville): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $users   = $ville->users()
            ->with(['grade'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(UserResource::collection($users));
    }
}
