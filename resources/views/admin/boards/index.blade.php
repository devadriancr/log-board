<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('boards.title') }}</flux:card.heading>

                <flux:card.actions>
                    <flux:button variant="outline" icon="plus" :href="route('admin.boards.create')" wire:navigate>
                        {{ __('boards.create.title') }}
                    </flux:button>
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body class="p-0">
                @if ($boards->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="squares-2x2" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('boards.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column align="center">{{ __('boards.fields.sort_order') }}</flux:table.column>
                                <flux:table.column>{{ __('boards.fields.name') }}</flux:table.column>
                                <flux:table.column>{{ __('boards.fields.slug') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('boards.fields.link_style') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('boards.columns.cards') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('boards.columns.status') }}</flux:table.column>
                                <flux:table.column align="end"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($boards as $board)
                                    <flux:table.row :key="$board->id">
                                        <flux:table.cell align="center" class="py-4">{{ $board->sort_order }}</flux:table.cell>
                                        <flux:table.cell variant="strong" class="py-4">{{ $board->name }}</flux:table.cell>
                                        <flux:table.cell class="py-4 font-mono">{{ $board->slug }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-4">{{ $board->link_style->label() }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-4">{{ $board->cards_count }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            <div class="flex flex-wrap justify-center gap-1.5">
                                                @if ($board->is_active)
                                                    <flux:badge color="green" size="sm">{{ __('common.status.active') }}</flux:badge>
                                                @else
                                                    <flux:badge color="zinc" size="sm">{{ __('common.status.inactive') }}</flux:badge>
                                                @endif

                                                @if ($board->is_home)
                                                    <flux:badge color="amber" size="sm">{{ __('boards.badges.home') }}</flux:badge>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell align="end" class="py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button
                                                    size="sm"
                                                    variant="outline"
                                                    icon="squares-plus"
                                                    class="min-w-26 justify-center"
                                                    :href="route('admin.boards.design', $board)"
                                                >
                                                    {{ __('boards.actions.design') }}
                                                </flux:button>

                                                <flux:button
                                                    size="sm"
                                                    variant="outline"
                                                    icon="pencil-square"
                                                    class="min-w-26 justify-center"
                                                    :href="route('admin.boards.edit', $board)"
                                                    wire:navigate
                                                >
                                                    {{ __('common.actions.edit') }}
                                                </flux:button>

                                                <flux:modal.trigger :name="'delete-board-'.$board->id">
                                                    <flux:button
                                                        size="sm"
                                                        variant="outline"
                                                        color="red"
                                                        icon="trash"
                                                        class="min-w-26 justify-center"
                                                    >
                                                        {{ __('common.actions.delete') }}
                                                    </flux:button>
                                                </flux:modal.trigger>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @endif
            </flux:card.body>
        </flux:card>

        @foreach ($boards as $board)
            <flux:modal :name="'delete-board-'.$board->id" class="max-w-md">
                <form method="POST" action="{{ route('admin.boards.destroy', $board) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <div>
                        <flux:heading size="lg">{{ __('boards.delete.title', ['name' => $board->name]) }}</flux:heading>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="outline" icon="x-mark">{{ __('common.actions.cancel') }}</flux:button>
                        </flux:modal.close>

                        <flux:button variant="outline" color="red" icon="trash" type="submit">{{ __('common.actions.delete') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endforeach
    </div>
</x-layouts.app>
