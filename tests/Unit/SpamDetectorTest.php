<?php

namespace Tests\Unit;

use App\Services\SpamDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Calibrated on the real enquiries received by vmnewswire.com (spam samples are copied from them,
 * with personal details changed) and on realistic genuine enquiries that must never be flagged.
 */
class SpamDetectorTest extends TestCase
{
    use RefreshDatabase;

    private function assess(array $data, array $context = [])
    {
        return app(SpamDetector::class)->assess($data + ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'company' => null, 'message' => ''], $context);
    }

    /** @return array<string, array{array}> */
    public static function spamSamples(): array
    {
        return [
            'russian transfer bot' => [[
                'name' => 'Вам перевод 187721 руб. забрать тут https://tinyurl.com/55zcxy7r#tdI7MR',
                'email' => 'victim@icloud.com', 'company' => 'google', 'message' => 'IywOV7f 2KzA TxH0LC4 XdGE6CG PtwM nQFSViY',
            ]],
            'russian transfer bot, other wording' => [[
                'name' => 'Пени 498 руб за просрочку, зачислено', 'email' => 'someone@gmail.com', 'company' => 'google',
                'message' => 'P1a5Zy1 L9ue pZKSPOU Sg70hdr PHTp L92oCL1',
            ]],
            'russian prize bait' => [[
                'name' => 'suespisiow', 'email' => 'endibyf4817@geiiml.my.id', 'company' => 'google',
                'message' => 'Отличные новости! Ваш ценный приз в ожидании! Перейдите к подробности по ссылке https://mhcxi8.vt9vx0.buzz/WNy2YaJb THRT158413JUYGTD',
            ]],
            'gibberish bot' => [[
                'name' => 'NARTYTRYUT733223NEWETREWT', 'email' => 'whitaker75645@notlettersmail.com', 'company' => 'google',
                'message' => 'METYUTYJ733223MAYTRYR',
            ]],
            'guest post seller (Christy)' => [[
                'name' => 'Christy Mark', 'email' => 'christy@zoomwings.co.uk', 'company' => 'Zoom Wings',
                'message' => "Hi\n\nI'm Christy from Zoom Wings. I found this https://usawire.com/vmnewswire-brings-package-comparison/ post, which is a backlink to your website https://vmnewswire.com/ and I think your team is doing the link building for the website.\n\nWe have been in guest posting for a long time. We have strong DA, DR, and quality websites for guest posting with 100% relevancy with your niche. The posts will be 100% SEO optimized with dofollow backlinks.\n\n200+ Sites & Pricing:\nhttps://docs.google.com/spreadsheets/d/1ZhzNDaJFB5aF5t4/\n\nExtended List of 1000+ Sites:\nhttps://docs.google.com/spreadsheets/d/1RloY4MhNVROU/\n\nP.S. We value your privacy and comply with anti-spam laws. To unsubscribe, Please reply with a \"No\" on the email subject-line.",
            ]],
            'guest post seller (Emmara)' => [[
                'name' => 'Emmara Molli', 'email' => 'emmaramolli@gmail.com', 'company' => 'digisyntex',
                'message' => "Hi, Do you want to improve your website's SEO, build quality backlinks, and boost your online visibility? I can assist you by providing high-quality tech and AI-related guest posting services on relevant websites at reasonable rates. You can view my website on Google Sheets. https://docs.google.com/spreadsheets/d/1jcUe-LJkfrVtrp2/edit",
            ]],
            'seo marketer (Azel)' => [[
                'name' => 'Azel', 'email' => 'azel.seomarketing@gmail.com', 'company' => 'Guest posting',
                'message' => 'Hello, I help brands strengthen their online presence through SEO, GEO, quality backlinks, and search experience optimization. If this aligns with your current goals, I would be happy to share relevant website options for your review.',
            ]],
            'link exchange offer' => [[
                'name' => 'Mark Lee', 'email' => 'mark@linkpartners.net',
                'message' => 'We offer a link exchange with high DA sites (DA 50+). Dofollow links, niche edits and sponsored posts available. Our SEO services can get you on the first page of Google.',
            ]],
            'shortened links flood' => [[
                'name' => 'Promo Team', 'email' => 'promo@example.org',
                'message' => 'Check https://bit.ly/a1 and https://bit.ly/a2 and https://bit.ly/a3 and https://bit.ly/a4 and https://bit.ly/a5 now',
            ]],
        ];
    }

    #[DataProvider('spamSamples')]
    public function test_real_spam_is_classified_as_spam(array $data): void
    {
        $result = $this->assess($data);

        $this->assertTrue($result->isSpam(), 'Expected spam, got score '.$result->score.' ('.implode(', ', $result->rules()).')');
    }

    /** @return array<string, array{array}> */
    public static function genuineSamples(): array
    {
        return [
            'product launch' => [['name' => 'Priya Sharma', 'email' => 'priya@acme-robotics.com', 'company' => 'Acme Robotics',
                'message' => 'We are launching our new warehouse robot on 12 March and want US and India coverage. Which package would you recommend?']],
            'earnings release' => [['name' => "Tom O'Neil", 'email' => 'ir@northwind.io', 'company' => 'Northwind Holdings',
                'message' => 'We need our Q3 earnings release distributed to Yahoo Finance, Benzinga and AP News on 4 November at 8am ET.']],
            'draft as a google doc' => [['name' => 'Ana Ruiz', 'email' => 'ana@studio.es',
                'message' => 'Here is our draft: https://docs.google.com/document/d/1AbCdEf/edit and our site https://studio.es. Can you publish it next week?']],
            'asks about seo benefit' => [['name' => 'Lee Chen', 'email' => 'lee@brightapp.co',
                'message' => 'Will the press release help our SEO? Do the published articles include a link back to our website?']],
            'agency asking about backlinks and dofollow' => [['name' => 'Maria Rossi', 'email' => 'maria@prhouse.it', 'company' => 'PR House',
                'message' => 'We are a PR agency with 5 clients a month. Do the placements carry backlinks, and are they dofollow? We need pricing for 20 releases.']],
            'russian client asking for translation' => [['name' => 'Анна Петрова', 'email' => 'anna@company.ru', 'company' => 'ООО Ромашка',
                'message' => 'Здравствуйте! Нам нужен перевод пресс-релиза на английский и распространение в США. Сколько это стоит?']],
            'korean name with seo in email' => [['name' => 'Seo-yeon Park', 'email' => 'seoyeon.park@hanmail.net',
                'message' => 'Please send me the price for the GlobeNewswire package for a fintech announcement.']],
            'outreach team address' => [['name' => 'Daniel Kim', 'email' => 'outreach@greenenergy.org', 'company' => 'Green Energy Org',
                'message' => 'We want to announce our new solar project in Kenya. What does the Global package include?']],
            'short and simple' => [['name' => 'Komal Patil', 'email' => 'komal@gmail.com', 'message' => 'Hi, please call me about pricing.']],
        ];
    }

    #[DataProvider('genuineSamples')]
    public function test_genuine_enquiries_are_not_classified_as_spam(array $data): void
    {
        $result = $this->assess($data);

        $this->assertFalse($result->isSpam(), 'False positive: score '.$result->score.' ('.implode(', ', $result->rules()).')');
    }

    public function test_plain_genuine_enquiries_are_not_even_flagged_for_review(): void
    {
        foreach (['product launch', 'earnings release', 'draft as a google doc', 'asks about seo benefit', 'russian client asking for translation', 'short and simple'] as $key) {
            $result = $this->assess(self::genuineSamples()[$key][0]);
            $this->assertFalse($result->needsReview(), "{$key}: score {$result->score} (".implode(', ', $result->rules()).')');
        }
    }

    public function test_borderline_pitch_is_kept_for_review_not_spam(): void
    {
        // The softer "Jessica" style: no explicit SEO words, but a guest-post sender and a placement offer.
        $result = $this->assess([
            'name' => 'Jessica Smith', 'email' => 'jessica@guestpostservicecompany.com',
            'message' => 'Hi, I noticed vmnewswire.com was mentioned on https://www.ustimesnow.com/article. We occasionally help with getting relevant content placed on editorial websites, so I thought I would reach out.',
        ]);

        $this->assertTrue($result->needsReview());
        $this->assertFalse($result->isSpam());
    }

    public function test_behaviour_signals_and_thresholds_come_from_config(): void
    {
        $data = ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'We are launching a product next month.'];

        $this->assertTrue($this->assess($data, ['honeypot' => true])->isSpam());
        $this->assertSame(['too_fast'], $this->assess($data, ['too_fast' => true])->rules());

        config(['spam.spam_threshold' => 6]);
        $this->assertTrue($this->assess($data, ['too_fast' => true])->isSpam());
    }

    public function test_reasons_explain_the_score(): void
    {
        $result = $this->assess(self::spamSamples()['russian transfer bot'][0]);

        $this->assertSame($result->score, array_sum(array_column($result->reasons, 'points')));
        $this->assertContains('ru_money_bot', $result->rules());
        $this->assertContains('random_text', $result->rules());
        $this->assertContains('link_shortener', $result->rules());
    }
}
