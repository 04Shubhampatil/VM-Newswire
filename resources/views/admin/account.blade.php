<x-layouts.admin title="My account" :description="'Signed in as '.$user->email" :breadcrumbs="[['My account', null]]">
    @if ($user->must_change_password)
        <x-admin.alert type="warning" class="max-w-xl" title="Set your own password.">You signed in with a one-time password.</x-admin.alert>
    @endif
    <form method="POST" action="{{ route('admin.account.password.update') }}" class="flex max-w-xl flex-col gap-6">
        @csrf @method('PUT')
        <x-admin.panel title="Change password">
            <x-admin.input name="current_password" label="Current password" type="password" autocomplete="current-password" required />
            <x-admin.input name="password" label="New password" type="password" autocomplete="new-password" required help="At least 8 characters, with letters and numbers." />
            <x-admin.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
        </x-admin.panel>
        <button type="submit" class="btn btn-primary btn-sm self-start"><x-icon name="check" :size="15" />Update password</button>
    </form>
</x-layouts.admin>
