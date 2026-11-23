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
    <body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="flex items-center gap-2 border-b border-zinc-200 bg-white px-2 py-1.5 shadow-xs sm:px-3 dark:border-zinc-800 dark:bg-zinc-900">
            <a href="{{ route('home') }}" class="shrink-0 px-1" aria-label="YKM">
                <img src="{{ asset('images/ykm.svg') }}" alt="YKM" class="h-6 w-auto dark:brightness-125">
            </a>

            <nav class="flex min-w-0 flex-1 gap-1 overflow-x-auto" aria-label="Tableros">
                @if ($boards->count() > 1)
                    @foreach ($boards as $item)
                        <a
                            href="{{ $item->is_home ? route('home') : route('boards.show', ['board' => $item->slug]) }}"
                            @class([
                                'whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium',
                                // the red of the logo marks the board you are in
                                'bg-zinc-100 text-zinc-900 shadow-[inset_0_-2px_0_var(--color-brand-red)] dark:bg-zinc-800 dark:text-white' => $board && $item->id === $board->id,
                                'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => ! $board || $item->id !== $board->id,
                            ])
                            @if ($board && $item->id === $board->id) aria-current="page" @endif
                        >
                            {{ $item->name }}
                        </a>
                    @endforeach
                @endif
            </nav>

            <div class="flex shrink-0 items-center gap-1">
                <flux:dropdown x-data align="end">
                    <flux:button variant="subtle" size="sm" square class="group" :aria-label="__('appearance.title')" :title="__('appearance.title')">
                        <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
                        <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
                        <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                        <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">{{ __('appearance.light') }}</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">{{ __('appearance.dark') }}</flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">{{ __('appearance.system') }}</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                @auth
                    {{-- The circle with the initials: profile, admin (only if allowed) and log out --}}
                    <flux:dropdown position="bottom" align="end">
                        <button
                            type="button"
                            class="flex size-8 items-center justify-center rounded-full border border-zinc-300 bg-zinc-100 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                            aria-label="{{ __('public.account') }}"
                            title="{{ __('public.account') }}"
                        >
                            {{ auth()->user()->initials() }}
                        </button>

                        <flux:menu class="w-56">
                            <div class="grid px-2 py-1.5 text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
                            </div>

                            <flux:menu.separator />

                            <flux:menu.item icon="user" :href="route('settings.profile')">{{ __('public.profile') }}</flux:menu.item>

                            @can('dashboard.view')
                                <flux:menu.item icon="cog-6-tooth" :href="route('dashboard')">{{ __('public.admin') }}</flux:menu.item>
                            @endcan

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    {{ __('public.logout') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button variant="ghost" size="sm" icon="arrow-right-end-on-rectangle" :href="route('login')">
                        {{ __('public.login') }}
                    </flux:button>
                @endauth
            </div>
        </header>

        <main class="px-2 py-3 sm:px-3">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
