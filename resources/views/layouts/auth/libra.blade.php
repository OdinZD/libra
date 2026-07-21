<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-libra-warm-white antialiased">
        <div class="flex min-h-svh">
            <!-- Left Branding Panel -->
            <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col lg:items-center lg:justify-center bg-gradient-to-br from-libra-amber-50 via-libra-coral-50 to-libra-purple-50">
                <!-- Floating decorative shapes -->
                <div class="absolute top-16 left-12 h-20 w-20 rounded-2xl bg-libra-amber-200/40 animate-float"></div>
                <div class="absolute top-32 right-16 h-14 w-14 rounded-full bg-libra-coral-200/40 animate-float-delayed"></div>
                <div class="absolute bottom-24 left-20 h-16 w-16 rounded-full bg-libra-purple-200/40 animate-float"></div>
                <div class="absolute bottom-40 right-24 h-12 w-12 rounded-2xl bg-libra-amber-300/30 animate-float-delayed"></div>
                <div class="absolute top-1/2 left-1/3 h-10 w-10 rounded-full bg-libra-coral-300/20 animate-pulse-soft"></div>

                <!-- Branding content -->
                <div class="relative z-10 flex flex-col items-center gap-6 px-12 text-center">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo-libra.png" alt="Libra" class="size-12 rounded-lg" />
                        <span class="text-4xl font-bold text-gradient-libra">Libra</span>
                    </div>
                    <p class="max-w-sm text-lg font-medium text-libra-warm-text-secondary">
                        Vaša platforma za učenje i podučavanje
                    </p>
                    <p class="max-w-xs text-sm text-libra-warm-text-secondary/70">
                        Organizirajte sesije, pratite napredak i povežite se s učenicima — sve na jednom mjestu.
                    </p>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="flex w-full flex-col items-center justify-center gap-6 p-6 md:p-10 lg:w-1/2">
                <div class="flex w-full max-w-sm flex-col gap-2">
                    <!-- Mobile logo (hidden on lg+) -->
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium lg:hidden mb-4" wire:navigate>
                        <span class="flex items-center gap-2">
                            <img src="/images/logo-libra.png" alt="Libra" class="size-9 rounded-lg" />
                            <span class="text-2xl font-bold text-gradient-libra">Libra</span>
                        </span>
                    </a>
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
