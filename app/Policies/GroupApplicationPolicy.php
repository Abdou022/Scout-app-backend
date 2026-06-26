<?php

namespace App\Policies;

use App\Models\GroupApplication;
use App\Models\User;

class GroupApplicationPolicy
{
    /**
     * Listing des candidatures :
     * Admin : toutes
     * Chef régiment : celles des groupes de son régiment
     * Chef groupe : celles de son groupe
     * Candidat : uniquement les siennes
     */
    public function viewAny(User $user): bool
    {
        return true; // La restriction de scope est appliquée dans le controller
    }

    /**
     * Voir une candidature spécifique.
     */
    public function view(User $user, GroupApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isChefRegiment()) {
            // Vérifier que le groupe de la candidature appartient au régiment du chef
            return $application->group->regiment_id === $user->regiment_id;
        }

        if ($user->isChefGroupe()) {
            return $application->group_id === $user->group_id;
        }

        // Un candidat ne peut voir que ses propres candidatures
        return $application->user_id === $user->id;
    }

    /**
     * Soumettre une candidature : candidat uniquement.
     */
    public function create(User $user): bool
    {
        return $user->isCandidat();
    }

    /**
     * Accepter une candidature : admin ou chef groupe (pour son groupe).
     */
    public function accept(User $user, GroupApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isChefGroupe() && $application->group_id === $user->group_id;
    }

    /**
     * Refuser une candidature : mêmes règles que l'acceptation.
     */
    public function refuse(User $user, GroupApplication $application): bool
    {
        return $this->accept($user, $application);
    }

    /**
     * Supprimer une candidature : uniquement le candidat, si statut = en_attente.
     */
    public function delete(User $user, GroupApplication $application): bool
    {
        return $application->user_id === $user->id
            && $application->statut === 'en_attente';
    }
}
