<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('appearance.title')">
        <x-appearance-switcher :labels="true" :size="null" />
    </x-settings.layout>
</div>
