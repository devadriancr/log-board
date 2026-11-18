@php
    $fromDesign = request('from') === 'design';
    $cancelUrl = $fromDesign && $card->board_id
        ? route('admin.boards.design', $card->board_id)
        : route('admin.cards.index');
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

        <flux:card.body>
            <div class="grid gap-6 md:grid-cols-2">
                <x-listbox
                    name="board_id"
                    :label="__('cards.fields.board_id')"
                    :options="$boards"
                    :value="old('board_id', $card->board_id)"
                />

                <flux:input
                    name="title"
                    type="text"
                    :label="__('cards.fields.title')"
                    :value="old('title', $card->title)"
                    required
                    autofocus
                />

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

                <flux:input
                    name="link_columns"
                    type="number"
                    min="1"
                    max="4"
                    :label="__('cards.fields.link_columns')"
                    :value="old('link_columns', $card->link_columns)"
                    required
                />

                <div class="md:self-end md:pb-2.5">
                    <flux:checkbox
                        name="is_active"
                        value="1"
                        :label="__('cards.fields.is_active')"
                        :checked="(bool) old('is_active', $card->is_active)"
                    />
                </div>
            </div>
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
