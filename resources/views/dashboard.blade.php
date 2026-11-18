<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('dashboard.title') }}</flux:card.heading>
            </flux:card.header>

            <flux:card.body>
                @if ($boards->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="squares-2x2" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('dashboard.empty') }}</flux:text>

                        <flux:button variant="outline" icon="plus" :href="route('admin.boards.create')" wire:navigate>
                            {{ __('boards.create.title') }}
                        </flux:button>
                    </div>
                @else
                    <div x-data="{ tab: {{ $boards->first()->id }} }">
                        <div class="-mt-1 mb-5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-b border-zinc-200 dark:border-zinc-700">
                            <nav class="flex flex-wrap gap-1">
                                @foreach ($boards as $board)
                                    <button
                                        type="button"
                                        class="-mb-px border-b-2 px-3 py-2 text-sm font-medium"
                                        x-on:click="tab = {{ $board->id }}"
                                        x-bind:class="tab === {{ $board->id }}
                                            ? 'border-zinc-800 text-zinc-900 dark:border-white dark:text-white'
                                            : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white'"
                                        x-bind:aria-current="tab === {{ $board->id }} ? 'page' : null"
                                    >
                                        {{ $board->name }}
                                    </button>
                                @endforeach
                            </nav>

                            @foreach ($boards as $board)
                                <div class="flex items-center gap-2 pb-2" x-show="tab === {{ $board->id }}" @if (! $loop->first) x-cloak @endif>
                                    <flux:button size="sm" variant="outline" icon="squares-plus" :href="route('admin.boards.design', $board)">
                                        {{ __('dashboard.design') }}
                                    </flux:button>

                                    <flux:button size="sm" variant="outline" icon="arrow-top-right-on-square" :href="$board->publicUrl()" target="_blank">
                                        {{ __('dashboard.view') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>

                        @foreach ($boards as $board)
                            <div x-show="tab === {{ $board->id }}" @if (! $loop->first) x-cloak @endif>
                                @if ($board->cards->isEmpty())
                                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                                        <flux:icon name="rectangle-group" class="size-8 text-zinc-400" />
                                        <flux:text>{{ __('public.empty') }}</flux:text>
                                    </div>
                                @else
                                    <x-board-grid :board="$board" :cards="$board->cards" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card.body>
        </flux:card>
    </div>
</x-layouts.app>
