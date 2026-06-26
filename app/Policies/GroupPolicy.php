<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    /**
     * Admin peut créer dans n'importe quel régiment.
     * Chef de régiment peut créer uniquement dans son régiment.
     */
    public function create(User $user, ?int $regimentId = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            // Vérifie que le groupe sera créé dans son propre régiment
            return $user->regiment_id === $regimentId;
        }

        return false;
    }

    /**
     * Modification d'un groupe :
     * Admin peut tout modifier.
     * Chef de régiment peut modifier les groupes de son régiment.
     */
    public function update(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            return $group->regiment_id === $user->regiment_id;
        }

        return false;
    }

    /**
     * Suppression : mêmes règles que la modification.
     */
    public function delete(User $user, Group $group): bool
    {
        return $this->update($user, $group);
    }

    /**
     * Voir les membres d'un groupe.
     * Admin, chef régiment (son régiment), chef groupe (son groupe).
     */
    public function viewMembers(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            return $group->regiment_id === $user->regiment_id;
        }

        if ($user->isChefGroupe()) {
            return $group->id === $user->group_id;
        }

        return false;
    }

    /**
     * Supprimer un membre du groupe :
     * Admin peut supprimer n'importe qui.
     * Chef de groupe peut supprimer uniquement de son groupe.
     */
    public function removeMember(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefGroupe() && $group->id === $user->group_id;
    }

    /**
     * Changer le chef d'un groupe :
     * Admin ou chef de régiment (pour les groupes de son régiment).
     */
    public function assignChef(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefRegiment() && $group->regiment_id === $user->regiment_id;
    }

    /**
     * Changer l'assistant d'un groupe :
     * Admin ou chef de groupe (pour son propre groupe).
     */
    public function assignAssistant(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefGroupe() && $group->id === $user->group_id;
    }

    /**
     * Voir les candidatures d'un groupe.
     */
    public function viewApplications(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            return $group->regiment_id === $user->regiment_id;
        }

        if ($user->isChefGroupe()) {
            return $group->id === $user->group_id;
        }

        return false;
    }
}
