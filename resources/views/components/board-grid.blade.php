@props(['board', 'cards'])

@use('App\Enums\LinkStyle')

@php
    $isButton = $board->link_style === LinkStyle::Button;

    // Botón: caja con borde, flecha siempre visible. Enlace: texto subrayado con icono, sin caja.
    $itemClasses = $isButton
        ? 'rounded-lg border border-zinc-300 bg-white px-4 py-3 shadow-xs dark:border-zinc-500 dark:bg-zinc-700'
        : 'py-1.5';

    $linkClasses = $isButton
        ? 'transition hover:border-zinc-400 hover:bg-zinc-50 dark:hover:border-zinc-400 dark:hover:bg-zinc-600/75'
        : '';

    $labelClasses = $isButton
        ? 'text-sm font-medium text-zinc-800 dark:text-white'
        : 'text-sm font-medium text-zinc-900 underline decoration-zinc-300 underline-offset-4 group-hover:decoration-current dark:text-white dark:decoration-zinc-500';

    $arrowClasses = $isButton
        ? 'text-zinc-400 group-hover:text-zinc-700 dark:group-hover:text-zinc-200'
        : 'text-zinc-400 opacity-0 transition group-hover:text-zinc-700 group-hover:opacity-100 dark:group-hover:text-zinc-200';
@endphp

<div class="board-grid-wrap">
    <div class="grid grid-cols-12 gap-5">
        @foreach ($cards as $card)
            <div
                class="board-card col-span-12"
                style="--x: {{ $card->grid_x + 1 }}; --w: {{ $card->grid_w }}; --y: {{ $card->grid_y + 1 }}; --h: {{ $card->grid_h }}"
            >
                <flux:card body="divided" class="h-full">
                    <flux:card.header :style="$card->headerStyle()">
                        <flux:card.heading size="lg" class="flex items-center gap-2" :style="$card->color ? 'color: inherit' : null">
                            @if ($card->icon)
                                <flux:icon :name="$card->icon" class="size-5 shrink-0" />
                            @endif
                            {{ $card->title }}
                        </flux:card.heading>
                    </flux:card.header>

                    <flux:card.body>
                        @if ($card->isText())
                            <div class="rich-content">{!! $card->content !!}</div>
                        @else
                        <div class="board-links grid gap-3">
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
                                        class="board-link flex flex-wrap items-center justify-between gap-x-4 gap-y-2 {{ $itemClasses }}"
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
                        @endif
                    </flux:card.body>
                </flux:card>
            </div>
        @endforeach
    </div>
</div>
