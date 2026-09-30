<x-mail::message>
# Thank you, {{ $enquiry->name }}

We've received your enquiry{{ $enquiry->package_name_snapshot ? ' about **'.$enquiry->package_name_snapshot.'**' : '' }}.

**What happens next:** our team will review your details and contact you shortly to confirm the right package, timing and next steps. No payment is required at this stage.

@if ($enquiry->package_name_snapshot)
<x-mail::panel>
**Selected package:** {{ $enquiry->package_name_snapshot }}
</x-mail::panel>
@endif

If you have questions in the meantime, just reply to this email.

Kind regards,<br>
The {{ $settings->get('company_name') }} team

{{ $settings->get('company_email') }}@if ($settings->get('phone')) · {{ $settings->get('phone') }}@endif<br>
{{ config('app.url') }}
</x-mail::message>
