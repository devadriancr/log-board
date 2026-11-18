<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('applications.edit.title') }}</flux:heading>

        @include('admin.applications.form', ['action' => route('admin.applications.update', $application), 'method' => 'PUT'])
    </div>
</x-layouts.app>
