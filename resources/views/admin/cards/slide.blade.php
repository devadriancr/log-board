{{-- One notice of a text card: its own editor. The TipTap toolbar and editor are built by rich-editor.js. --}}
<div data-slide class="mb-4 rounded-lg border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-700">
    <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-1.5 dark:border-zinc-600">
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('cards.slide') }} <span data-slide-number>{{ $number }}</span></span>

        <flux:button
            type="button"
            size="xs"
            variant="ghost"
            icon="trash"
            data-remove-slide
            :title="__('cards.remove_slide')"
            :aria-label="__('cards.remove_slide')"
        />
    </div>

    <div data-toolbar class="flex flex-wrap items-center gap-0.5 border-b border-zinc-200 p-1.5 dark:border-zinc-600"></div>
    <div data-editor></div>

    <textarea name="{{ $field ?? 'content' }}[]" hidden>{{ $html }}</textarea>
</div>
