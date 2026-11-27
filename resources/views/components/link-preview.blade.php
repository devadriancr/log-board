{{-- Thumbnail of the linked page (see resources/js/link-preview.js). It is only to look at: the click opens the link. --}}
@props(['link', 'url' => null])

<div
    {{ $attributes->class('link-preview-box') }}
    data-size="{{ $link->preview_size }}"
    data-url="{{ $url ?? $link->urlTemplate() }}"
    data-title="{{ __('public.preview_title', ['name' => $link->tr('label')]) }}"
    x-data="linkPreview"
>
    <div data-frame inert></div>

    {{-- Skeleton shaped like a chart while the page loads --}}
    <div class="absolute inset-0 flex flex-col gap-2 bg-(--preview-surface) p-3" x-show="state === 'loading'">
        <div class="flex min-h-0 flex-1 items-end gap-[4%] motion-safe:animate-pulse" aria-hidden="true">
            @foreach ([45, 70, 55, 90, 65, 80] as $height)
                <span class="flex-1 rounded bg-(--preview-bone)" style="height: {{ $height }}%"></span>
            @endforeach
        </div>
        <p role="status" class="text-center text-xs text-(--preview-text)">{{ __('public.preview_loading') }}</p>
    </div>

    <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-(--preview-surface) p-3 text-center" x-show="state === 'failed'" x-cloak>
        <flux:icon name="exclamation-circle" class="size-6 text-(--preview-text)" />
        <p role="status" class="text-xs text-(--preview-text)">{{ __('public.preview_failed') }}</p>
    </div>
</div>
