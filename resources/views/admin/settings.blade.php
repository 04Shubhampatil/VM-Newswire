<x-layouts.admin title="Settings" description="Company details, notification email and the links used across the website." :breadcrumbs="[['Settings', null]]">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="flex flex-col gap-6">
        @csrf @method('PUT')
        <div class="grid max-w-5xl items-start gap-6 lg:grid-cols-2">
            <x-admin.panel title="Company details" description="Shown in the header, footer and contact page.">
                <x-admin.input name="company_name" label="Company name" :value="$settings->get('company_name')" required />
                <x-admin.input name="company_email" label="Public email" type="email" :value="$settings->get('company_email')" required />
                <x-admin.input name="phone" label="Phone" :value="$settings->all()['phone']" />
                <x-admin.input name="whatsapp" label="WhatsApp number" :value="$settings->all()['whatsapp']" help="International format, e.g. +1 555 000 0000" />
                <x-admin.textarea name="address" label="Address" :value="$settings->all()['address']" rows="3" help="Not shown on the website; kept for invoices and emails." />
            </x-admin.panel>
            <div class="flex flex-col gap-6">
                <x-admin.panel title="Enquiry notifications">
                    <x-admin.input name="admin_notification_email" label="Send new enquiries to" type="email" :value="$settings->all()['admin_notification_email']"
                                   :help="'Leave empty to use '.($settings->adminNotificationEmail() ?: 'the public email').'.'" />
                </x-admin.panel>
                <x-admin.panel title="Website">
                    <x-admin.input name="network_size_label" label="Network size label" :value="$settings->get('network_size_label')" required maxlength="12" help="The headline reach figure, e.g. 200+. Shown on the hero stat card and the logo strip label." />
                    <x-admin.input name="social_linkedin" label="LinkedIn URL" type="url" :value="$settings->all()['social_linkedin']" />
                    <x-admin.input name="social_x" label="X (Twitter) URL" type="url" :value="$settings->all()['social_x']" />
                    <x-admin.input name="social_facebook" label="Facebook URL" type="url" :value="$settings->all()['social_facebook']" />
                    <x-admin.input name="social_instagram" label="Instagram URL" type="url" :value="$settings->all()['social_instagram']" />
                    <x-admin.input name="social_youtube" label="YouTube URL" type="url" :value="$settings->all()['social_youtube']" help="Footer icons link to the contact page until a URL is set." />
                </x-admin.panel>
            </div>
        </div>
        <x-admin.form-actions>
            <x-slot:note>Changes apply to the website immediately.</x-slot:note>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />Save settings</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
