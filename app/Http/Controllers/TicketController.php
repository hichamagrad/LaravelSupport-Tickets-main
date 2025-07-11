<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Requests\TicketRequest;
use Coderflex\LaravelTicket\Models\Label;
use Illuminate\Database\Eloquent\Builder;
use Coderflex\LaravelTicket\Models\Category;
use App\Notifications\AssignedTicketNotification;
use App\Notifications\NewTicketCreatedNotification;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = Ticket::with('user', 'categories', 'labels', 'assignedToUser')
            ->when($request->has('status'), function (Builder $query) use ($request) {
                return $query->where('status', $request->input('status'));
            })
            ->when($request->has('priority'), function (Builder $query) use ($request) {
                return $query->withPriority($request->input('priority'));
            })
            ->when($request->has('category'), function (Builder $query) use ($request) {
                return $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('id', $request->input('category'));
                });
            })
            ->when(optional(auth()->user())->hasRole('agent'), function (Builder $query) {
                return $query->where('assigned_to', auth()->id());
            })
            ->when(optional(auth()->user())->hasRole('user'), function (Builder $query) {
                return $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate();

        return view('tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $labels = Label::visible()->pluck('name', 'id');

        $categories = Category::visible()->pluck('name', 'id');

        $users = User::role('agent')->orderBy('name')->pluck('name', 'id');

        return view('tickets.create', compact('labels', 'categories', 'users'));
    }

    public function store(TicketRequest $request)
    {
        $ticket = Ticket::create(array_merge(
            $request->only('title', 'message', 'status', 'priority'),
            ['user_id' => auth()->id()]
        ));

        if ($request->filled('categories')) {
            $ticket->attachCategories($request->input('categories'));
        }

        if ($request->filled('labels')) {
            $ticket->attachLabels($request->input('labels'));
        }

        if ($request->input('assigned_to')) {
            $ticket->assignTo($request->input('assigned_to'));
            User::find($request->input('assigned_to'))->notify(new AssignedTicketNotification($ticket));
        } else {
            User::role('admin')
                ->each(fn ($user) => $user->notify(new NewTicketCreatedNotification($ticket)));
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Make sure the file exists and is valid
                if ($file->isValid()) {
                    // Store the file and add it to the media collection
                    $ticket->addMedia($file)
                           ->toMediaCollection('tickets_attachments');
                }
            }
        }

        return to_route('tickets.index');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);
    
        $ticket->load('media');
    
        $messages = $ticket->messages()
        ->latest()
        ->paginate(20);
    
        $messages->load('user');

        return view('tickets.show', compact('ticket', 'messages'));
    }
    

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        $labels = Label::visible()->pluck('name', 'id');

        $categories = Category::visible()->pluck('name', 'id');

        $users = User::role('agent')->orderBy('name')->pluck('name', 'id');

        return view('tickets.edit', compact('ticket', 'labels', 'categories', 'users'));
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update($request->only('title', 'message', 'status', 'priority', 'assigned_to'));

        $ticket->syncCategories($request->input('categories'));

        $ticket->syncLabels($request->input('labels'));

        if ($ticket->wasChanged('assigned_to')) {
            User::find($request->input('assigned_to'))->notify(new AssignedTicketNotification($ticket));
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Make sure the file exists and is valid
                if ($file->isValid()) {
                    // Store the file and add it to the media collection
                    $ticket->addMedia($file)
                           ->toMediaCollection('tickets_attachments');
                }
            }
        }

        return to_route('tickets.index');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return to_route('tickets.index');
    }

    public function upload(Request $request)
    {
        $paths = [];

        if ($request->file('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('tmp', 'public');
            }
        }

        return $paths;
    }

    public function close(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->close();

        return to_route('tickets.show', $ticket);
    }

    public function reopen(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->reopen();

        return to_route('tickets.show', $ticket);
    }

    public function archive(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->archive();

        return to_route('tickets.show', $ticket);
    }
}
