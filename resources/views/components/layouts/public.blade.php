@props([
    'boards' => collect(),
    'board' => null,
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-800 dark:text-white">
        <header class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <x-app-logo />
                </a>

                <div class="flex items-center gap-3">
                    <x-appearance-switcher />

                    @auth
                        <flux:button variant="outline" icon="cog-6-tooth" :href="route('admin.boards.index')">
                            {{ __('public.admin') }}
                        </flux:button>
                    @else
                        <flux:button variant="outline" icon="arrow-right-end-on-rectangle" :href="route('login')">
                            {{ __('public.login') }}
                        </flux:button>
                    @endauth
                </div>
            </div>

            @if ($boards->count() > 1)
                <nav class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 sm:px-6" aria-label="Tableros">
                    @foreach ($boards as $item)
                        <a
                            href="{{ $item->is_home ? route('home') : route('boards.show', ['board' => $item->slug]) }}"
                            @class([
                                '-mb-px whitespace-nowrap border-b-2 px-3 py-2.5 text-sm font-medium',
                                'border-zinc-800 text-zinc-900 dark:border-white dark:text-white' => $board && $item->id === $board->id,
                                'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white' => ! $board || $item->id !== $board->id,
                            ])
                            @if ($board && $item->id === $board->id) aria-current="page" @endif
                        >
                            {{ $item->name }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
