<x-layouts.admin title="Settings" breadcrumb="Admin / Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid max-w-5xl items-start gap-6 lg:grid-cols-2">
        @csrf @method('PUT')
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">Company details</h2>
            <x-admin.input name="company_name" label="Company name" :value="$settings->get('company_name')" required />
            <x-admin.input name="company_email" label="Public email" type="email" :value="$settings->get('company_email')" required />
            <x-admin.input name="phone" label="Phone" :value="$settings->all()['phone']" />
            <x-admin.input name="whatsapp" label="WhatsApp number" :value="$settings->all()['whatsapp']" help="International format, e.g. +1 555 000 0000" />
            <x-admin.textarea name="address" label="Address" :value="$settings->all()['address']" rows="3" />
        </section>
        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Enquiry notifications</h2>
                <x-admin.input name="admin_notification_email" label="Send new enquiries to" type="email" :value="$settings->all()['admin_notification_email']"
                               :help="'Leave empty to use '.($settings->adminNotificationEmail() ?: 'the public email').'.'" />
            </section>
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Website</h2>
                <x-admin.input name="network_size_label" label="Network size label" :value="$settings->get('network_size_label')" required maxlength="12" help="Shown as the headline reach figure, e.g. 200+" />
                <x-admin.input name="social_linkedin" label="LinkedIn URL" type="url" :value="$settings->all()['social_linkedin']" />
                <x-admin.input name="social_x" label="X (Twitter) URL" type="url" :value="$settings->all()['social_x']" />
                <x-admin.input name="social_facebook" label="Facebook URL" type="url" :value="$settings->all()['social_facebook']" />
            </section>
            <button type="submit" class="btn btn-primary btn-sm self-start">Save settings</button>
        </div>
    </form>
</x-layouts.admin>
