@use('App\Enums\LinkStyle')

<flux:card body="divided" class="w-full">
    <form method="POST" action="{{ $action }}">
        @csrf
        @isset($method)
            @method($method)
        @endisset

        <flux:card.body>
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input
                    name="name"
                    type="text"
                    :label="__('boards.fields.name')"
                    :value="old('name', $board->name)"
                    required
                    autofocus
                />

                <flux:input
                    name="slug"
                    type="text"
                    :label="__('boards.fields.slug')"
                    :placeholder="__('boards.placeholders.slug')"
                    :value="old('slug', $board->slug)"
                    required
                />

                <x-listbox
                    name="link_style"
                    :label="__('boards.fields.link_style')"
                    :options="collect(LinkStyle::cases())->mapWithKeys(fn ($style) => [$style->value => $style->label()])"
                    :value="old('link_style', $board->link_style?->value)"
                />

                <flux:input
                    name="sort_order"
                    type="number"
                    min="0"
                    max="32767"
                    :label="__('boards.fields.sort_order')"
                    :value="old('sort_order', $board->sort_order)"
                    :readonly="! $board->exists"
                    required
                />

                <flux:checkbox
                    name="is_active"
                    value="1"
                    :label="__('boards.fields.is_active')"
                    :checked="(bool) old('is_active', $board->is_active)"
                />

                <flux:checkbox
                    name="is_home"
                    value="1"
                    :label="__('boards.fields.is_home')"
                    :checked="(bool) old('is_home', $board->is_home)"
                />
            </div>
        </flux:card.body>

        <flux:card.footer>
            <flux:card.actions>
                <flux:button variant="outline" icon="x-mark" :href="(auth()->user()->can('boards.view') ? route('admin.boards.index') : route('dashboard'))" wire:navigate>
                    {{ __('common.actions.cancel') }}
                </flux:button>

                <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
            </flux:card.actions>
        </flux:card.footer>
    </form>
</flux:card>
