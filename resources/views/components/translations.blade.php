{{-- The English and Japanese versions of a text field (the field itself is the Spanish one). Empty = the Spanish text is shown. --}}
@props(['name', 'label', 'model', 'textarea' => false])

@foreach (['en', 'ja'] as $lang)
    @if ($textarea)
        <flux:textarea
            name="{{ $name }}_{{ $lang }}"
            lang="{{ $lang }}"
            rows="2"
            :label="$label.' ('.__('common.languages.'.$lang).')'"
        >{{ old($name.'_'.$lang, $model->{$name.'_'.$lang}) }}</flux:textarea>
    @else
        <flux:input
            name="{{ $name }}_{{ $lang }}"
            type="text"
            lang="{{ $lang }}"
            :label="$label.' ('.__('common.languages.'.$lang).')'"
            :value="old($name.'_'.$lang, $model->{$name.'_'.$lang})"
        />
    @endif
@endforeach
