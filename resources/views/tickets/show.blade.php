<?php use Illuminate\Support\Str; ?>
    <x-app-layout>
    <x-slot name="header">
        {{ $ticket->title }}
    </x-slot>
    @hasanyrole('admin|agent')
        <div class="mb-4 flex justify-end space-x-2">
            @if($ticket->isOpen())
                <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <x-primary-button>
                        Close
                    </x-primary-button>
                </form>
            @elseif(!$ticket->isArchived())
                <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <x-primary-button>
                        Reopen
                    </x-primary-button>
                </form>

                <form action="{{ route('tickets.archive', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <x-primary-button>
                        Archive
                    </x-primary-button>
                </form>
            @endif
        </div>
    @endhasanyrole

    <div class="space-y-6">
        <!-- Message principal du ticket -->
        <div class="rounded-lg bg-white p-4 shadow">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Message</h2>
            <p class="text-gray-700">{{ $ticket->message }}</p>
        </div>

        <!-- Pièces jointes -->
        @if($ticket->getMedia('tickets_attachments')->count())
            <div class="rounded-lg bg-white p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Attachments</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($ticket->getMedia('tickets_attachments') as $media)
                        <div class="border rounded p-2 shadow">
                            @if(Str::startsWith($media->mime_type, 'image/'))
                                <a href="{{ $media->getUrl() }}" target="_blank">
                                    <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->file_name }}" class="w-full h-auto rounded">
                                </a>
                                <p class="mt-1 text-sm text-gray-600">{{ $media->file_name }}</p>
                            @else
                                <a href="{{ route('attachment-download', $media) }}" class="text-blue-600 hover:underline">
                                    {{ $media->file_name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Zone de réponse + messages -->
        <div class="rounded-lg bg-white p-4 shadow space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Messages</h2>

            <!-- Debug info -->
            <div class="bg-gray-100 p-3 rounded mb-4">
                <p class="text-sm font-medium mb-2">WebSocket Status:</p>
                <div id="websocket-status" class="text-sm">Initializing...</div>
            </div>

            <!-- Hidden ticket ID for JavaScript -->
            <input type="hidden" id="ticket-id" value="{{ $ticket->id }}">

            @if(!$ticket->isArchived())
                <form id="message-form" action="{{ route('message.store', $ticket) }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <div>
                        <textarea id="message-input" name="message"
                                  class="mt-1 block w-full h-32 rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring-primary-200 focus:ring focus:ring-opacity-50"
                                  required></textarea>
                        <div id="error-message" class="text-red-500 mt-1 hidden"></div>
                        @error('message')
                            <p class="text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-primary-button type="submit" id="submit-button" class="mt-4">
                        Submit
                    </x-primary-button>
                </form>
            @endif

            <!-- Liste des messages -->
            <div id="messages-container">                
                @foreach ($messages as $message)
                    <div class="border rounded-md p-4 bg-white mb-2">
                        <div class="text-sm text-gray-500">
                            {{ optional($message->user)->name ?? 'N/A' }} • {{ $message->created_at->diffForHumans() }}
                        </div>
                        <div class="mt-2 text-gray-700">
                            {{ $message->message }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div>
                {{ $messages->links() }}
            </div>
        </div>
    </div>

    <!-- Include the external JavaScript file -->
    <script src="{{ asset('js/ticket-messaging.js') }}"></script>
</x-app-layout>
