<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\SiteSetting;
use App\Services\SiteSettings;
use Illuminate\Database\Seeder;

/**
 * Seeds editable site settings, draft legal pages and general FAQs.
 * Existing values are never overwritten, so it is safe to re-run.
 */
class SiteContentSeeder extends Seeder
{
    public function run(SiteSettings $settings): void
    {
        $values = SiteSettings::defaults();
        $values['privacy_content'] = file_get_contents(__DIR__.'/content/privacy.md');
        $values['terms_content'] = file_get_contents(__DIR__.'/content/terms.md');

        foreach ($values as $key => $value) {
            SiteSetting::firstOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
        $settings->flush();

        if (Faq::whereNull('package_id')->exists()) {
            return;
        }

        $faqs = [
            ['Enquiries', 'Do I pay online?', 'No. VM Newswire is enquiry-based. Submit an enquiry and our team will contact you to confirm the package, timing and invoice. No payment is required to enquire.'],
            ['Packages', 'Which package is right for my announcement?', 'It depends on where you want to be seen. Compare the packages side by side, or choose "Not sure" on the enquiry form and we will recommend one.'],
            ['Packages', 'What does "200+ media outlets" mean?', 'Beyond the headline platforms named in each package, your release can be distributed across an extended network of digital publications. The exact network depends on the package you choose.'],
            ['Sample reports', 'What is in a sample report?', 'A sample distribution report shows the outlets where a release in that package appeared, with live links. Download one from any package page before you enquire.'],
            ['Enquiries', 'Can I distribute more than one press release?', 'Yes. Tell us how many releases you plan to distribute in the enquiry form and we will come back with options.'],
            ['Enquiries', 'How do I send my press release?', 'After you enquire, our team will contact you and explain how to share your release, images and timing.'],
            ['General', 'How quickly will my release be published?', '[Turnaround time to be confirmed by VM Newswire.]'],
            ['Sample reports', 'Will I receive a report after distribution?', 'Yes. Every package includes professional reporting, so you can see where your release was published.'],
        ];

        foreach ($faqs as $i => [$category, $question, $answer]) {
            Faq::create(['category' => $category, 'question' => $question, 'answer' => $answer, 'display_order' => $i + 1]);
        }
    }
}
