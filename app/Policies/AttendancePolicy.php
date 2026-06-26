<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Voir la liste des présences :
     * Admin, chef régiment (son régiment), chef groupe (son groupe).
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'chef_regiment', 'chef_groupe']);
    }

    /**
     * Voir une présence spécifique.
     * Admin, chef régiment, chef groupe, ou le candidat lui-même.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            // Vérifier que l'utilisateur concerné appartient au régiment du chef
            return $attendance->user->regiment_id === $user->regiment_id;
        }

        if ($user->isChefGroupe()) {
            return $attendance->user->group_id === $user->group_id;
        }

        // Un candidat ne peut voir que ses propres présences
        return $attendance->user_id === $user->id;
    }

    /**
     * Créer une présence :
     * Admin : événements de type ville
     * Chef régiment : événements de son régiment
     * Chef groupe : activités de son groupe
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'chef_regiment', 'chef_groupe']);
    }

    /**
     * Modifier une présence selon le type de l'entité pointée.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Chef régiment pour les présences liées à des événements de son régiment
        if ($user->isChefRegiment() && $attendance->attendable_type === \App\Models\Event::class) {
            return $attendance->attendable->regiment_id === $user->regiment_id;
        }

        // Chef groupe pour les présences liées à des activités de son groupe
        if ($user->isChefGroupe() && $attendance->attendable_type === \App\Models\Activity::class) {
            return $attendance->attendable->group_id === $user->group_id;
        }

        return false;
    }

    /**
     * Supprimer une présence : admin uniquement.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Voir les présences d'un utilisateur spécifique.
     */
    public function viewUserAttendances(User $authUser, User $targetUser): bool
    {
        if ($authUser->isAdmin()) {
            return true;
        }

        if ($authUser->isChefRegiment()) {
            return $targetUser->regiment_id === $authUser->regiment_id;
        }

        if ($authUser->isChefGroupe()) {
            return $targetUser->group_id === $authUser->group_id;
        }

        // Candidat peut voir ses propres présences
        return $authUser->id === $targetUser->id;
    }
}
