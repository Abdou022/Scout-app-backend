<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de vérification du rôle utilisateur.
 * Utilisable avec un ou plusieurs rôles : ->middleware('CheckUser:admin,chef_regiment')
 */
class CheckUserRole
{
    /**
     * Vérifie que l'utilisateur authentifié possède l'un des rôles autorisés.
     *
     * @param string $roles Rôles acceptés, séparés par des virgules (ex: 'admin,chef_regiment')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // L'utilisateur doit être authentifié
        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si le rôle de l'utilisateur figure parmi les rôles autorisés
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Accès refusé. Rôle insuffisant.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
