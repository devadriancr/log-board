@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
])

@php
    $placeholder ??= __('common.select');
    $options = collect($options)->mapWithKeys(fn ($text, $key) => [(string) $key => $text])->all();
@endphp

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        x-data="{
            value: @js((string) ($value ?? '')),
            options: @js($options),
            get text() { return this.options[this.value] ?? @js($placeholder) },
        }"
        x-modelable="value"
        {{ $attributes->whereStartsWith('x-model') }}
        class="w-full [&_ui-dropdown]:block [&_ui-dropdown]:w-full"
    >
        @if ($name)
            <input type="hidden" name="{{ $name }}" x-bind:value="value">
        @endif

        <flux:dropdown>
            <flux:button variant="outline" icon:trailing="chevron-down" class="w-full justify-between font-normal">
                <span class="truncate" x-text="text" x-bind:class="value === '' ? 'text-zinc-400' : ''"></span>
            </flux:button>

            <flux:menu class="max-h-72 min-w-56 overflow-y-auto" x-on:click="value = $event.target.closest('[data-value]')?.dataset.value ?? value">
                @foreach ($options as $key => $text)
                    <flux:menu.item as="button" type="button" data-value="{{ $key }}">
                        <span class="flex w-full items-center justify-between gap-6">
                            <span>{{ $text }}</span>
                            <flux:icon name="check" variant="micro" class="size-4" x-show="value === $el.closest('[data-value]').dataset.value" />
                        </span>
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </div>

    @if ($name)
        <flux:error :name="$name" />
    @endif
</flux:field>
