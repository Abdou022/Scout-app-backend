<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['ville', 'regiment', 'group', 'grade']);

        return response()->json(new UserResource($user));
    }

    /**
     * Mettre à jour les informations générales du profil.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Modifier le mot de passe de l'utilisateur connecté.
     * Vérifie d'abord l'ancien mot de passe avant de le remplacer.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // Vérification que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    /**
     * Uploader et remplacer la photo de profil.
     * Stockage sur le disque 'public' (nécessite php artisan storage:link).
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();

        // Supprimer l'ancienne photo si elle existe
        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        // Stocker la nouvelle photo dans storage/app/public/profile_pics/
        $path = $request->file('photo')->store('profile_pics', 'public');
        $user->update(['profile_pic' => $path]);

        return response()->json([
            'message'     => 'Photo de profil mise à jour.',
            'profile_pic' => asset('storage/' . $path),
        ]);
    }
}
