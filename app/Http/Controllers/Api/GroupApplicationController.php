<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupApplicationRequest;
use App\Http\Resources\GroupApplicationResource;
use App\Models\GroupApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupApplicationController extends Controller
{
    /**
     * Lister les candidatures selon le scope du rôle authentifié.
     */
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $perPage  = min($request->query('per_page', 15), 50);

        $query = GroupApplication::with(['user', 'group'])
            ->orderBy('created_at', 'desc');

        if ($authUser->isChefRegiment()) {
            // Le chef de régiment voit les candidatures des groupes de son régiment
            $query->whereHas('group', fn($q) => $q->where('regiment_id', $authUser->regiment_id));
        } elseif ($authUser->isChefGroupe()) {
            // Le chef de groupe voit uniquement les candidatures de son groupe
            $query->where('group_id', $authUser->group_id);
        } elseif ($authUser->isCandidat()) {
            // Un candidat ne voit que ses propres candidatures
            $query->where('user_id', $authUser->id);
        }

        return response()->json(GroupApplicationResource::collection($query->paginate($perPage)));
    }

    /**
     * Soumettre une candidature (candidat uniquement).
     */
    public function store(StoreGroupApplicationRequest $request): JsonResponse
    {
        $this->authorize('create', GroupApplication::class);

        $authUser = $request->user();

        // Vérifier qu'une candidature en attente n'existe pas déjà pour ce groupe
        $exists = GroupApplication::where('user_id', $authUser->id)
            ->where('group_id', $request->group_id)
            ->where('statut', 'en_attente')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Vous avez déjà une candidature en attente pour ce groupe.',
            ], 422);
        }

        $application = GroupApplication::create([
            'user_id'  => $authUser->id,
            'group_id' => $request->group_id,
            'statut'   => 'en_attente',
        ]);

        return response()->json(new GroupApplicationResource($application->load('group')), 201);
    }

    /**
     * Afficher une candidature avec vérification du scope.
     */
    public function show(Request $request, GroupApplication $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json(new GroupApplicationResource($application->load(['user', 'group'])));
    }

    /**
     * Accepter une candidature.
     * Admin ou chef de groupe (son groupe uniquement).
     * Met à jour le statut et assigne le groupe au candidat.
     */
    public function accept(Request $request, GroupApplication $application): JsonResponse
    {
        $this->authorize('accept', $application);

        if ($application->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette candidature ne peut plus être modifiée.'], 422);
        }

        $application->update(['statut' => 'acceptee']);

        // Assigner le candidat au groupe et son régiment
        $group = $application->group()->with('regiment')->first();
        $application->user->update([
            'group_id'    => $application->group_id,
            'regiment_id' => $group->regiment_id,
        ]);

        return response()->json([
            'message'     => 'Candidature acceptée.',
            'application' => new GroupApplicationResource($application),
        ]);
    }

    /**
     * Refuser une candidature.
     * Admin ou chef de groupe (son groupe uniquement).
     */
    public function refuse(Request $request, GroupApplication $application): JsonResponse
    {
        $this->authorize('refuse', $application);

        if ($application->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette candidature ne peut plus être modifiée.'], 422);
        }

        $application->update(['statut' => 'refusee']);

        return response()->json([
            'message'     => 'Candidature refusée.',
            'application' => new GroupApplicationResource($application),
        ]);
    }

    /**
     * Supprimer une candidature.
     * Uniquement le candidat lui-même, si le statut est 'en_attente'.
     */
    public function destroy(Request $request, GroupApplication $application): JsonResponse
    {
        $this->authorize('delete', $application);
        $application->delete();

        return response()->json(['message' => 'Candidature supprimée.']);
    }
}
