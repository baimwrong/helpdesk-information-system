<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can reply to the ticket.
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    /**
     * Determine whether the user can add internal staff notes.
     */
    public function addNote(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the status of the ticket.
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update priority / SLA.
     */
    public function updatePriority(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can submit a satisfaction rating.
     */
    public function rate(User $user, Ticket $ticket): bool
    {
        if ($ticket->user_id !== $user->id) {
            return false;
        }

        return in_array($ticket->status, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED], true);
    }

    /**
     * Determine whether the user can download attachments of the ticket.
     */
    public function downloadAttachment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
