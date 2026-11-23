@php
    $fromDesign = request('from') === 'design';
    $cancelUrl = $fromDesign && $card->board_id
        ? route('admin.boards.design', $card->board_id)
        : (auth()->user()->can('cards.view') ? route('admin.cards.index') : route('dashboard'));
@endphp

<flux:card body="divided" class="w-full">
    <form method="POST" action="{{ $action }}">
        @csrf
        @isset($method)
            @method($method)
        @endisset

        @if ($fromDesign)
            <input type="hidden" name="from" value="design">
        @endif

        <input type="hidden" name="type" value="{{ old('type', $card->type) }}">

        <flux:card.body>
            <div class="grid gap-6 md:grid-cols-2">
                <x-listbox
                    name="board_id"
                    :label="__('cards.fields.board_id')"
                    :options="$boards"
                    :value="old('board_id', $card->board_id)"
                />

                {{-- Text cards have no header: no title, icon or colour (the title is written in the content) --}}
                @unless ($card->isText())
                <flux:input
                    name="title"
                    type="text"
                    :label="__('cards.fields.title')"
                    :value="old('title', $card->title)"
                    required
                    autofocus
                />

                @endunless

                <flux:input
                    name="grid_w"
                    type="number"
                    min="1"
                    max="12"
                    :label="__('cards.fields.grid_w')"
                    :value="old('grid_w', $card->grid_w)"
                    required
                />

                <flux:input
                    name="grid_h"
                    type="number"
                    min="1"
                    max="20"
                    :label="__('cards.fields.grid_h')"
                    :value="old('grid_h', $card->grid_h)"
                    required
                />

                @unless ($card->isText())
                {{-- x-data goes on a plain div: @js is not evaluated inside component tag attributes --}}
                <div x-data="{ icon: @js((string) old('icon', $card->icon)), search: '', open: false }">
                <flux:field>
                    <flux:label>{{ __('cards.fields.icon') }}</flux:label>

                    <input type="hidden" name="icon" x-bind:value="icon">

                    <div class="relative" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
                        <flux:button variant="outline" icon:trailing="chevron-down" class="w-full justify-between font-normal [&>span]:min-w-0 [&>span]:flex-1 [&>span]:text-start" x-on:click="open = !open">
                            <span class="flex items-center gap-2">
                                <span x-ref="preview" class="flex size-5 items-center justify-center [&>svg]:size-5"></span>
                                <span x-text="icon || @js(__('cards.fields.icon_none'))" x-bind:class="icon ? '' : 'text-zinc-400'"></span>
                            </span>
                        </flux:button>

                        <div x-show="open" x-cloak class="absolute start-0 top-full z-20 mt-1 flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-md dark:border-zinc-600 dark:bg-zinc-700">
                            <flux:input x-model="search" size="sm" icon="magnifying-glass" :placeholder="__('cards.fields.icon_search')" />

                            <button type="button" x-on:click="icon = ''; open = false" class="rounded-md px-2 py-1 text-start text-sm text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                {{ __('cards.fields.icon_none') }}
                            </button>

                            <div
                                x-ref="grid"
                                x-effect="icon; $refs.preview.innerHTML = $el.querySelector(`[data-icon='${icon}']`)?.innerHTML ?? ''"
                                class="grid max-h-64 grid-cols-6 gap-1 overflow-y-auto"
                            >
                                @foreach ($icons as $name)
                                    <button
                                        type="button"
                                        data-icon="{{ $name }}"
                                        title="{{ $name }}"
                                        x-show="$el.dataset.icon.includes(search.trim().toLowerCase())"
                                        x-on:click="icon = $el.dataset.icon; open = false"
                                        class="flex size-11 items-center justify-center rounded-md text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                        x-bind:class="icon === $el.dataset.icon && 'bg-zinc-200 dark:bg-zinc-600'"
                                    >
                                        <flux:icon :name="$name" class="size-5" />
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <flux:error name="icon" />
                </flux:field>
                </div>

                {{-- Palette colours: they colour the header, the border and the title of the card; the body stays white --}}
                <div x-data="{ color: @js((string) old('color', $card->color)) }">
                    <flux:field>
                        <flux:label>{{ __('cards.fields.color') }}</flux:label>

                        <input type="hidden" name="color" x-bind:value="color">

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                title="{{ __('cards.fields.color_none') }}"
                                aria-label="{{ __('cards.fields.color_none') }}"
                                x-on:click="color = ''"
                                class="flex h-10 w-11 items-center justify-center rounded-lg border-2 border-zinc-300 bg-white text-sm text-zinc-700 dark:border-zinc-500 dark:bg-zinc-700 dark:text-zinc-200"
                                x-bind:class="! color && 'ring-2 ring-zinc-500 ring-offset-2 ring-offset-white dark:ring-zinc-300 dark:ring-offset-zinc-800'"
                            >Aa</button>

                            @foreach (\App\Models\Card::COLORS as $key => $name)
                                <button
                                    type="button"
                                    data-card-color="{{ $key }}"
                                    title="{{ $name }}"
                                    aria-label="{{ $name }}"
                                    x-on:click="color = '{{ $key }}'"
                                    class="card-swatch flex h-10 w-11 items-center justify-center rounded-lg text-sm font-medium"
                                    x-bind:class="color === '{{ $key }}' && 'ring-2 ring-zinc-500 ring-offset-2 ring-offset-white dark:ring-zinc-300 dark:ring-offset-zinc-800'"
                                >Aa</button>
                            @endforeach

                            <span class="text-sm text-zinc-500 dark:text-zinc-400" x-text="color ? @js(\App\Models\Card::COLORS)[color] : @js(__('cards.fields.color_none'))"></span>
                        </div>

                        <flux:error name="color" />
                    </flux:field>
                </div>
                @endunless
                <div class="md:self-end md:pb-2.5">
                    <flux:checkbox
                        name="is_active"
                        value="1"
                        :label="__('cards.fields.is_active')"
                        :checked="(bool) old('is_active', $card->is_active)"
                    />
                </div>
            </div>

            @if ($card->isText())
                @php($slides = array_values((array) old('content', $card->content)) ?: [''])

                {{-- Each notice (slide) has its own editor; with several the public board shows them as a carousel --}}
                <div
                    id="rich-editor"
                    class="mt-6"
                    data-upload="{{ route('admin.uploads.image') }}"
                    data-token="{{ csrf_token() }}"
                    data-labels="{{ json_encode(__('cards.editor')) }}"
                >
                    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <flux:label>{{ __('cards.fields.slides') }}</flux:label>
                            <flux:text class="text-xs">{{ __('cards.slides_hint') }} {{ __('cards.markdown_hint') }}</flux:text>
                        </div>

                        <div class="w-44">
                            <flux:input
                                name="slide_seconds"
                                type="number"
                                min="3"
                                max="60"
                                size="sm"
                                :label="__('cards.fields.slide_seconds')"
                                :value="old('slide_seconds', $card->slide_seconds)"
                                required
                            />
                        </div>
                    </div>

                    <div data-slides>
                        @foreach ($slides as $html)
                            @include('admin.cards.slide', ['html' => $html, 'number' => $loop->iteration])
                        @endforeach
                    </div>

                    <template data-slide-template>
                        @include('admin.cards.slide', ['html' => '', 'number' => 0])
                    </template>

                    <flux:button type="button" variant="outline" icon="plus" data-add-slide>{{ __('cards.add_slide') }}</flux:button>

                    <flux:error name="content" />
                    <flux:error name="slide_seconds" />
                </div>
            @endif
        </flux:card.body>

        <flux:card.footer>
            <flux:card.actions>
                <flux:button variant="outline" icon="x-mark" :href="$cancelUrl">
                    {{ __('common.actions.cancel') }}
                </flux:button>

                <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
            </flux:card.actions>
        </flux:card.footer>
    </form>
</flux:card>
