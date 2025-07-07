<a 
    {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center']) }}
    role="menuitem"
    tabindex="-1"
    @click="closeProfileMenu"
>
    @if(isset($icon))
        <span class="mr-3">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</a>
