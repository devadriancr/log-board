<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('cards.title') }}</flux:card.heading>

                <flux:card.actions>
                    <flux:button variant="outline" icon="plus" :href="route('admin.cards.create')" wire:navigate>
                        {{ __('cards.create.title') }}
                    </flux:button>

                    <flux:button variant="outline" icon="document-text" :href="route('admin.cards.create', ['type' => 'text'])" wire:navigate>
                        {{ __('cards.new_text') }}
                    </flux:button>
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body class="p-0">
                @if ($cards->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="rectangle-group" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('cards.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('cards.fields.board_id') }}</flux:table.column>
                                <flux:table.column>{{ __('cards.fields.title') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('cards.columns.size') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('cards.columns.links') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('cards.columns.status') }}</flux:table.column>
                                <flux:table.column align="end"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($cards as $card)
                                    <flux:table.row :key="$card->id">
                                        <flux:table.cell class="py-4">{{ $card->board->name }}</flux:table.cell>
                                        <flux:table.cell variant="strong" class="py-4">
                                            <span class="inline-flex items-center gap-2">
                                                @if ($card->colorName())
                                                    <span class="card-swatch size-3 shrink-0 rounded-full" data-card-color="{{ $card->colorName() }}" title="{{ \App\Models\Card::COLORS[$card->color] }}"></span>
                                                @endif
                                                {{ $card->title }}
                                            </span>
                                        </flux:table.cell>
                                        <flux:table.cell align="center" class="py-4 font-mono">{{ $card->grid_w }} × {{ $card->grid_h }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-4">{{ $card->isText() ? __('cards.types.text') : $card->links_count }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            @if ($card->is_active)
                                                <flux:badge color="green" size="sm">{{ __('common.status.active') }}</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">{{ __('common.status.inactive') }}</flux:badge>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell align="end" class="py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button
                                                    size="sm"
                                                    variant="outline"
                                                    icon="pencil-square"
                                                    class="min-w-26 justify-center"
                                                    :href="route('admin.cards.edit', $card)"
                                                    wire:navigate
                                                >
                                                    {{ __('common.actions.edit') }}
                                                </flux:button>

                                                <flux:modal.trigger :name="'delete-card-'.$card->id">
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

        @foreach ($cards as $card)
            <flux:modal :name="'delete-card-'.$card->id" class="max-w-md">
                <form method="POST" action="{{ route('admin.cards.destroy', $card) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <flux:heading size="lg">{{ __('cards.delete.title', ['name' => $card->title]) }}</flux:heading>

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
