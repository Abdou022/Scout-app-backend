<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{

    use AuthorizesRequests;
    /**
     * Lister les utilisateurs selon le scope du rôle authentifié.
     * Admin : tous ; Chef régiment : son régiment ; Chef groupe : son groupe.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $authUser = $request->user();
        $perPage  = min($request->query('per_page', 15), 50);

        $query = User::with(['ville', 'grade'])->orderBy('created_at', 'desc');

        // Filtrer selon le scope du rôle
        if ($authUser->isChefRegiment()) {
            // Le chef de régiment ne voit que les membres de son régiment
            $query->where('regiment_id', $authUser->regiment_id);
        } elseif ($authUser->isChefGroupe()) {
            // Le chef de groupe ne voit que les membres de son groupe
            $query->where('group_id', $authUser->group_id);
        }

        return response()->json(UserResource::collection($query->paginate($perPage)));
    }

    /**
     * Afficher un utilisateur spécifique avec vérification du scope.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load(['ville', 'regiment', 'group', 'grade']);

        return response()->json(new UserResource($user));
    }

    /**
     * Mettre à jour un utilisateur (admin uniquement).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'nom'         => 'sometimes|string|max:100',
            'prenom'      => 'sometimes|string|max:100',
            'email'       => 'sometimes|email|unique:users,email,' . $user->id,
            'telephone'   => 'nullable|string|max:20',
            'role'        => 'sometimes|in:admin,chef_regiment,chef_groupe,candidat',
            'ville_id'    => 'sometimes|exists:villes,id',
            'regiment_id' => 'nullable|exists:regiments,id',
            'group_id'    => 'nullable|exists:groups,id',
            'grade_id'    => 'nullable|exists:grades,id',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Supprimer un utilisateur (admin uniquement).
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    /**
     * Afficher le grade d'un utilisateur.
     */
    public function showGrade(User $user): JsonResponse
    {
        $user->load('grade');

        return response()->json([
            'grade' => $user->grade,
        ]);
    }

    /**
     * Attribuer ou modifier le grade d'un utilisateur.
     * Admin peut attribuer à tout le monde ; chef groupe uniquement aux membres du sien.
     */
    public function assignGrade(Request $request, User $user): JsonResponse
    {
        $this->authorize('assignGrade', $user);

        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
        ]);

        $user->update(['grade_id' => $validated['grade_id']]);

        return response()->json([
            'message' => 'Grade mis à jour.',
            'user'    => new UserResource($user->fresh(['grade'])),
        ]);
    }
}
