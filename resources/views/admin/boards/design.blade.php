<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('design.title') }}</flux:card.heading>

                <flux:card.actions>
                    <flux:button
                        variant="outline"
                        icon="plus"
                        :href="route('admin.cards.create', ['board' => $board->id, 'from' => 'design'])"
                    >
                        {{ __('design.new_card') }}
                    </flux:button>

                    <flux:button
                        variant="outline"
                        icon="document-text"
                        :href="route('admin.cards.create', ['board' => $board->id, 'from' => 'design', 'type' => 'text'])"
                    >
                        {{ __('cards.new_text') }}
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
                                <div @class([
                                    'grid-stack-item-content design-card flex flex-col overflow-hidden rounded-xl border border-zinc-300 bg-white shadow-xs dark:border-zinc-600 dark:bg-zinc-700',
                                    'opacity-60' => ! $card->is_active,
                                ])>
                                    <div class="gs-handle flex cursor-move items-center justify-between gap-2 border-b border-zinc-200 px-3 py-2 dark:border-zinc-600" @if ($card->headerStyle()) style="{{ $card->headerStyle() }}" @endif>
                                        <div class="flex min-w-0 items-center gap-2">
                                            <flux:icon name="bars-3" variant="micro" class="size-4 shrink-0 text-zinc-400" />
                                            {{-- A text card has no header: this bar is only the handle to move it --}}
                                            @unless ($card->isText())
                                                @if ($card->icon)
                                                    <flux:icon :name="$card->icon" class="size-5 shrink-0 text-zinc-600 dark:text-zinc-300" />
                                                @endif
                                                <span class="truncate text-sm font-medium text-zinc-800 dark:text-white" @if ($card->color) style="color: inherit" @endif>{{ $card->title }}</span>
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
                                        </div>
                                    </div>
                                    @if ($card->isText())
                                        <div class="rich-content flex-1 overflow-y-auto px-3 py-2 text-zinc-700 dark:text-zinc-200">{!! $card->content !!}</div>
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
    </div>
</x-layouts.app>
