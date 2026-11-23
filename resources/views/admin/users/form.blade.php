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
                    :label="__('users.fields.name')"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                />

                <flux:input
                    name="email"
                    type="email"
                    :label="__('users.fields.email')"
                    :value="old('email', $user->email)"
                    required
                />

                <flux:input
                    name="password"
                    type="password"
                    :label="__('users.fields.password')"
                    :description="$user->exists ? __('users.hints.password') : null"
                    autocomplete="new-password"
                    :required="! $user->exists"
                />

                <flux:input
                    name="password_confirmation"
                    type="password"
                    :label="__('users.fields.password_confirmation')"
                    autocomplete="new-password"
                    :required="! $user->exists"
                />

                <x-listbox
                    name="role"
                    :label="__('users.fields.role')"
                    :options="$roles"
                    :value="old('role', $role)"
                />

                <div class="flex items-end pb-2">
                    <flux:checkbox
                        name="is_active"
                        value="1"
                        :label="__('users.fields.is_active')"
                        :checked="(bool) old('is_active', $user->is_active)"
                    />
                </div>
            </div>
        </flux:card.body>

        <flux:card.footer>
            <flux:card.actions>
                <flux:button variant="outline" icon="x-mark" :href="route('admin.users.index')" wire:navigate>
                    {{ __('common.actions.cancel') }}
                </flux:button>

                <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
            </flux:card.actions>
        </flux:card.footer>
    </form>
</flux:card>
