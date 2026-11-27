@php($slides = array_values((array) old($field, $card->{$field})) ?: [''])

{{-- Each notice (slide) has its own editor; with several the public board shows them as a carousel --}}
<div
    data-rich-editor
    data-upload="{{ route('admin.uploads.image') }}"
    data-token="{{ csrf_token() }}"
    data-labels="{{ json_encode(__('cards.editor')) }}"
    data-colors="{{ json_encode(\App\Models\Card::colors()) }}"
>
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <flux:label>{{ __('cards.fields.slides') }}</flux:label>
            <flux:text class="text-xs">{{ __('cards.slides_hint') }}</flux:text>
        </div>

        @if ($main)
            <div class="w-44">
                <flux:input
                    name="slide_seconds"
                    type="number"
                    min="3"
                    max="60"
                    size="sm"
                    :label="__('cards.fields.slide_seconds')"
                    :value="old('slide_seconds', $card->slide_seconds)"
                    required
                />
            </div>
        @endif
    </div>

    <div data-slides>
        @foreach ($slides as $html)
            @include('admin.cards.slide', ['html' => $html, 'number' => $loop->iteration, 'field' => $field])
        @endforeach
    </div>

    <template data-slide-template>
        @include('admin.cards.slide', ['html' => '', 'number' => 0, 'field' => $field])
    </template>

    <flux:button type="button" variant="outline" icon="plus" data-add-slide>{{ __('cards.add_slide') }}</flux:button>

    <flux:error name="{{ $field }}" />
    @if ($main)
        <flux:error name="slide_seconds" />
    @endif
</div>
