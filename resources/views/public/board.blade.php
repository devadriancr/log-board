<x-layouts.public :boards="$boards" :board="$board" :title="$board?->name ?? config('app.name')">
    @if ($cards->isEmpty())
        <div class="flex flex-col items-center gap-4 py-24 text-center">
            <flux:icon name="squares-2x2" class="size-8 text-zinc-400" />
            <flux:text>{{ __('public.empty') }}</flux:text>
        </div>
    @else
        <x-board-grid :board="$board" :cards="$cards" />
    @endif
</x-layouts.public>
