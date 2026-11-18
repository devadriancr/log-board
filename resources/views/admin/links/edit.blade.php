<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('links.edit.title') }}</flux:heading>

        @include('admin.links.form', ['action' => route('admin.links.update', $link), 'method' => 'PUT'])
    </div>
</x-layouts.app>
