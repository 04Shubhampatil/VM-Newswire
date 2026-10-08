<x-layouts.admin title="Website Content" description="Edit the copy and images shown on the public website. Changes go live as soon as you save." :breadcrumbs="[['Website Content', route('admin.content.edit')], ['Footer & legal', null]]">
    <x-slot:actions>
        <x-button :href="route('privacy')" variant="secondary" size="sm" target="_blank" icon-left="external">Preview privacy page</x-button>
    </x-slot:actions>

    @include('admin.content.partials.tabs')

    <form method="POST" action="{{ route('admin.content.update') }}" class="flex flex-col gap-6">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="footer-legal">

        <x-admin.panel title="Footer" description="The short description under the logo at the bottom of every page.">
            <x-admin.textarea name="footer_text" label="Footer description" :value="$settings->get('footer_text')" rows="2" required maxlength="300" />
        </x-admin.panel>

        <div class="grid items-start gap-6 xl:grid-cols-2">
            <x-admin.panel title="Privacy policy" description="Markdown is supported. HTML is removed for safety.">
                <x-admin.input name="privacy_updated_at" label="Last updated" type="date" :value="$settings->all()['privacy_updated_at']" class="max-w-xs" />
                <x-admin.textarea name="privacy_content" label="Content" :value="$settings->all()['privacy_content']" rows="18" mono />
            </x-admin.panel>
            <x-admin.panel title="Terms of use" description="Markdown is supported. HTML is removed for safety.">
                <x-admin.input name="terms_updated_at" label="Last updated" type="date" :value="$settings->all()['terms_updated_at']" class="max-w-xs" />
                <x-admin.textarea name="terms_content" label="Content" :value="$settings->all()['terms_content']" rows="18" mono />
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <x-slot:note>Saving publishes the changes immediately.</x-slot:note>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />Save footer &amp; legal</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
