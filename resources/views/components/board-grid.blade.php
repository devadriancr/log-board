@props(['board', 'cards'])

@use('App\Enums\LinkStyle')

@php
    $isButton = $board->link_style === LinkStyle::Button;

    $itemClasses = $isButton
        ? 'rounded-lg border border-zinc-300 bg-white px-3.5 py-3 shadow-xs dark:border-zinc-500 dark:bg-zinc-700'
        : 'py-1';

    $labelClasses = $isButton
        ? 'text-sm font-medium text-zinc-800 dark:text-white'
        : 'text-sm font-medium text-zinc-900 underline decoration-zinc-300 underline-offset-4 group-hover:decoration-current dark:text-white dark:decoration-zinc-500';
@endphp

<div class="board-grid-wrap">
    <div class="grid grid-cols-12 gap-5">
    @foreach ($cards as $card)
        <div
            class="board-card col-span-12"
            style="--x: {{ $card->grid_x + 1 }}; --w: {{ $card->grid_w }}; --y: {{ $card->grid_y + 1 }}; --h: {{ $card->grid_h }}"
        >
            <flux:card body="divided" class="h-full">
                <flux:card.header>
                    <flux:card.heading size="lg">{{ $card->title }}</flux:card.heading>
                </flux:card.header>

                <flux:card.body>
                    <div class="board-links grid gap-3" style="--cols: {{ $card->link_columns }}">
                        @foreach ($card->links as $link)
                            @if ($link->options->isEmpty())
                                <a
                                    href="{{ $link->urlTemplate() }}"
                                    @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                    @class([
                                        'group flex items-start justify-between gap-3',
                                        $itemClasses,
                                        'transition hover:bg-zinc-50 dark:hover:bg-zinc-600/75' => $isButton,
                                    ])
                                >
                                    <span class="flex min-w-0 flex-col gap-0.5">
                                        <span class="{{ $labelClasses }}">{{ $link->label }}</span>

                                        @if ($link->show_description && $link->description)
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $link->description }}</span>
                                        @endif
                                    </span>

                                    <span class="mt-0.5 flex shrink-0 items-center gap-1.5 text-zinc-400 group-hover:text-zinc-700 dark:group-hover:text-zinc-200">
                                        @if ($link->application->requires_auth)
                                            <flux:icon name="lock-closed" variant="micro" class="size-4" :title="__('public.requires_auth')" />
                                        @endif

                                        <flux:icon name="arrow-up-right" variant="micro" class="size-4" />
                                    </span>
                                </a>
                            @else
                                <div
                                    class="{{ $itemClasses }}"
                                    x-data="{
                                        param: @js($link->options->first()->value),
                                        url: @js($link->urlTemplate()),
                                        blank: @js($link->open_in_new_tab),
                                        go() {
                                            const target = this.url.replace('{param}', encodeURIComponent(this.param));
                                            this.blank ? window.open(target, '_blank', 'noopener') : (window.location.href = target);
                                        },
                                    }"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="flex min-w-0 flex-col gap-0.5">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-white">{{ $link->label }}</span>

                                            @if ($link->show_description && $link->description)
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $link->description }}</span>
                                            @endif
                                        </span>

                                        @if ($link->application->requires_auth)
                                            <flux:icon name="lock-closed" variant="micro" class="mt-0.5 size-4 shrink-0 text-zinc-400" :title="__('public.requires_auth')" />
                                        @endif
                                    </div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <div class="min-w-0 flex-1">
                                            <x-listbox
                                                x-model="param"
                                                :options="$link->options->pluck('label', 'value')"
                                                :value="$link->options->first()->value"
                                            />
                                        </div>

                                        <flux:button type="button" variant="outline" icon="arrow-up-right" x-on:click="go()">
                                            {{ __('common.actions.open') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </flux:card.body>
            </flux:card>
        </div>
    @endforeach
</div>
</div>
