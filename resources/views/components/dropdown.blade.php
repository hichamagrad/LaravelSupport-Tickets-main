<div class="relative inline-block text-left">
    <!-- Trigger button -->
    {{ $trigger }}
    
    <!-- Dropdown menu -->
    <div 
        x-show="isProfileMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @click.away="closeProfileMenu"
        @keydown.escape="closeProfileMenu"
        class="absolute right-0 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="menu-button"
        tabindex="-1"
    >
        <div class="py-1" role="none">
            {{ $content }}
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

