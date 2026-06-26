<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\GroupApplicationResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Lister tous les groupes (visibles de tous les utilisateurs authentifiés).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 15), 50);
        $groups  = Group::with(['regiment', 'chef', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(GroupResource::collection($groups));
    }

    /**
     * Créer un groupe.
     * Admin : n'importe quel régiment.
     * Chef régiment : uniquement son régiment (vérifié par Policy).
     */
    public function store(StoreGroupRequest $request): JsonResponse
    {
        // Vérification du scope : le chef de régiment ne peut créer que dans son régiment
        $this->authorize('create', [Group::class, $request->regiment_id]);

        $group = Group::create(array_merge($request->validated(), [
            'created_by' => $request->user()->id,
        ]));

        return response()->json(new GroupResource($group->load('regiment', 'chef', 'assistant')), 201);
    }

    /**
     * Afficher un groupe.
     */
    public function show(Group $group): JsonResponse
    {
        $group->load(['regiment', 'chef', 'assistant']);

        return response()->json(new GroupResource($group));
    }

    /**
     * Mettre à jour un groupe.
     * Admin ou chef régiment (scope régiment).
     */
    public function update(UpdateGroupRequest $request, Group $group): JsonResponse
    {
        $this->authorize('update', $group);
        $group->update($request->validated());

        return response()->json(new GroupResource($group->fresh(['regiment', 'chef', 'assistant'])));
    }

    /**
     * Supprimer un groupe.
     */
    public function destroy(Group $group): JsonResponse
    {
        $this->authorize('delete', $group);
        $group->delete();

        return response()->json(['message' => 'Groupe supprimé.']);
    }

    /**
     * Lister les membres du groupe.
     * La Policy vérifie le scope du rôle.
     */
    public function members(Request $request, Group $group): JsonResponse
    {
        $this->authorize('viewMembers', $group);

        $perPage = min($request->query('per_page', 15), 50);
        $members = $group->members()
            ->with('grade')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(UserResource::collection($members));
    }

    /**
     * Retirer un membre du groupe.
     * Admin ou chef de groupe (son groupe uniquement).
     */
    public function removeMember(Request $request, Group $group, User $user): JsonResponse
    {
        $this->authorize('removeMember', $group);

        // Vérifier que l'utilisateur est bien membre de ce groupe
        if ($user->group_id !== $group->id) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas membre de ce groupe.'], 404);
        }

        $user->update(['group_id' => null]);

        return response()->json(['message' => 'Membre retiré du groupe.']);
    }

    /**
     * Affecter un chef au groupe.
     * Admin ou chef régiment (pour les groupes de son régiment).
     */
    public function assignChef(Request $request, Group $group): JsonResponse
    {
        $this->authorize('assignChef', $group);

        $validated = $request->validate([
            'chef_id' => 'required|exists:users,id',
        ]);

        $chef = User::findOrFail($validated['chef_id']);
        $chef->update(['role' => 'chef_groupe', 'group_id' => $group->id]);
        $group->update(['chef_id' => $validated['chef_id']]);

        return response()->json([
            'message' => 'Chef de groupe affecté.',
            'group'   => new GroupResource($group->fresh('chef')),
        ]);
    }

    /**
     * Affecter un assistant au groupe.
     * Admin ou chef de groupe (pour son propre groupe).
     */
    public function assignAssistant(Request $request, Group $group): JsonResponse
    {
        $this->authorize('assignAssistant', $group);

        $validated = $request->validate([
            'assistant_id' => 'required|exists:users,id',
        ]);

        $assistant = User::findOrFail($validated['assistant_id']);
        $assistant->update(['group_id' => $group->id]);
        $group->update(['assistant_id' => $validated['assistant_id']]);

        return response()->json([
            'message' => 'Assistant affecté.',
            'group'   => new GroupResource($group->fresh('assistant')),
        ]);
    }

    /**
     * Lister les activités d'un groupe.
     */
    public function activities(Request $request, Group $group): JsonResponse
    {
        $authUser = $request->user();

        // Vérification du droit d'accès selon le rôle
        $canView = $authUser->isAdmin()
            || ($authUser->isChefRegiment() && $group->regiment_id === $authUser->regiment_id)
            || ($authUser->isChefGroupe() && $group->id === $authUser->group_id)
            || ($authUser->isCandidat() && $group->id === $authUser->group_id);

        if (!$canView) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $perPage    = min($request->query('per_page', 15), 50);
        $activities = $group->activities()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(ActivityResource::collection($activities));
    }

    /**
     * Lister les candidatures pour un groupe.
     */
    public function applications(Request $request, Group $group): JsonResponse
    {
        $this->authorize('viewApplications', $group);

        $perPage      = min($request->query('per_page', 15), 50);
        $applications = $group->applications()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(GroupApplicationResource::collection($applications));
    }
}
