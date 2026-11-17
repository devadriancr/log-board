@props(['labels' => false, 'size' => 'sm'])

<flux:radio.group x-data variant="segmented" :size="$size" x-model="$flux.appearance" {{ $attributes }}>
    <flux:radio value="light" icon="sun" :aria-label="__('appearance.light')" :title="__('appearance.light')">
        {{ $labels ? __('appearance.light') : '' }}
    </flux:radio>
    <flux:radio value="dark" icon="moon" :aria-label="__('appearance.dark')" :title="__('appearance.dark')">
        {{ $labels ? __('appearance.dark') : '' }}
    </flux:radio>
    <flux:radio value="system" icon="computer-desktop" :aria-label="__('appearance.system')" :title="__('appearance.system')">
        {{ $labels ? __('appearance.system') : '' }}
    </flux:radio>
</flux:radio.group>
