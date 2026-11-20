<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('applications.create.title') }}</flux:heading>

        @include('admin.applications.form', ['action' => route('admin.applications.store'), 'designBoard' => $designBoard])
    </div>
</x-layouts.app>
