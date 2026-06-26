<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur (rôle candidat par défaut).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'password'  => Hash::make($request->password),
            'role'      => 'candidat',
            'ville_id'  => $request->ville_id,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * Connexion d'un utilisateur existant.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Identifiants incorrects.',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * Déconnexion : révocation du token courant.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Retourner les informations de l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['ville', 'regiment', 'group', 'grade']);

        return response()->json(new UserResource($user));
    }
}
