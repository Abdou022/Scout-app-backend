<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Lister les présences selon le scope du rôle.
     * Admin : toutes ; Chef régiment : membres de son régiment ; Chef groupe : membres de son groupe.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $authUser = $request->user();
        $perPage  = min($request->query('per_page', 15), 50);

        $query = Attendance::with('user')->orderBy('created_at', 'desc');

        if ($authUser->isChefRegiment()) {
            // Filtrer sur les présences des membres de son régiment
            $query->whereHas('user', fn($q) => $q->where('regiment_id', $authUser->regiment_id));
        } elseif ($authUser->isChefGroupe()) {
            // Filtrer sur les présences des membres de son groupe
            $query->whereHas('user', fn($q) => $q->where('group_id', $authUser->group_id));
        }

        return response()->json(AttendanceResource::collection($query->paginate($perPage)));
    }

    /**
     * Créer une entrée de présence.
     * Vérifie le type d'entité pointée (Event ou Activity) et le scope du créateur.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $attendance = Attendance::create($request->validated());

        return response()->json(new AttendanceResource($attendance->load('user')), 201);
    }

    /**
     * Afficher une entrée de présence.
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $this->authorize('view', $attendance);
        $attendance->load('user');

        return response()->json(new AttendanceResource($attendance));
    }

    /**
     * Mettre à jour une présence (statut).
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);
        $attendance->update($request->validated());

        return response()->json(new AttendanceResource($attendance));
    }

    /**
     * Supprimer une présence (admin uniquement).
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);
        $attendance->delete();

        return response()->json(['message' => 'Présence supprimée.']);
    }

    /**
     * Lister les présences d'un utilisateur spécifique.
     * Admin, chef régiment (scope), chef groupe (scope), ou le candidat lui-même.
     */
    public function userAttendances(Request $request, User $user): JsonResponse
    {
        // La Policy vérifie le droit de consulter les présences de cet utilisateur
        $this->authorize('viewUserAttendances', [Attendance::class, $user]);

        $perPage    = min($request->query('per_page', 15), 50);
        $attendance = Attendance::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(AttendanceResource::collection($attendance));
    }
}
