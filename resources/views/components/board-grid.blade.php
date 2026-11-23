@props(['board', 'cards'])

@use('App\Enums\LinkStyle')

@php
    $isButton = $board->link_style === LinkStyle::Button;

    // Botón: caja con borde, flecha siempre visible. Enlace: texto subrayado con icono, sin caja.
    $itemClasses = $isButton
        ? 'rounded-lg border border-zinc-200 bg-white px-3 py-1.5 dark:border-zinc-600 dark:bg-zinc-800'
        : 'py-0.5';

    $linkClasses = $isButton
        ? 'transition hover:border-accent hover:bg-zinc-50 hover:shadow-xs dark:hover:border-accent dark:hover:bg-white/5'
        : '';

    $labelClasses = $isButton
        ? 'text-sm font-medium text-zinc-800 dark:text-white'
        : 'text-sm font-medium text-zinc-900 underline decoration-zinc-300 underline-offset-4 group-hover:decoration-current dark:text-white dark:decoration-zinc-500';

    $arrowClasses = $isButton
        ? 'text-zinc-400 group-hover:text-accent-content'
        : 'text-zinc-400 opacity-0 transition group-hover:text-zinc-700 group-hover:opacity-100 dark:group-hover:text-zinc-200';
@endphp

<div class="board-grid-wrap">
    <div class="board-cards">
        @foreach ($cards as $card)
            <div
                class="board-card"
                style="--x: {{ $card->grid_x + 1 }}; --w: {{ $card->grid_w }}; --y: {{ $card->grid_y + 1 }}; --h: {{ $card->grid_h }}"
            >
                {{-- The card keeps the size it was given in the designer: when its content does not fit, the body scrolls --}}
                <flux:card body="divided" class="flex h-full flex-col overflow-hidden" :data-card-color="$card->colorName()">
                    {{-- Text cards have no header: the title is part of the content --}}
                    @unless ($card->isText())
                    <flux:card.header @class(['card-head', 'bg-zinc-50 dark:bg-white/5' => ! $card->colorName()])>
                        <flux:card.heading size="lg" class="flex items-center gap-2">
                            @if ($card->icon)
                                <flux:icon :name="$card->icon" class="size-5 shrink-0" />
                            @endif
                            {{ $card->title }}
                        </flux:card.heading>
                    </flux:card.header>
                    @endunless

                    @php($slides = $card->isText() ? $card->content : [])

                    {{-- With several notices the carousel scrolls inside itself, so the card body only clips --}}
                    <flux:card.body @class(['min-h-0 flex-1', 'overflow-y-auto' => count($slides) < 2, 'overflow-hidden' => count($slides) > 1])>
                        @if ($card->isText() && count($slides) > 1)
                            {{-- Carousel: one notice at a time, changing every slide_seconds; it pauses while the pointer or focus is on it --}}
                            <div
                                class="flex h-full flex-col gap-2"
                                x-data="{
                                    i: 0,
                                    n: {{ count($slides) }},
                                    paused: false,
                                    timer: null,
                                    go(k) { this.i = (k + this.n) % this.n },
                                    init() {
                                        if (! matchMedia('(prefers-reduced-motion: reduce)').matches) {
                                            this.timer = setInterval(() => this.paused || this.go(this.i + 1), {{ $card->slide_seconds * 1000 }})
                                        }
                                    },
                                    destroy() { clearInterval(this.timer) },
                                }"
                                x-on:mouseenter="paused = true"
                                x-on:mouseleave="paused = false"
                                x-on:focusin="paused = true"
                                x-on:focusout="paused = false"
                            >
                                <div class="min-h-0 flex-1 overflow-y-auto">
                                    @foreach ($slides as $k => $html)
                                        <div
                                            x-show="i === {{ $k }}"
                                            @if ($k) x-cloak @endif
                                            x-transition:enter="transition-opacity duration-300"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            class="rich-content"
                                        >{!! $html !!}</div>
                                    @endforeach
                                </div>

                                <div class="flex shrink-0 items-center justify-center gap-1.5">
                                    <button type="button" x-on:click="go(i - 1)" aria-label="{{ __('public.previous') }}" class="rounded p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        <flux:icon name="chevron-left" variant="micro" class="size-4" />
                                    </button>

                                    @foreach ($slides as $k => $html)
                                        <button
                                            type="button"
                                            x-on:click="go({{ $k }})"
                                            aria-label="{{ __('public.slide', ['n' => $k + 1]) }}"
                                            class="size-2 rounded-full transition"
                                            x-bind:class="i === {{ $k }} ? 'bg-accent' : 'bg-zinc-300 dark:bg-zinc-600'"
                                        ></button>
                                    @endforeach

                                    <button type="button" x-on:click="go(i + 1)" aria-label="{{ __('public.next') }}" class="rounded p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                                        <flux:icon name="chevron-right" variant="micro" class="size-4" />
                                    </button>
                                </div>
                            </div>
                        @elseif ($card->isText())
                            <div class="rich-content">{!! $slides[0] ?? '' !!}</div>
                        @else
                        <div class="board-links grid gap-1.5">
                            @foreach ($card->links as $link)
                                @php($place = sprintf('--x: %d; --w: %d; --y: %d', $link->grid_x + 1, $link->grid_w, $link->grid_y + 1))
                                @if ($link->options->isEmpty())
                                    <a
                                        href="{{ $link->urlTemplate() }}"
                                        @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                        style="{{ $place }}"
                                        class="board-link group flex items-center gap-3 {{ $itemClasses }} {{ $linkClasses }}"
                                    >
                                        @unless ($isButton)
                                            <flux:icon name="link" variant="micro" class="size-4 shrink-0 text-zinc-400" />
                                        @endunless

                                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                            <span class="truncate {{ $labelClasses }}" title="{{ $link->label }}">{{ $link->label }}</span>

                                            @if ($link->show_description && $link->description)
                                                <span class="truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $link->description }}">{{ $link->description }}</span>
                                            @endif
                                        </span>

                                        <span class="flex shrink-0 items-center gap-1.5">
                                            @if ($link->application->requires_auth)
                                                <flux:icon name="lock-closed" variant="micro" class="size-4 text-zinc-400" :title="__('public.requires_auth')" />
                                            @endif

                                            <flux:icon name="arrow-up-right" variant="micro" class="size-4 {{ $arrowClasses }}" />
                                        </span>
                                    </a>
                                @else
                                    <div
                                        style="{{ $place }}"
                                        class="board-link flex flex-wrap items-center justify-between gap-x-3 gap-y-1.5 {{ $itemClasses }}"
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
                                        <div class="flex min-w-32 flex-1 items-center gap-2.5">
                                            @unless ($isButton)
                                                <flux:icon name="link" variant="micro" class="size-4 shrink-0 text-zinc-400" />
                                            @endunless

                                            <span class="flex min-w-0 flex-col gap-0.5">
                                                <span class="truncate text-sm font-medium text-zinc-800 dark:text-white" title="{{ $link->label }}">{{ $link->label }}</span>

                                                @if ($link->show_description && $link->description)
                                                    <span class="truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $link->description }}">{{ $link->description }}</span>
                                                @endif
                                            </span>

                                            @if ($link->application->requires_auth)
                                                <flux:icon name="lock-closed" variant="micro" class="size-4 shrink-0 text-zinc-400" :title="__('public.requires_auth')" />
                                            @endif
                                        </div>

                                        <div class="ml-auto flex items-center gap-2">
                                            <div class="w-36">
                                                <x-listbox
                                                    x-model="param"
                                                    :options="$link->options->pluck('label', 'value')"
                                                    :value="$link->options->first()->value"
                                                    size="sm"
                                                />
                                            </div>

                                            <flux:button type="button" size="sm" variant="outline" icon="arrow-up-right" x-on:click="go()">
                                                {{ __('common.actions.open') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </flux:card.body>
                </flux:card>
            </div>
        @endforeach
    </div>
</div>
