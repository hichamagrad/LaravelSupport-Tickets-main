<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    Log::info('Private channel authorization request', [
        'user_id' => $user->id,
        'ticket_id' => $ticketId,
        'user_roles' => $user->getRoleNames()
    ]);
    
    // Allow access to any authenticated user for now (for testing)
    return true;
    
    /* Original authorization logic - uncomment this later
    $ticket = Ticket::find($ticketId);
    
    if (!$ticket) {
        return false;
    }
    
    // Allow access if the user is the ticket creator
    if ($user->id === $ticket->user_id) {
        return true;
    }
    
    // Allow access if the user is assigned to the ticket
    if ($user->id === $ticket->assigned_to) {
        return true;
    }
    
    // Allow access if the user is an admin or agent
    return $user->hasAnyRole(['admin', 'agent']);
    */
});

// Public channels don't need authorization
Broadcast::channel('public.ticket.{ticketId}', function ($user = null, $ticketId) {
    Log::info('Public channel access', [
        'user_id' => $user ? $user->id : 'unauthenticated',
        'ticket_id' => $ticketId
    ]);
    
    return true;
});
