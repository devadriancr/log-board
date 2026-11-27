<flux:card body="divided" class="w-full">
    <form method="POST" action="{{ $action }}">
        @csrf

        @if ($designBoard ?? null)
            <input type="hidden" name="from" value="design">
            <input type="hidden" name="board" value="{{ $designBoard->id }}">
        @endif
        @isset($method)
            @method($method)
        @endisset

        <flux:card.body>
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input
                    name="name"
                    type="text"
                    :label="__('applications.fields.name')"
                    :value="old('name', $application->name)"
                    required
                    autofocus
                />

                <flux:input
                    name="base_url"
                    type="text"
                    class:input="font-mono"
                    :label="__('applications.fields.base_url')"
                    :placeholder="__('applications.placeholders.base_url')"
                    :value="old('base_url', $application->base_url)"
                    required
                />

                <div class="md:col-span-2">
                    <flux:input
                        name="preview_url"
                        type="text"
                        class:input="font-mono"
                        :label="__('applications.fields.preview_url')"
                        :description="__('applications.preview_url_help')"
                        :value="old('preview_url', $application->preview_url)"
                    />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea
                        name="notes"
                        rows="3"
                        :label="__('applications.fields.notes')"
                    >{{ old('notes', $application->notes) }}</flux:textarea>
                </div>

                <flux:checkbox
                    name="requires_auth"
                    value="1"
                    :label="__('applications.fields.requires_auth')"
                    :checked="(bool) old('requires_auth', $application->requires_auth)"
                />

                <flux:checkbox
                    name="is_active"
                    value="1"
                    :label="__('applications.fields.is_active')"
                    :checked="(bool) old('is_active', $application->is_active)"
                />
            </div>
        </flux:card.body>

        <flux:card.footer>
            <flux:card.actions>
                <flux:button variant="outline" icon="x-mark" :href="($designBoard ?? null) ? route('admin.boards.design', $designBoard) : (auth()->user()->can('applications.view') ? route('admin.applications.index') : route('dashboard'))" wire:navigate>
                    {{ __('common.actions.cancel') }}
                </flux:button>

                <flux:button variant="outline" icon="check" type="submit">{{ __('common.actions.save') }}</flux:button>
            </flux:card.actions>
        </flux:card.footer>
    </form>
</flux:card>
