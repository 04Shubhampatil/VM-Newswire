<?php

namespace Database\Seeders;

use App\Models\PressRelease;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER CONTENT for the public Blogs section (/blogs): general guidance articles about press
 * releases and media outreach, written so the page has something to show. They are not company news.
 * Replace or edit them in Admin → Blogs.
 */
class BlogSeeder extends Seeder
{
    /** Titles of the fictional press releases the earlier NewsroomSeeder created; they are replaced. */
    public const RETIRED_TITLES = [
        'Northbridge Analytics Launches Real-Time ESG Reporting Platform for Mid-Market Companies',
        'Harbourline Logistics Expands Cold-Chain Network With Three New Distribution Hubs',
        'Meridian Capital Partners Closes $240 Million Growth Fund Focused on Climate Infrastructure',
        'Kestrel Health Receives CE Mark for AI-Assisted Retinal Screening Device',
        'Oakfield Residential Breaks Ground on 1,200-Home Net-Zero Community in Austin',
        'Lumen Street Studios Announces Global Distribution Deal for Documentary Series "Tidewater"',
        'Brightpath Learning Partners With 300 Schools to Deliver Free Financial Literacy Curriculum',
        'Voltara Mobility Opens Its 500th Fast-Charging Station and Launches Fleet Subscription Plan',
        'Ledgerwave Secures Regulatory Approval to Offer Tokenised Bond Settlement in Singapore',
        'Fieldstone Foods Commits to 100% Recyclable Packaging Across Its Snack Range by Next Year',
        'Summit Peak Apparel Reports Record Quarter Driven by Direct-to-Consumer Growth',
        'Clearwater Economic Institute Publishes Outlook: Services Growth to Offset Manufacturing Slowdown',
    ];

    public function run(): void
    {
        PressRelease::whereIn('title', self::RETIRED_TITLES)->delete();

        if (PressRelease::exists()) {
            $this->command?->info('Blog posts already exist: placeholder articles skipped.');

            return;
        }

        $images = ['images/package-story.webp', 'images/media-network-hero.webp', 'images/hero-skyline.webp'];

        foreach ($this->posts() as $i => [$title, $category, $daysAgo, $excerpt, $body]) {
            PressRelease::create([
                'title' => $title,
                'category' => $category,
                'author' => 'VM Newswire Editorial Team',
                'excerpt' => $excerpt,
                'body' => $body,
                'image_path' => $images[$i % count($images)],
                'is_published' => true,
                'published_at' => now()->subDays($daysAgo)->setTime(9, 30),
            ]);
        }
    }

