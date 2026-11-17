@php
    $toast = match (true) {
        session()->has('error') => ['text' => session('error'), 'variant' => 'danger'],
        session()->has('success') => ['text' => session('success'), 'variant' => 'success'],
        default => null,
    };
@endphp

<flux:toast.group position="top end">
    <flux:toast />
</flux:toast.group>

@if ($toast)
    <div x-data x-init='$flux.toast(@json($toast))' hidden></div>
@endif
