@php
    $roleColors = ['super-admin' => 'amber', 'admin' => 'green', 'user' => 'zinc'];
@endphp

<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('users.title') }}</flux:card.heading>

                <flux:card.actions>
                    @can('users.create')
                        <flux:button variant="outline" icon="plus" :href="route('admin.users.create')" wire:navigate>
                            {{ __('users.create.title') }}
                        </flux:button>
                    @endcan
                </flux:card.actions>
            </flux:card.header>

            <flux:card.body class="p-0">
                @if ($users->isEmpty())
                    <div class="flex flex-col items-center gap-4 py-12 text-center">
                        <flux:icon name="users" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('users.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('users.fields.name') }}</flux:table.column>
                                <flux:table.column>{{ __('users.fields.email') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('users.columns.role') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('users.columns.status') }}</flux:table.column>
                                <flux:table.column align="end"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($users as $user)
                                    @php $roleName = $user->roles->first()?->name; @endphp

                                    <flux:table.row :key="$user->id">
                                        <flux:table.cell variant="strong" class="py-4">{{ $user->name }}</flux:table.cell>
                                        <flux:table.cell class="py-4">{{ $user->email }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            @if ($roleName)
                                                <flux:badge :color="$roleColors[$roleName] ?? 'zinc'" size="sm">{{ __('roles.names.'.$roleName) }}</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">{{ __('roles.names.user') }}</flux:badge>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            @if ($user->is_active)
                                                <flux:badge color="green" size="sm">{{ __('common.status.active') }}</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">{{ __('common.status.inactive') }}</flux:badge>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell align="end" class="py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                {{-- Only a super admin can touch another super admin --}}
                                                @if (! $user->isSuperAdmin() || auth()->user()->isSuperAdmin())
                                                    @can('users.update')
                                                        <flux:button
                                                            size="sm"
                                                            variant="outline"
                                                            icon="pencil-square"
                                                            class="min-w-26 justify-center"
                                                            :href="route('admin.users.edit', $user)"
                                                            wire:navigate
                                                        >
                                                            {{ __('common.actions.edit') }}
                                                        </flux:button>
                                                    @endcan

                                                    @if (! $user->is(auth()->user()))
                                                        @can('users.delete')
                                                            <flux:modal.trigger :name="'delete-user-'.$user->id">
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
                                                    @endif
                                                @endif
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

        @can('users.delete')
            @foreach ($users as $user)
                <flux:modal :name="'delete-user-'.$user->id" class="max-w-md">
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="space-y-6">
                        @csrf
                        @method('DELETE')

                        <flux:heading size="lg">{{ __('users.delete.title', ['name' => $user->name]) }}</flux:heading>

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
