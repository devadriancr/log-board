<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('applications.title') }}</flux:card.heading>

                <flux:card.actions>
                    @can('applications.create')
                    <flux:button variant="outline" icon="plus" :href="route('admin.applications.create')" wire:navigate>
                        {{ __('applications.create.title') }}
                    </flux:button>
                    @endcan
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body class="p-0">
                @if ($applications->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="computer-desktop" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('applications.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('applications.fields.name') }}</flux:table.column>
                                <flux:table.column>{{ __('applications.fields.base_url') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('applications.columns.links') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('applications.columns.status') }}</flux:table.column>
                                <flux:table.column align="end"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($applications as $application)
                                    <flux:table.row :key="$application->id">
                                        <flux:table.cell variant="strong" class="py-4">{{ $application->name }}</flux:table.cell>
                                        <flux:table.cell class="py-4 font-mono">{{ $application->base_url }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-4">{{ $application->links_count }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            <div class="flex flex-wrap justify-center gap-1.5">
                                                @if ($application->is_active)
                                                    <flux:badge color="green" size="sm">{{ __('common.status.active') }}</flux:badge>
                                                @else
                                                    <flux:badge color="zinc" size="sm">{{ __('common.status.inactive') }}</flux:badge>
                                                @endif

                                                @if ($application->requires_auth)
                                                    <flux:badge color="amber" size="sm" icon="lock-closed">{{ __('applications.badges.auth') }}</flux:badge>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell align="end" class="py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                @can('applications.update')
                                                <flux:button
                                                    size="sm"
                                                    variant="outline"
                                                    icon="pencil-square"
                                                    class="min-w-26 justify-center"
                                                    :href="route('admin.applications.edit', $application)"
                                                    wire:navigate
                                                >
                                                    {{ __('common.actions.edit') }}
                                                </flux:button>
                                                @endcan

                                                @can('applications.delete')
                                                <flux:modal.trigger :name="'delete-application-'.$application->id">
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
                                                @endcan
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

        @can('applications.delete')
        @foreach ($applications as $application)
            <flux:modal :name="'delete-application-'.$application->id" class="max-w-md">
                <form method="POST" action="{{ route('admin.applications.destroy', $application) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <flux:heading size="lg">{{ __('applications.delete.title', ['name' => $application->name]) }}</flux:heading>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="outline" icon="x-mark">{{ __('common.actions.cancel') }}</flux:button>
                        </flux:modal.close>

                        <flux:button variant="outline" color="red" icon="trash" type="submit">{{ __('common.actions.delete') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endforeach
        @endcan
    </div>
</x-layouts.app>
