<x-layouts.admin title="Change password" breadcrumb="Admin / Account">
    @if ($user->must_change_password)
        <p role="alert" class="max-w-xl rounded-[8px] border border-[#e8d9a8] bg-[#fff8e1] px-5 py-4 text-sm text-[#6b5200]">You signed in with a one-time password. Set your own password to continue.</p>
    @endif
    <form method="POST" action="{{ route('admin.account.password.update') }}" class="admin-panel flex max-w-xl flex-col gap-5">
        @csrf @method('PUT')
        <x-admin.input name="current_password" label="Current password" type="password" autocomplete="current-password" required />
        <x-admin.input name="password" label="New password" type="password" autocomplete="new-password" required help="At least 8 characters, with letters and numbers." />
        <x-admin.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
        <button type="submit" class="btn btn-primary btn-sm self-start">Update password</button>
    </form>
</x-layouts.admin>
