<?php

namespace App\Policies;

use App\Models\Regiment;
use App\Models\User;

class RegimentPolicy
{
    /**
     * Un admin peut tout faire.
     * Un chef de régiment ne peut modifier que son propre régiment.
     */
    public function update(User $user, Regiment $regiment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Le chef_id du régiment doit correspondre à l'utilisateur connecté
        return $user->isChefRegiment() && $regiment->chef_id === $user->id;
    }

    public function delete(User $user, Regiment $regiment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Listing des membres d'un régiment :
     * Admin peut tout voir, chef régiment uniquement les membres du sien.
     */
    public function viewMembers(User $user, Regiment $regiment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Le chef doit être chef de ce régiment précisément
        return $user->isChefRegiment() && $regiment->chef_id === $user->id;
    }

    /**
     * Changer le chef d'un régiment : admin uniquement.
     */
    public function assignChef(User $user, Regiment $regiment): bool
    {
        return $user->isAdmin();
    }
}
