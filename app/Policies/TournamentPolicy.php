<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    /**
     * Determine if the user can view any tournaments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the tournament.
     */
    public function view(User $user, Tournament $tournament): bool
    {
        return (int) $user->id === (int) $tournament->admin_id;
    }

    /**
     * Determine if the user can create tournaments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the tournament.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        return (int) $user->id === (int) $tournament->admin_id;
    }

    /**
     * Determine if the user can delete the tournament.
     */
    public function delete(User $user, Tournament $tournament): bool
    {
        return (int) $user->id === (int) $tournament->admin_id;
    }

    /**
     * Determine if the user can restore the tournament.
     */
    public function restore(User $user, Tournament $tournament): bool
    {
        return (int) $user->id === (int) $tournament->admin_id;
    }
}
