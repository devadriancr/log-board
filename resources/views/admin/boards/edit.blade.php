<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('boards.edit.title') }}</flux:heading>

        @include('admin.boards.form', ['action' => route('admin.boards.update', $board), 'method' => 'PUT'])
    </div>
</x-layouts.app>
