<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine if the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->admin_id;
    }

    /**
     * Determine if the user can create events.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->admin_id;
    }

    /**
     * Determine if the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->admin_id;
    }

    /**
     * Determine if the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->admin_id;
    }
}
