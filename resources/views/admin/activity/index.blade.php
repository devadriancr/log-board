@use('App\Enums\LinkStyle')

@php
    $eventColors = ['created' => 'green', 'updated' => 'zinc', 'deleted' => 'red', 'reordered' => 'zinc'];

    $label = fn (string $section, string $key) => Lang::has("$section.fields.$key") ? __("$section.fields.$key") : $key;

    // One value of the log written for people: Sí/No, a text cut short, the name of an enum...
    $show = function (string $key, mixed $value): string {
        return match (true) {
            str_starts_with($key, 'content') => __('activity.content_changed'),
            $key === 'link_style' => LinkStyle::tryFrom((string) $value)?->label() ?? (string) $value,
            is_bool($value) => $value ? __('activity.values.yes') : __('activity.values.no'),
            $value === null || $value === '' => '—',
            is_array($value) => Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE), 60),
            default => Str::limit((string) $value, 60),
        };
    };
@endphp

<x-layouts.app>
    <div class="flex w-full flex-1 flex-col gap-6">
        <flux:card body="divided" class="w-full">
            <flux:card.header>
                <flux:card.heading size="lg" level="1">{{ __('activity.title') }}</flux:card.heading>
            </flux:card.header>

            <flux:card.body class="p-0">
                <form method="GET" action="{{ route('admin.activity.index') }}" class="flex flex-wrap items-end gap-3 px-6 py-4">
                    <div class="w-56">
                        <x-listbox
                            name="section"
                            :label="__('activity.columns.section')"
                            :options="collect($sections)->prepend(__('activity.all'), '')"
                            :value="request('section')"
                        />
                    </div>

                    <div class="w-64">
                        <x-listbox
                            name="user"
                            :label="__('activity.columns.user')"
                            :options="$users->prepend(__('activity.all'), '')"
                            :value="request('user')"
                        />
                    </div>

                    <flux:button type="submit" variant="outline" icon="funnel">{{ __('activity.filter') }}</flux:button>

                    @if (request()->filled('section') || request()->filled('user'))
                        <flux:button variant="outline" icon="x-mark" :href="route('admin.activity.index')">{{ __('activity.clear') }}</flux:button>
                    @endif
                </form>

                @if ($activities->isEmpty())
                    <div class="flex flex-col items-center gap-4 border-t border-zinc-200 py-12 text-center dark:border-zinc-700">
                        <flux:icon name="clipboard-document-list" class="size-8 text-zinc-400" />
                        <flux:text>{{ __('activity.empty') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto px-6 pb-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('activity.columns.date') }}</flux:table.column>
                                <flux:table.column>{{ __('activity.columns.user') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('activity.columns.event') }}</flux:table.column>
                                <flux:table.column>{{ __('activity.columns.section') }}</flux:table.column>
                                <flux:table.column>{{ __('activity.columns.item') }}</flux:table.column>
                                <flux:table.column>{{ __('activity.columns.changes') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($activities as $activity)
                                    @php
                                        $section = $activity->log_name;
                                        $new = (array) $activity->properties->get('attributes', []);
                                        $old = (array) $activity->properties->get('old', []);
                                    @endphp

                                    <flux:table.row :key="$activity->id">
                                        <flux:table.cell class="whitespace-nowrap py-4">{{ $activity->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                                        <flux:table.cell class="py-4">{{ $activity->causer?->name ?? __('activity.system') }}</flux:table.cell>
                                        <flux:table.cell align="center" class="py-0">
                                            <flux:badge :color="$eventColors[$activity->event] ?? 'zinc'" size="sm">
                                                {{ Lang::has('activity.events.'.$activity->event) ? __('activity.events.'.$activity->event) : $activity->event }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="py-4">{{ __('activity.sections.'.$section) }}</flux:table.cell>
                                        <flux:table.cell variant="strong" class="py-4">{{ $activity->description }}</flux:table.cell>
                                        <flux:table.cell class="py-3">
                                            @if ($activity->event !== 'deleted')
                                                <ul class="space-y-0.5 text-xs text-zinc-600 dark:text-zinc-300">
                                                    @foreach ($new as $key => $value)
                                                        <li>
                                                            <span class="font-medium">{{ $label($section, $key) }}:</span>
                                                            @if ($activity->event === 'updated' && array_key_exists($key, $old))
                                                                {{ $show($key, $old[$key]) }} → {{ $show($key, $value) }}
                                                            @else
                                                                {{ $show($key, $value) }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @endif
            </flux:card.body>

            @if ($activities->hasPages())
                <flux:card.footer>
                    <div class="w-full">
                        <flux:pagination :paginator="$activities" />
                    </div>
                </flux:card.footer>
            @endif
        </flux:card>
    </div>
</x-layouts.app>
