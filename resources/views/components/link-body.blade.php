{{-- Name, description and icons of a link of the board --}}
@props(['link', 'isButton', 'labelClasses', 'arrowClasses'])

@unless ($isButton)
    <flux:icon name="link" variant="micro" class="size-4 shrink-0 text-zinc-400" />
@endunless

<span class="flex min-w-0 flex-1 flex-col gap-0.5">
    <span class="truncate {{ $labelClasses }}" title="{{ $link->tr('label') }}">{{ $link->tr('label') }}</span>

    @if ($link->show_description && $link->tr('description'))
        <span class="truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $link->tr('description') }}">{{ $link->tr('description') }}</span>
    @endif
</span>

<span class="flex shrink-0 items-center gap-1.5">
    @if ($link->application->requires_auth)
        <flux:icon name="lock-closed" variant="micro" class="size-4 text-zinc-400" :title="__('public.requires_auth')" />
    @endif

    <flux:icon name="arrow-up-right" variant="micro" class="size-4 {{ $arrowClasses }}" />
</span>
