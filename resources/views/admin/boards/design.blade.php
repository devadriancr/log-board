@php
    // After a failed save only the modal that was submitted shows what the user typed.
    $old = fn (string $form, string $key, $default = null) => old('_form') === $form ? old($key, $default) : $default;
    $hasHome = $boards->contains('is_home', true);
@endphp

<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('design.title') }}</flux:card.heading>

                <flux:card.actions>
                    <flux:modal.trigger name="board-settings">
                        <flux:button variant="outline" icon="cog-6-tooth">{{ __('design.board_settings') }}</flux:button>
                    </flux:modal.trigger>

                    <flux:button
                        variant="outline"
                        icon="computer-desktop"
                        :href="route('admin.applications.create', ['board' => $board->id, 'from' => 'design'])"
                    >
                        {{ __('design.new_application') }}
                    </flux:button>

                    <flux:button
                        variant="outline"
                        icon="rectangle-group"
                        :href="route('admin.cards.create', ['board' => $board->id, 'from' => 'design'])"
                    >
                        {{ __('design.new_card') }}
                    </flux:button>

                    <flux:button
                        variant="outline"
                        icon="document-text"
                        :href="route('admin.cards.create', ['board' => $board->id, 'from' => 'design', 'type' => 'text'])"
                    >
                        {{ __('cards.new_text_short') }}
                    </flux:button>

                    @if ($cards->isNotEmpty())
                        <flux:button id="design-save" type="button" variant="outline" icon="check">
                            {{ __('design.save') }}
                        </flux:button>
                    @endif
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body>
                <nav
                    id="board-tabs"
                    class="-mt-1 mb-5 flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700"
                    data-url="{{ route('admin.boards.reorder') }}"
                    data-token="{{ csrf_token() }}"
                    data-error="{{ __('design.messages.error') }}"
                >
                    @foreach ($boards as $item)
                        <a
                            data-board="{{ $item->id }}"
                            @if ($item->is_home) data-home title="{{ __('boards.fields.is_home') }}" @else draggable="true" @endif
                            href="{{ route('admin.boards.design', $item) }}"
                            @class([
                                '-mb-px flex items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium',
                                'cursor-grab active:cursor-grabbing' => ! $item->is_home,
                                'border-zinc-800 text-zinc-900 dark:border-white dark:text-white' => $item->id === $board->id,
                                'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white' => $item->id !== $board->id,
                            ])
                            @if ($item->id === $board->id) aria-current="page" @endif
                        >
                            @if ($item->is_home)
                                <flux:icon name="lock-closed" variant="micro" class="size-3.5 text-zinc-400" />
                            @endif
                            {{ $item->name }}
                        </a>
                    @endforeach

                    <flux:modal.trigger name="new-board">
                        <button
                            type="button"
                            data-add
                            class="-mb-px flex items-center border-b-2 border-transparent px-3 py-2 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white"
                            aria-label="{{ __('design.new_board') }}"
                            title="{{ __('design.new_board') }}"
                        >
                            <flux:icon name="plus" variant="micro" class="size-4" />
                        </button>
                    </flux:modal.trigger>
                </nav>

                @if ($cards->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="rectangle-group" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('design.empty') }}</flux:text>
                    </div>
                @else
                    <div class="mb-1 flex text-center font-mono text-xs text-zinc-400 dark:text-zinc-500" aria-hidden="true">
                        <div class="w-7 shrink-0"></div>
                        <div class="mx-px grid flex-1 grid-cols-12">
                            @foreach (range('A', 'L') as $column)
                                <span>{{ $column }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex">
                    <div id="board-rows" class="w-7 shrink-0 pt-px text-center font-mono text-xs text-zinc-400 dark:text-zinc-500" aria-hidden="true"></div>

                    <div
                        id="board-design"
                        class="grid-stack board-guides flex-1"
                        data-url="{{ route('admin.boards.design.update', $board) }}"
                        data-token="{{ csrf_token() }}"
                        data-saved="{{ __('design.messages.saved') }}"
                        data-error="{{ __('design.messages.error') }}"
                    >
                        @foreach ($cards as $card)
                            <div
                                class="grid-stack-item"
                                gs-id="{{ $card->id }}"
                                gs-x="{{ $card->grid_x }}"
                                gs-y="{{ $card->grid_y }}"
                                gs-w="{{ $card->grid_w }}"
                                gs-h="{{ $card->grid_h }}"
                            >
                                <div @if ($card->colorName()) data-card-color="{{ $card->colorName() }}" @endif @class([
                                    'grid-stack-item-content design-card flex flex-col overflow-hidden rounded-xl border border-zinc-300 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900',
                                    'opacity-60' => ! $card->is_active,
                                ])>
                                    <div class="gs-handle flex cursor-move items-center justify-between gap-2 border-b border-zinc-200 px-3 py-2 dark:border-zinc-600 @if ($card->colorName()) card-head @endif">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <flux:icon name="bars-3" variant="micro" class="size-4 shrink-0 text-zinc-400" />
                                            {{-- A text card has no header: this bar is only the handle to move it --}}
                                            @if ($card->isText() && count($card->content) > 1)
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('cards.slides_count', ['count' => count($card->content)]) }}</span>
                                            @endif
                                            @unless ($card->isText())
                                                @if ($card->icon)
                                                    <flux:icon :name="$card->icon" class="size-5 shrink-0 text-zinc-600 dark:text-zinc-300" />
                                                @endif
                                                <span class="truncate text-sm font-medium text-zinc-800 dark:text-white">{{ $card->title }}</span>
                                            @endunless
                                        </div>

                                        <div class="flex shrink-0 items-center gap-2">
                                            <span data-size class="design-size rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-xs text-zinc-500 dark:bg-zinc-600 dark:text-zinc-200">{{ $card->grid_w }}×{{ $card->grid_h }}</span>

                                            <flux:button
                                                size="xs"
                                                variant="outline"
                                                icon="pencil-square"
                                                :href="route('admin.cards.edit', ['card' => $card, 'from' => 'design'])"
                                                :aria-label="__('common.actions.edit')"
                                                :title="__('common.actions.edit')"
                                            />

                                            <flux:modal.trigger :name="'delete-card-'.$card->id">
                                                <flux:button
                                                    size="xs"
                                                    variant="outline"
                                                    color="red"
                                                    icon="trash"
                                                    :aria-label="__('common.actions.delete')"
                                                    :title="__('common.actions.delete')"
                                                />
                                            </flux:modal.trigger>
                                        </div>
                                    </div>
                                    @if ($card->isText())
                                        <div class="rich-content flex-1 overflow-y-auto px-3 py-2 text-zinc-700 dark:text-zinc-200">{!! $card->content[0] ?? '' !!}</div>
                                    @else
                                    <div class="flex-1 overflow-y-auto px-2 py-1.5 text-sm">
                                        @if ($card->links->isEmpty())
                                            <p class="px-1 text-zinc-400">{{ __('design.no_links') }}</p>
                                        @else
                                            {{-- 12 columns like the cards: each link can be dragged and resized by cells (at least 4, so 3 per row at most) --}}
                                            <div class="grid-stack link-grid">
                                                @foreach ($card->links as $link)
                                                    <div
                                                        class="grid-stack-item"
                                                        gs-id="{{ $link->id }}"
                                                        gs-x="{{ $link->grid_x }}"
                                                        gs-y="{{ $link->grid_y }}"
                                                        gs-w="{{ $link->grid_w }}"
                                                        gs-min-w="4"
                                                        gs-h="1"
                                                    >
                                                        <div class="grid-stack-item-content flex cursor-grab items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-2 active:cursor-grabbing dark:border-zinc-500 dark:bg-zinc-600/60">
                                                            <span data-order class="w-4 shrink-0 text-right font-mono text-xs text-zinc-400" title="{{ __('links.fields.sort_order') }}">{{ $link->sort_order }}</span>
                                                            <a
                                                                draggable="false"
                                                                href="{{ route('admin.links.edit', ['link' => $link, 'from' => 'design']) }}"
                                                                title="{{ $link->label }}"
                                                                @class([
                                                                    'min-w-0 truncate text-zinc-700 hover:text-zinc-900 hover:underline dark:text-zinc-200 dark:hover:text-white',
                                                                    'line-through opacity-60' => ! $link->is_active,
                                                                ])
                                                            >
                                                                {{ $link->label }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>


                                    <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-600">
                                        <flux:button
                                            size="xs"
                                            variant="outline"
                                            icon="plus"
                                            class="w-full justify-center"
                                            :href="route('admin.links.create', ['card' => $card->id, 'from' => 'design'])"
                                            :aria-label="__('design.new_link')"
                                            :title="__('design.new_link')"
                                        >
                                            <span class="design-label truncate">{{ __('design.new_link') }}</span>
                                        </flux:button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    </div>
                @endif
            </flux:card.body>
        </flux:card>

        {{-- After a failed save, open again the modal that was submitted so its errors are seen --}}
        @if ($errors->any() && in_array(old('_form'), ['settings', 'new'], true))
            <div x-data x-init="$nextTick(() => $flux.modal(@js(old('_form') === 'new' ? 'new-board' : 'board-settings')).show())"></div>
        @endif

        {{-- Modals live outside the grid so GridStack never moves them --}}
        <flux:modal name="board-settings" class="w-full max-w-lg">
            <form method="POST" action="{{ route('admin.boards.update', $board) }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="from" value="design">
                <input type="hidden" name="_form" value="settings">
                <input type="hidden" name="sort_order" value="{{ $board->sort_order }}">
                <input type="hidden" name="is_home" value="{{ (int) $board->is_home }}">

                <flux:heading size="lg">{{ __('design.board_settings') }}</flux:heading>

                <flux:input name="name" type="text" :label="__('boards.fields.name')" :value="$old('settings', 'name', $board->name)" required />

                <flux:input name="slug" type="text" :label="__('boards.fields.slug')" :placeholder="__('boards.placeholders.slug')" :value="$old('settings', 'slug', $board->slug)" required />

                <x-listbox
                    name="link_style"
                    :label="__('boards.fields.link_style')"
                    :options="collect(\App\Enums\LinkStyle::cases())->mapWithKeys(fn ($style) => [$style->value => $style->label()])"
                    :value="$old('settings', 'link_style', $board->link_style?->value)"
                />

                <flux:checkbox name="is_active" value="1" :label="__('boards.fields.is_active')" :checked="(bool) $old('settings', 'is_active', $board->is_active)" />

                {{-- Delete asks for a second click; its form is outside this one (forms cannot nest) and is reached with form="" --}}
                <div class="flex flex-wrap items-center justify-between gap-2" x-data="{ confirm: false }" x-on:modal-close.document="confirm = false">
                    <div class="flex items-center gap-2">
                        <flux:button
                            type="button"
                            variant="outline"
                            color="red"
                            icon="trash"
                            x-show="! confirm"
                            x-on:click="confirm = true"
                            :disabled="$cards->isNotEmpty()"
                        >
                            {{ __('design.delete_board') }}
                        </flux:button>

                        <span x-show="confirm" x-cloak class="flex items-center gap-2">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('boards.delete.title', ['name' => $board->name]) }}</span>

                            <flux:button type="submit" form="delete-board-form" variant="outline" color="red" icon="trash">{{ __('common.actions.delete') }}</flux:button>
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <flux:modal.close>
                            <flux:button variant="outline" icon="x-mark">{{ __('common.actions.cancel') }}</flux:button>
                        </flux:modal.close>

                        <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
                    </div>
                </div>

                @if ($cards->isNotEmpty())
                    <flux:text class="text-xs">{{ __('design.delete_board_blocked') }}</flux:text>
                @endif
            </form>

            <form id="delete-board-form" method="POST" action="{{ route('admin.boards.destroy', $board) }}" hidden>
                @csrf
                @method('DELETE')
                <input type="hidden" name="from" value="design">
            </form>
        </flux:modal>

        <flux:modal name="new-board" class="w-full max-w-lg">
            <form method="POST" action="{{ route('admin.boards.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="from" value="design">
                <input type="hidden" name="_form" value="new">
                <input type="hidden" name="sort_order" value="{{ $boards->count() + 1 }}">

                <flux:heading size="lg">{{ __('design.new_board') }}</flux:heading>

                <flux:input name="name" type="text" :label="__('boards.fields.name')" :value="$old('new', 'name')" required />

                <flux:input name="slug" type="text" :label="__('boards.fields.slug')" :placeholder="__('boards.placeholders.slug')" :value="$old('new', 'slug')" required />

                <x-listbox
                    name="link_style"
                    :label="__('boards.fields.link_style')"
                    :options="collect(\App\Enums\LinkStyle::cases())->mapWithKeys(fn ($style) => [$style->value => $style->label()])"
                    :value="$old('new', 'link_style', \App\Enums\LinkStyle::Button->value)"
                />

                <div class="flex flex-col gap-3">
                    <flux:checkbox name="is_active" value="1" :label="__('boards.fields.is_active')" :checked="(bool) $old('new', 'is_active', true)" />

                    {{-- Only one board can be the home: while there is one, this option is locked --}}
                    <div>
                        <flux:checkbox name="is_home" value="1" :label="__('boards.fields.is_home')" :checked="! $hasHome && (bool) $old('new', 'is_home')" :disabled="$hasHome" />

                        @if ($hasHome)
                            <flux:text class="mt-1 ps-6 text-xs">{{ __('design.home_taken') }}</flux:text>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="outline" icon="x-mark">{{ __('common.actions.cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
                </div>
            </form>
        </flux:modal>

        @foreach ($cards as $card)
            <flux:modal :name="'delete-card-'.$card->id" class="max-w-md">
                <form method="POST" action="{{ route('admin.cards.destroy', $card) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="from" value="design">

                    <flux:heading size="lg">{{ __('cards.delete.title', ['name' => $card->title]) }}</flux:heading>

                    @if ($card->links->isNotEmpty())
                        <flux:text>{{ __('design.delete_blocked') }}</flux:text>
                    @endif

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="outline" icon="x-mark">{{ __('common.actions.cancel') }}</flux:button>
                        </flux:modal.close>

                        <flux:button variant="outline" color="red" icon="trash" type="submit" :disabled="$card->links->isNotEmpty()">{{ __('common.actions.delete') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endforeach
    </div>
</x-layouts.app>
