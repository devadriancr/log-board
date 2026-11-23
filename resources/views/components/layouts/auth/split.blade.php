<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
        <div class="flex min-h-dvh items-center justify-center px-6 py-12">
            <div class="flex w-full max-w-sm flex-col gap-8">
                <a href="{{ route('home') }}" class="flex justify-center" wire:navigate>
                    <img src="{{ asset('images/ykm-full.svg') }}" alt="{{ config('app.name') }}" class="h-16 w-auto dark:brightness-125">
                </a>

                {{ $slot }}
            </div>
        </div>
        @fluxScripts
    </body>
</html>
