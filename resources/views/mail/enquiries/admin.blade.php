<x-mail::message>
# New enquiry: {{ $enquiry->package_label }}

A new enquiry was submitted on the website.

<x-mail::table>
| | |
|:--|:--|
| **Name** | {{ $enquiry->name }} |
| **Email** | {{ $enquiry->email }} |
| **Phone / WhatsApp** | {{ $enquiry->phone }} |
| **Company** | {{ $enquiry->company ?: '—' }} |
| **Country** | {{ $enquiry->country ?: '—' }} |
| **Package** | {{ $enquiry->package_label }} |
| **Package price** | {{ $enquiry->formatted_price_snapshot ?: '—' }} |
| **Press releases** | {{ $enquiry->release_count ?: '—' }} |
| **Source page** | {{ $enquiry->source_page ?: '—' }} |
| **Submitted** | {{ $enquiry->created_at->timezone(config('app.timezone'))->format('j M Y, H:i T') }} |
</x-mail::table>

**Message**

{{ $enquiry->message }}

<x-mail::button :url="$adminUrl">
Open in admin
</x-mail::button>

Reply to this email to respond to {{ $enquiry->name }} directly.
</x-mail::message>
