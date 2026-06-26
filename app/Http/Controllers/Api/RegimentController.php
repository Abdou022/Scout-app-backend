<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegimentRequest;
use App\Http\Requests\UpdateRegimentRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\RegimentResource;
use App\Http\Resources\UserResource;
use App\Models\Regiment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegimentController extends Controller
{
    /**
     * Lister tous les régiments.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage   = min($request->query('per_page', 15), 50);
        $regiments = Regiment::with(['ville', 'chef'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(RegimentResource::collection($regiments));
    }

    /**
     * Créer un régiment (admin uniquement).
     */
    public function store(StoreRegimentRequest $request): JsonResponse
    {
        $regiment = Regiment::create($request->validated());

        return response()->json(new RegimentResource($regiment->load('ville', 'chef')), 201);
    }

    /**
     * Afficher un régiment.
     */
    public function show(Regiment $regiment): JsonResponse
    {
        $regiment->load(['ville', 'chef']);

        return response()->json(new RegimentResource($regiment));
    }

    /**
     * Mettre à jour un régiment (admin uniquement, vérifié par middleware).
     */
    public function update(UpdateRegimentRequest $request, Regiment $regiment): JsonResponse
    {
        $regiment->update($request->validated());

        return response()->json(new RegimentResource($regiment->fresh(['ville', 'chef'])));
    }

    /**
     * Supprimer un régiment (admin uniquement).
     */
    public function destroy(Regiment $regiment): JsonResponse
    {
        $regiment->delete();

        return response()->json(['message' => 'Régiment supprimé.']);
    }

    /**
     * Lister les groupes d'un régiment.
     */
    public function groups(Request $request, Regiment $regiment): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $groups  = $regiment->groups()
            ->with(['chef', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(GroupResource::collection($groups));
    }

    /**
     * Lister les événements d'un régiment (filtrés par la même ville).
     */
    public function events(Request $request, Regiment $regiment): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $events  = $regiment->events()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(EventResource::collection($events));
    }

    /**
     * Lister les membres d'un régiment.
     * Admin : tout ; Chef régiment : uniquement son régiment (Policy).
     */
    public function users(Request $request, Regiment $regiment): JsonResponse
    {
        $this->authorize('viewMembers', $regiment);

        $perPage = min($request->query('per_page', 15), 50);
        $users   = $regiment->users()
            ->with('grade')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(UserResource::collection($users));
    }

    /**
     * Affecter un chef à un régiment (admin uniquement).
     */
    public function assignChef(Request $request, Regiment $regiment): JsonResponse
    {
        $this->authorize('assignChef', $regiment);

        $validated = $request->validate([
            'chef_id' => 'required|exists:users,id',
        ]);

        // Mettre à jour le rôle de l'utilisateur désigné comme chef
        $chef = User::findOrFail($validated['chef_id']);
        $chef->update(['role' => 'chef_regiment', 'regiment_id' => $regiment->id]);

        $regiment->update(['chef_id' => $validated['chef_id']]);

        return response()->json([
            'message'  => 'Chef de régiment affecté.',
            'regiment' => new RegimentResource($regiment->fresh('chef')),
        ]);
    }
}
