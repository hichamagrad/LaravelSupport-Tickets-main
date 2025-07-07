<!DOCTYPE html>
<html x-data="data" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Scripts -->
    <script src="{{ asset('js/init-alpine.js') }}"></script>
    
    <!-- Add direct styles for background and dropdown -->
    <style>
        /* Override gray background to white only in the main content area */
        .bg-gray-50 {
            background-color: white !important;
        }
        
        [x-cloak] { display: none !important; }
        .dropdown-open { display: block !important; }
        
        /* Direct logout link style - hidden by default */
        .direct-logout {
            position: fixed;
            top: 5px;
            right: 5px;
            z-index: 1000;
            background-color: #5CC8D7;
            color: #331B3F;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body>
    <!-- Direct logout link - hidden -->
    <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
        @csrf
        <button type="submit" class="direct-logout">
            Logout
        </button>
    </form>
    
    <div class="flex h-screen bg-gray-50" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <!-- Desktop sidebar -->
        @include('layouts.navigation')
        <!-- Mobile sidebar -->
        <!-- Backdrop -->
        @include('layouts.navigation-mobile')
        <div class="flex flex-col flex-1 w-full z-10 relative">
            @include('layouts.top-menu')
            <main class="h-full overflow-y-auto z-20 relative">
                <div class="container px-6 mx-auto grid z-30 relative pointer-events-auto">
                    @if (isset($header))
                        <h2 class="my-6 text-2xl font-semibold text-gray-700">
                            {{ $header }}
                        </h2>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputElement = document.querySelector('input[type="file"]');
            
            if (inputElement && window.FilePond) {
                const pond = FilePond.create(inputElement, {
                    server: {
                        url: "{{ route('tickets.upload') }}",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