    /**
     * @return array<int, array{string, string, int, string, string}>
     */
    private function posts(): array
    {
        return [
            [
                'How to Write a Press Release That Gets Noticed', 'Press Release Writing', 2,
                'Learn the key elements of a successful press release and how to craft a message that captures media attention.',
                "A press release is one of the most effective ways to get your news in front of journalists, industry analysts and your target audience. But writing a release that stands out takes more than just sharing the facts. It requires a clear structure, a compelling headline and a focus on what matters to the reader.\n\n## 1. Start with a compelling headline\n\nYour headline is the first thing people see, and it determines whether your press release gets opened. Keep it clear, concise and newsworthy. Focus on the key benefit, result or announcement, and avoid jargon or overly complex language.\n\n## 2. Lead with the key message\n\nThe opening paragraph should summarise the who, what, where, when and why. Journalists often decide within seconds whether to continue reading, so get to the point quickly and make your message easy to understand.\n\n## 3. Keep it short and structured\n\nWhile there is no strict word count, most press releases are between 300 and 600 words. Use short paragraphs, bullet points where appropriate and a clear structure with subheadings to make your release easy to scan.\n\n## 4. Add context and credibility\n\nInclude relevant background information, quotes from key people and data where possible. This helps journalists understand the bigger picture and increases the credibility of your story.\n\n## 5. End with a clear call to action\n\nTell readers what to do next, whether it is visiting your website, contacting your team or attending an event. Make it simple and direct.\n\n> A well-crafted press release does more than share news. It builds credibility, creates visibility and opens doors to new opportunities.",
            ],
            [
                'The Role of Data in Modern Press Releases', 'Media Relations', 8,
                'Numbers give journalists something concrete to report. Here is how to use data without overwhelming your story.',
                "Journalists receive hundreds of releases a week. The ones that get picked up usually give the reporter something they can verify and quote: a figure, a comparison, a trend.\n\n## Lead with one number\n\nChoose the single statistic that best supports your announcement and put it in the first two paragraphs. Supporting figures can follow, but one clear number is easier to remember and to headline.\n\n## Show the source\n\nState where each figure comes from, the period it covers and the sample size where relevant. Reporters will ask, and a release that already answers makes their job easier.\n\n## Compare, do not just count\n\n\"Forty percent faster than last year\" tells a story; \"an average of 3.2 days\" does not. Wherever possible, give the reader a comparison that explains why the number matters.\n\n## Keep charts for the follow-up\n\nA release is text first. Offer charts, tables and the underlying data as downloadable material so journalists who want more can get it without the release becoming a report.",
            ],
            [
                'How to Build Stronger Media Relationships', 'Communications', 14,
                'Coverage follows trust. Practical ways to become the contact journalists actually want to hear from.',
                "Good media relations is less about sending more and more about being useful. Reporters remember the sources that respect their time, answer quickly and never oversell.\n\n## Know the beat\n\nRead what each journalist has written recently before you contact them. A release about logistics sent to a consumer technology reporter wastes both your time and theirs.\n\n## Make the first contact about them\n\nIntroduce yourself with something relevant: a comment on a recent article, a data point they might find useful or an expert you can offer for a story they are already working on.\n\n## Respond fast and honestly\n\nWhen a journalist calls, reply the same day, even if the answer is \"we cannot comment yet\". Missing a deadline once is often enough to be dropped from the contact list.\n\n## Follow up without chasing\n\nOne polite follow-up a few days after a release is reasonable. Repeated calls asking whether the story will run are not.",
            ],
            [
                'Common Mistakes to Avoid in Your Press Release', 'PR Strategy', 21,
                'Small errors cost coverage. These are the problems editors see most often, and how to fix them before you send.',
                "Most releases that fail do so for avoidable reasons. Before you distribute, check your draft against this list.\n\n## Burying the news\n\nIf the announcement appears in paragraph four, it will not be read. The first sentence should contain the news.\n\n## Writing for your boss instead of the reader\n\nSuperlatives, mission statements and internal titles do not help a journalist. Plain language and concrete facts do.\n\n## No quote worth quoting\n\nA quote that repeats the first paragraph adds nothing. Use quotes for opinion, context or a human angle the facts cannot carry.\n\n## Missing contact details\n\nEvery release needs a named person, a direct phone number and an email address that is monitored on the day of distribution.\n\n## Sending at the wrong time\n\nAvoid Friday afternoons, public holidays and the hour after a major news event. Mid-morning on a Tuesday to Thursday is usually a safer choice.",
            ],
            [
                'Why Timing Matters in Media Outreach', 'Industry Insights', 28,
                'The same story can succeed or disappear depending on when it lands. How to choose the right moment.',
                "Distribution timing is one of the few variables fully under your control. Used well, it can double the attention a release receives.\n\n## Work backwards from the reader\n\nTrade publications often plan features months ahead; daily news desks decide each morning. Find out when the outlets you care about make decisions and send accordingly.\n\n## Avoid the crowd\n\nEarnings seasons, major product launches and industry conferences fill inboxes. Unless your news is tied to those events, pick a quieter week.\n\n## Give exclusives a head start\n\nIf one outlet gets the story first, agree the publication time with them and schedule wider distribution for immediately afterwards.\n\n## Use embargoes sparingly\n\nAn embargo can help journalists prepare, but it only works with outlets you trust. For most announcements, a clear release time is enough.",
            ],
            [
                'Reading Your Distribution Report: What the Numbers Mean', 'Distribution & Reporting', 35,
                'A distribution report lists where your release appeared. Here is how to turn that list into useful insight.',
                "After a release goes out you receive a report of placements, often with live links. It is more than a receipt.\n\n## Placements versus pickups\n\nA placement is the release published on a partner site; a pickup is a journalist writing their own story from it. Both matter, but they are measured differently, so keep them separate when you report internally.\n\n## Look at the mix\n\nWhich categories of outlet carried the release: newswires, business media, regional news, trade sites? The mix tells you which audiences you reached and which you did not.\n\n## Track what happened next\n\nMatch the report against website traffic, enquiries and social mentions in the following week. That link between distribution and results is what justifies the next release.\n\n## Keep the links\n\nLive links in the report are useful for sales decks, investor updates and your own newsroom page. Save them while they are fresh.",
            ],
        ];
    }
}
