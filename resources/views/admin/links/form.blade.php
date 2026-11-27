@php
    $fromDesign = request('from') === 'design';
    $cancelUrl = $fromDesign && $link->card_id
        ? route('admin.boards.design', $link->card?->board_id)
        : (auth()->user()->can('links.view') ? route('admin.links.index') : route('dashboard'));
@endphp

<flux:card body="divided" class="w-full">
    <form
        method="POST"
        action="{{ $action }}"
        x-data="{ path: @js(old('path', $link->path)), options: @js(old('options', $options)) }"
    >
        @csrf
        @isset($method)
            @method($method)
        @endisset

        @if ($fromDesign)
            <input type="hidden" name="from" value="design">
        @endif

        <flux:card.body>
            <div class="grid gap-6 md:grid-cols-2">
                <x-listbox
                    name="card_id"
                    :label="__('links.fields.card_id')"
                    :options="$cards"
                    :value="old('card_id', $link->card_id)"
                />

                <x-listbox
                    name="application_id"
                    :label="__('links.fields.application_id')"
                    :options="$applications"
                    :value="old('application_id', $link->application_id)"
                />

                <flux:input
                    name="label"
                    type="text"
                    :label="__('links.fields.label')"
                    :value="old('label', $link->label)"
                    required
                    autofocus
                />

                <x-translations name="label" :label="__('links.fields.label')" :model="$link" />

                <flux:input
                    name="path"
                    type="text"
                    class:input="font-mono"
                    x-model="path"
                    :label="__('links.fields.path')"
                    :placeholder="__('links.placeholders.path')"
                    :value="old('path', $link->path)"
                    required
                />

                <div class="md:self-end md:pb-2.5">
                    <flux:checkbox
                        name="is_active"
                        value="1"
                        :label="__('links.fields.is_active')"
                        :checked="(bool) old('is_active', $link->is_active)"
                    />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea
                        name="description"
                        rows="2"
                        :label="__('links.fields.description')"
                    >{{ old('description', $link->description) }}</flux:textarea>
                </div>

                <x-translations name="description" :label="__('links.fields.description')" :model="$link" :textarea="true" />

                <flux:checkbox
                    name="show_description"
                    value="1"
                    :label="__('links.fields.show_description')"
                    :checked="(bool) old('show_description', $link->show_description)"
                />

                <flux:checkbox
                    name="open_in_new_tab"
                    value="1"
                    :label="__('links.fields.open_in_new_tab')"
                    :checked="(bool) old('open_in_new_tab', $link->open_in_new_tab)"
                />

                <div class="flex flex-col gap-4 md:col-span-2" x-show="path.includes('{param}')" x-cloak>
                    <flux:separator variant="subtle" />

                    <flux:heading>{{ __('links.options.title') }}</flux:heading>

                    <template x-for="(option, i) in options" x-bind:key="i">
                        <div class="grid grid-cols-2 items-center gap-3 md:grid-cols-[1fr_1fr_1fr_1fr_auto]">
                            <flux:input
                                type="text"
                                class:input="font-mono"
                                x-model="option.value"
                                x-bind:name="'options[' + i + '][value]'"
                                :placeholder="__('links.options.value')"
                                :aria-label="__('links.options.value')"
                            />

                            <flux:input
                                type="text"
                                x-model="option.label"
                                x-bind:name="'options[' + i + '][label]'"
                                :placeholder="__('links.options.label')"
                                :aria-label="__('links.options.label')"
                            />

                            <flux:input
                                type="text"
                                lang="en"
                                x-model="option.label_en"
                                x-bind:name="'options[' + i + '][label_en]'"
                                :placeholder="__('links.options.label').' ('.__('common.languages.en').')'"
                                :aria-label="__('links.options.label').' ('.__('common.languages.en').')'"
                            />

                            <flux:input
                                type="text"
                                lang="ja"
                                x-model="option.label_ja"
                                x-bind:name="'options[' + i + '][label_ja]'"
                                :placeholder="__('links.options.label').' ('.__('common.languages.ja').')'"
                                :aria-label="__('links.options.label').' ('.__('common.languages.ja').')'"
                            />

                            <flux:button type="button" variant="outline" color="red" icon="trash" x-on:click="options.splice(i, 1)">
                                {{ __('common.actions.remove') }}
                            </flux:button>
                        </div>
                    </template>

                    @if ($errors->has('options') || $errors->has('options.*'))
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">
                            {{ $errors->first('options') ?: collect($errors->get('options.*'))->flatten()->first() }}
                        </p>
                    @endif

                    <div>
                        <flux:button type="button" variant="outline" icon="plus" x-on:click="options.push({ value: '', label: '', label_en: '', label_ja: '' })">
                            {{ __('links.options.add') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:card.body>

        <flux:card.footer>
            <flux:card.actions>
                <flux:button variant="outline" icon="x-mark" :href="$cancelUrl">
                    {{ __('common.actions.cancel') }}
                </flux:button>

                <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
            </flux:card.actions>
        </flux:card.footer>
    </form>
</flux:card>
