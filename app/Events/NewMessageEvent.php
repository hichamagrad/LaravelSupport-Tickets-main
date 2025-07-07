<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('NewMessageEvent created', [
            'message_id' => $message->id,
            'ticket_id' => $message->ticket_id,
            'user_id' => $message->user_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $ticketId = $this->message->ticket_id;
        Log::info('Broadcasting on channels', [
            'private' => 'ticket.' . $ticketId,
            'public' => 'public.ticket.' . $ticketId
        ]);
        
        // Broadcast on both private and public channels to ensure delivery
        return [
            new PrivateChannel('ticket.' . $ticketId),
            new Channel('public.ticket.' . $ticketId)
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        Log::info('Broadcasting with name: new.message');
        return 'new.message';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $data = [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->diffForHumans(),
            'timestamp' => $this->message->created_at->timestamp,
            'formatted_time' => $this->message->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'ticket_id' => $this->message->ticket_id
        ];
        
        Log::info('Broadcasting data', $data);
        
        return $data;
    }
} 