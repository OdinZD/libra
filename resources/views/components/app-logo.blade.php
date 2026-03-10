@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="h-8 w-auto rounded-md" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="h-8 w-auto rounded-md" />
        </x-slot>
    </flux:brand>
@endif
