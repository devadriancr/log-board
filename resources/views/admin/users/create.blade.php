<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('users.create.title') }}</flux:heading>

        @include('admin.users.form', ['action' => route('admin.users.store')])
    </div>
</x-layouts.app>
