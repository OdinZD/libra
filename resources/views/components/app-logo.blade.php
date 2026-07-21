@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-9 rounded-md" />
        </x-slot>
        <span class="text-lg font-bold text-black tracking-tight">Libra</span>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-9 rounded-md" />
        </x-slot>
        <span class="text-lg font-bold text-black tracking-tight">Libra</span>
    </flux:brand>
@endif
