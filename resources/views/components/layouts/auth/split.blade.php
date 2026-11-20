<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
        <div class="grid min-h-dvh lg:grid-cols-[minmax(0,5fr)_minmax(0,4fr)]">
            <div class="relative hidden overflow-hidden bg-linear-to-br from-zinc-700 via-zinc-800 to-zinc-950 p-12 text-white lg:flex lg:flex-col">
                <div class="absolute inset-0 opacity-20 [background-image:radial-gradient(circle_at_1px_1px,white_1px,transparent_0)] [background-size:24px_24px]"></div>

                <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3 text-lg font-semibold" wire:navigate>
                    <img src="{{ asset('images/ykm-full.svg') }}" alt="" class="h-14 w-auto">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <p class="relative z-10 mt-auto max-w-sm text-3xl font-semibold leading-tight tracking-tight">
                    {{ __('login.tagline') }}
                </p>
            </div>

            <div class="relative flex items-center justify-center px-6 py-12">
                <x-appearance-switcher class="absolute right-6 top-6" />

                <div class="flex w-full max-w-sm flex-col gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 text-lg font-semibold text-zinc-900 lg:hidden dark:text-white" wire:navigate>
                        <img src="{{ asset('images/ykm.svg') }}" alt="" class="h-8 w-auto">
                        {{ config('app.name', 'Laravel') }}
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
