<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\AttendanceResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Lister les activités selon le scope du rôle.
     * Admin : toutes ; CG/assistant : celles de leur groupe ; Candidat : celles de son groupe.
     */
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $perPage  = min($request->query('per_page', 15), 50);

        $query = Activity::with(['group'])->orderBy('created_at', 'desc');

        if (!$authUser->isAdmin()) {
            // Tous les autres rôles ne voient que les activités de leur groupe
            if (!$authUser->group_id) {
                return response()->json(['data' => []]);
            }
            $query->where('group_id', $authUser->group_id);
        }

        return response()->json(ActivityResource::collection($query->paginate($perPage)));
    }

    /**
     * Créer une activité.
     * Chef de groupe ou assistant du groupe uniquement.
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        // La Policy vérifie que le créateur est chef/assistant du groupe demandé
        $this->authorize('create', [Activity::class, $request->group_id]);

        $activity = Activity::create(array_merge($request->validated(), [
            'created_by' => $request->user()->id,
        ]));

        return response()->json(new ActivityResource($activity->load('group')), 201);
    }

    /**
     * Afficher une activité.
     */
    public function show(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);
        $activity->load(['group', 'createdBy']);

        return response()->json(new ActivityResource($activity));
    }

    /**
     * Mettre à jour une activité.
     * Chef de groupe ou assistant (pour leur groupe).
     */
    public function update(UpdateActivityRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);
        $activity->update($request->validated());

        return response()->json(new ActivityResource($activity->fresh('group')));
    }

    /**
     * Supprimer une activité (chef de groupe uniquement, pas l'assistant).
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);
        $activity->delete();

        return response()->json(['message' => 'Activité supprimée.']);
    }

    /**
     * Lister les présences à une activité.
     * Admin ou chef de groupe.
     */
    public function attendance(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('viewAttendance', $activity);

        $perPage    = min($request->query('per_page', 15), 50);
        $attendance = $activity->attendances()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(AttendanceResource::collection($attendance));
    }
}
