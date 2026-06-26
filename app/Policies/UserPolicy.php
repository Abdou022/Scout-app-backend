<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Seul un admin peut mettre à jour n'importe quel utilisateur.
     * Un utilisateur peut consulter son propre profil.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // Admin voit tout le monde
        if ($authUser->isAdmin()) {
            return true;
        }

        // Chef régiment voit les membres de son régiment
        if ($authUser->isChefRegiment()) {
            return $targetUser->regiment_id === $authUser->regiment_id;
        }

        // Chef groupe voit les membres de son groupe
        if ($authUser->isChefGroupe()) {
            return $targetUser->group_id === $authUser->group_id;
        }

        // Un candidat peut se voir lui-même
        return $authUser->id === $targetUser->id;
    }

    /**
     * Seul un admin peut modifier un utilisateur.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->isAdmin();
    }

    /**
     * Seul un admin peut supprimer un utilisateur.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        return $authUser->isAdmin();
    }

    /**
     * Attribution d'un grade :
     * - Admin peut attribuer un grade à n'importe qui
     * - Chef de groupe peut attribuer un grade uniquement aux membres de son groupe
     */
    public function assignGrade(User $authUser, User $targetUser): bool
    {
        if ($authUser->isAdmin()) {
            return true;
        }

        if ($authUser->isChefGroupe()) {
            return $targetUser->group_id === $authUser->group_id;
        }

        return false;
    }

    /**
     * Affichage de la liste des utilisateurs selon le scope du rôle.
     */
    public function viewAny(User $authUser): bool
    {
        return in_array($authUser->role, ['admin', 'chef_regiment', 'chef_groupe']);
    }
}
