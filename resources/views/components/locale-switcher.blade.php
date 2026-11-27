{{-- Language of the site: Spanish (main), English or Japanese. The choice is kept in the session. --}}
@props(['inline' => false])

@if ($inline)
    {{-- Always visible (admin sidebar): one small link per language --}}
    <div class="flex justify-center gap-1" role="group" aria-label="{{ __('common.language') }}">
        @foreach (['es' => 'ES', 'en' => 'EN', 'ja' => '日本語'] as $code => $short)
            <a
                href="{{ route('locale', $code) }}"
                lang="{{ $code }}"
                title="{{ config('app.locales')[$code] }}"
                @if (app()->getLocale() === $code) aria-current="true" @endif
                @class([
                    'rounded-md px-2 py-1 text-xs font-medium',
                    'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-white' => app()->getLocale() === $code,
                    'text-zinc-500 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' => app()->getLocale() !== $code,
                ])
            >{{ $short }}</a>
        @endforeach
    </div>
@else
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="subtle" size="sm" icon="language" :aria-label="__('common.language')" :title="__('common.language')" {{ $attributes }}>
            {{ strtoupper(app()->getLocale()) }}
        </flux:button>

        <flux:menu>
            @foreach (config('app.locales') as $code => $name)
                <flux:menu.item :href="route('locale', $code)" :icon="app()->getLocale() === $code ? 'check' : null" lang="{{ $code }}">{{ $name }}</flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>
@endif
