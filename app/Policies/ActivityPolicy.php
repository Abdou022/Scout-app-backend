<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Voir la liste des activités :
     * Admin : toutes
     * Chef groupe ou assistant : activités de leur groupe
     * Candidat : activités du groupe dont il est membre
     */
    public function viewAny(User $user): bool
    {
        return true; // La restriction de scope est appliquée dans le controller
    }

    /**
     * Voir une activité spécifique :
     * Admin, chef groupe (son groupe), candidat (membre du groupe).
     */
    public function view(User $user, Activity $activity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Chef groupe ou assistant qui gèrent ce groupe
        if ($user->isChefGroupe()) {
            return $activity->group_id === $user->group_id;
        }

        // Candidat membre du groupe
        if ($user->isCandidat()) {
            return $activity->group_id === $user->group_id;
        }

        return false;
    }

    /**
     * Créer une activité : chef de groupe ou assistant pour leur groupe.
     */
    public function create(User $user, int $groupId): bool
    {
        if ($user->isChefGroupe() || $user->role === 'assistant') {
            return $user->group_id === $groupId;
        }

        return false;
    }

    /**
     * Modifier une activité : chef de groupe ou assistant pour leur groupe.
     */
    public function update(User $user, Activity $activity): bool
    {
        if ($user->isChefGroupe() || $user->role === 'assistant') {
            return $activity->group_id === $user->group_id;
        }

        return false;
    }

    /**
     * Supprimer une activité : uniquement le chef de groupe (pas l'assistant).
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->isChefGroupe() && $activity->group_id === $user->group_id;
    }

    /**
     * Voir les présences d'une activité : admin ou chef de groupe.
     */
    public function viewAttendance(User $user, Activity $activity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefGroupe() && $activity->group_id === $user->group_id;
    }
}
