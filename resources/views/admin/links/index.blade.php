<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('links.title') }}</flux:card.heading>

                <flux:card.actions>
                    <flux:button variant="outline" icon="plus" :href="route('admin.links.create')" wire:navigate>
                        {{ __('links.create.title') }}
                    </flux:button>
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body class="p-0">
                @if ($links->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="link" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('links.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('links.columns.card') }}</flux:table.column>
                                <flux:table.column>{{ __('links.fields.label') }}</flux:table.column>
                                <flux:table.column>{{ __('links.columns.application') }}</flux:table.column>
                                <flux:table.column>{{ __('links.fields.path') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('links.columns.options') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('links.columns.status') }}</flux:table.column>
                                <flux:table.column align="end"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($links as $link)
                                    <flux:table.row :key="$link->id">
                                        <flux:table.cell class="py-4">{{ $link->card->board->name }} › {{ $link->card->title }}</flux:table.cell>
                                        <flux:table.cell variant="strong" class="py-4">{{ $link->label }}</flux:table.cell>
                                        <flux:table.cell class="py-4">{{ $link->application->name }}</flux:table.cell>
                                        <flux:table.cell class="py-4 font-mono">{{ $link->path }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-4">{{ $link->options_count ?: '—' }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            @if ($link->is_active)
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
                                                    :href="route('admin.links.edit', $link)"
                                                    wire:navigate
                                                >
                                                    {{ __('common.actions.edit') }}
                                                </flux:button>

                                                <flux:modal.trigger :name="'delete-link-'.$link->id">
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

        @foreach ($links as $link)
            <flux:modal :name="'delete-link-'.$link->id" class="max-w-md">
                <form method="POST" action="{{ route('admin.links.destroy', $link) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <flux:heading size="lg">{{ __('links.delete.title', ['name' => $link->label]) }}</flux:heading>

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
