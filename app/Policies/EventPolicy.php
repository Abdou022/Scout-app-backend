<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Créer un événement :
     * Admin peut créer des événements de type 'ville'.
     * Chef de régiment peut créer des événements de type 'regiment' pour son régiment.
     */
    public function create(User $user, string $type, ?int $regimentId = null): bool
    {
        if ($user->isAdmin() && $type === 'ville') {
            return true;
        }

        if ($user->isChefRegiment() && $type === 'regiment') {
            // Le chef de régiment ne peut créer qu'un événement pour son propre régiment
            return $user->regiment_id === $regimentId;
        }

        return false;
    }

    /**
     * Voir un événement : uniquement les événements de la même ville que l'utilisateur.
     */
    public function view(User $user, Event $event): bool
    {
        return $event->ville_id === $user->ville_id;
    }

    /**
     * Modifier un événement :
     * Admin : tous les événements.
     * Chef de régiment : ses événements de type 'regiment'.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment() && $event->type === 'regiment') {
            // L'événement doit appartenir au régiment du chef
            return $event->regiment_id === $user->regiment_id;
        }

        return false;
    }

    /**
     * Supprimer un événement : mêmes règles que la modification.
     */
    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /**
     * Consulter les présences d'un événement :
     * Admin ou chef de régiment (pour les événements de son régiment).
     */
    public function viewAttendance(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefRegiment() && $event->regiment_id === $user->regiment_id;
    }
}
