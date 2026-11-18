<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('cards.create.title') }}</flux:heading>

        @include('admin.cards.form', ['action' => route('admin.cards.store'), 'boards' => $boards])
    </div>
</x-layouts.app>
