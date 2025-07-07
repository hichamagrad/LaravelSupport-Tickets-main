<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Events\NewMessageEvent;
use App\Http\Requests\MessageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CommentEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(MessageRequest $request, Ticket $ticket)
    {
        try {
            Log::info('Message store method called', [
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'is_ajax' => $request->ajax() || $request->wantsJson()
            ]);
            
            // Create the message
            $message = $ticket->messages()->create($request->validated() + ['user_id' => auth()->user()->id]);

            // Send notifications
            $users = $ticket->messages()
                ->pluck('user_id')
                ->push($ticket->user_id)
                ->filter(fn ($user) => $user != auth()->user()->id)
                ->unique();

            Notification::send(User::findMany($users), new CommentEmailNotification($message));

            // Log before broadcasting
            Log::info('Broadcasting new message event', [
                'message_id' => $message->id,
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id()
            ]);

            // Broadcast the new message event
            event(new NewMessageEvent($message));

            // Prepare response data
            $responseData = [
                'success' => true,
                'id' => $message->id,
                'message' => $message->message,
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name
                ],
                'created_at' => $message->created_at->diffForHumans(),
                'timestamp' => $message->created_at->timestamp,
            ];
            
            Log::info('Message created successfully', $responseData);

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return new JsonResponse($responseData);
            }

            // Otherwise, redirect back to the ticket
            return to_route('tickets.show', $ticket);
        } catch (\Exception $e) {
            Log::error('Error creating message: ' . $e->getMessage(), [
                'exception' => $e,
                'ticket_id' => $ticket->id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'An error occurred while sending your message.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['message' => 'An error occurred while sending your message.']);
        }
    }

    /**
     * Get messages for a ticket since a specific ID
     * Used for polling to fetch new messages
     */
    public function getMessages(Request $request, Ticket $ticket)
    {
        try {
            $sinceId = $request->input('since', 0);
            
            $messages = $ticket->messages()
                ->with('user')
                ->where('id', '>', $sinceId)
                ->orderBy('id', 'asc')
                ->get();
            
            $formattedMessages = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name
                    ],
                    'created_at' => $message->created_at->diffForHumans(),
                    'timestamp' => $message->created_at->timestamp,
                ];
            });
            
            return new JsonResponse([
                'success' => true,
                'messages' => $formattedMessages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching messages: ' . $e->getMessage(), [
                'exception' => $e,
                'ticket_id' => $ticket->id
            ]);
            
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
