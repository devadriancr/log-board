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

                    @if ($cards->isNotEmpty())
                        <flux:button id="design-save" type="button" variant="outline" icon="check">
                            {{ __('design.save') }}
                        </flux:button>
                    @endif
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body>
                <nav class="-mt-1 mb-5 flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
                    @foreach ($boards as $item)
                        <a
                            href="{{ route('admin.boards.design', $item) }}"
                            @class([
                                '-mb-px border-b-2 px-3 py-2 text-sm font-medium',
                                'border-zinc-800 text-zinc-900 dark:border-white dark:text-white' => $item->id === $board->id,
                                'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white' => $item->id !== $board->id,
                            ])
                            @if ($item->id === $board->id) aria-current="page" @endif
                        >
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
                    <div class="mb-1 grid grid-cols-12 text-center font-mono text-xs text-zinc-400 dark:text-zinc-500" aria-hidden="true">
                        @for ($column = 1; $column <= 12; $column++)
                            <span>{{ $column }}</span>
                        @endfor
                    </div>

                    <div
                        id="board-design"
                        class="grid-stack board-guides"
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
                                    <div class="gs-handle flex cursor-move items-center justify-between gap-2 border-b border-zinc-200 px-3 py-2 dark:border-zinc-600">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <flux:icon name="bars-3" variant="micro" class="size-4 shrink-0 text-zinc-400" />
                                            <span class="truncate text-sm font-medium text-zinc-800 dark:text-white">{{ $card->title }}</span>
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

                                    <ul class="flex-1 space-y-1 overflow-y-auto px-3 py-2 text-sm">
                                        @forelse ($card->links as $link)
                                            <li class="truncate">
                                                <a
                                                    href="{{ route('admin.links.edit', ['link' => $link, 'from' => 'design']) }}"
                                                    @class([
                                                        'text-zinc-600 hover:text-zinc-900 hover:underline dark:text-zinc-300 dark:hover:text-white',
                                                        'line-through opacity-60' => ! $link->is_active,
                                                    ])
                                                >
                                                    {{ $link->label }}
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-zinc-400">{{ __('design.no_links') }}</li>
                                        @endforelse
                                    </ul>

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
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card.body>
        </flux:card>
    </div>
</x-layouts.app>
