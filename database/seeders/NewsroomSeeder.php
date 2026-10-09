<?php

namespace Database\Seeders;

use App\Models\PressRelease;
use Illuminate\Database\Seeder;

/**
 * TEST DATA for the public Newsroom (/newsroom). The companies and announcements below are
 * fictional placeholders so the page has content to show; replace them in Admin → Newsroom.
 */
class NewsroomSeeder extends Seeder
{
    public function run(): void
    {
        if (PressRelease::exists()) {
            $this->command?->info('Press releases already exist: newsroom test data skipped.');

            return;
        }

        $images = ['images/package-story.webp', 'images/media-network-hero.webp', 'images/hero-skyline.webp'];

        foreach ($this->releases() as $i => [$title, $category, $author, $daysAgo, $excerpt, $body]) {
            PressRelease::create([
                'title' => $title,
                'category' => $category,
                'author' => $author,
                'excerpt' => $excerpt,
                'body' => $body,
                'image_path' => $images[$i % count($images)],
                'is_published' => true,
                'published_at' => now()->subDays($daysAgo)->setTime(9 + ($i % 6), 15 * ($i % 4)),
            ]);
        }
    }

    /**
     * @return array<int, array{string, string, string, int, string, string}>
     */
    private function releases(): array
    {
        return [
            [
                'Northbridge Analytics Launches Real-Time ESG Reporting Platform for Mid-Market Companies',
                'Technology', 'Priya Nair', 1,
                'The new platform gives finance teams a single dashboard for emissions, supply-chain and governance data, with audit-ready exports built in.',
                "**LONDON** — Northbridge Analytics today announced the general availability of its real-time ESG reporting platform, built for mid-market companies that need investor-grade sustainability data without an enterprise-sized reporting team.\n\n## One dashboard for every disclosure\n\nThe platform connects to accounting, logistics and energy systems, normalises the data against the leading reporting frameworks and produces audit-ready exports in minutes. Finance teams can track emissions, supplier risk and board-level governance metrics from a single view.\n\n\"Mid-market companies are being asked the same questions as listed multinationals, with a fraction of the resources,\" said Priya Nair, Chief Executive of Northbridge Analytics. \"We built the platform so a two-person finance team can answer those questions with confidence.\"\n\n## Availability\n\nThe platform is available today in the United Kingdom, Ireland and the Netherlands, with wider European availability planned for early next year. Pricing is based on company size and starts with a 30-day guided onboarding.\n\n### About Northbridge Analytics\n\nNorthbridge Analytics builds reporting software for finance teams. The company is headquartered in London and backed by Halden Ventures.",
            ],
            [
                'Harbourline Logistics Expands Cold-Chain Network With Three New Distribution Hubs',
                'Business', 'Daniel Okafor', 3,
                'The expansion adds temperature-controlled capacity in Rotterdam, Lyon and Barcelona, cutting delivery times for pharmaceutical and fresh-food clients.',
                "**ROTTERDAM** — Harbourline Logistics has opened three new temperature-controlled distribution hubs in Rotterdam, Lyon and Barcelona, adding more than 40,000 square metres of cold-chain capacity to its European network.\n\n## Faster delivery for sensitive goods\n\nThe hubs are positioned along the company's busiest pharmaceutical and fresh-food corridors and are expected to reduce average delivery times on those routes by up to 18 hours. Each site operates 24 hours a day with continuous temperature monitoring and automated exception alerts.\n\n\"Our customers ship products where a two-degree change matters,\" said Daniel Okafor, Chief Operating Officer. \"This investment gives them shorter routes, more monitoring and more predictable arrival windows.\"\n\n## Investment and jobs\n\nThe three hubs represent a combined investment of €62 million and will create approximately 310 permanent roles across operations, quality assurance and fleet management.\n\n### About Harbourline Logistics\n\nHarbourline Logistics provides temperature-controlled freight, warehousing and last-mile services across 14 European countries.",
            ],
            [
                'Meridian Capital Partners Closes $240 Million Growth Fund Focused on Climate Infrastructure',
                'Finance', 'Sofia Lindqvist', 5,
                'Fund III will back grid storage, industrial heat recovery and water-efficiency companies across North America and Europe.',
                "**NEW YORK** — Meridian Capital Partners today announced the final close of Meridian Growth Fund III at $240 million, exceeding its $200 million target. The fund will invest in growth-stage companies building climate infrastructure, including grid-scale storage, industrial heat recovery and water-efficiency technology.\n\n## Investor base\n\nFund III attracted commitments from pension funds, insurance companies, university endowments and family offices across North America and Europe, with more than 60 percent of capital coming from returning investors.\n\n\"Infrastructure is where climate ambition becomes physical,\" said Sofia Lindqvist, Managing Partner. \"Fund III lets us back the operators who are actually building it.\"\n\n## First investments\n\nThe fund has already completed two investments: a long-duration storage developer in Texas and a heat-recovery specialist serving food manufacturers in Germany. Further announcements are expected over the coming quarters.\n\n### About Meridian Capital Partners\n\nMeridian Capital Partners is a growth-equity firm with offices in New York and Stockholm and more than $900 million under management.",
            ],
            [
                'Kestrel Health Receives CE Mark for AI-Assisted Retinal Screening Device',
                'Health', 'Dr. Amara Bello', 8,
                'The handheld device enables primary-care clinics to screen for diabetic retinopathy in under four minutes without a specialist on site.',
                "**DUBLIN** — Kestrel Health has received CE marking for KestrelView, a handheld retinal screening device that uses on-device artificial intelligence to flag signs of diabetic retinopathy in primary-care settings.\n\n## Screening where patients already are\n\nKestrelView captures a retinal image and returns a referral recommendation in under four minutes. Clinics can screen patients during routine diabetes appointments rather than referring them to hospital eye departments, where waiting lists in several countries exceed six months.\n\n\"Most sight loss from diabetes is preventable if it is caught early,\" said Dr. Amara Bello, Chief Medical Officer. \"Putting screening in the clinic patients already visit removes the biggest barrier to that.\"\n\n## Clinical evidence\n\nIn a multi-site study of 4,120 patients, the device achieved 94 percent sensitivity and 91 percent specificity against specialist grading. Full results have been submitted for peer review.\n\n### About Kestrel Health\n\nKestrel Health develops point-of-care diagnostic devices for chronic disease management and is headquartered in Dublin, Ireland.",
            ],
            [
                'Oakfield Residential Breaks Ground on 1,200-Home Net-Zero Community in Austin',
                'Real Estate', 'Marcus Reed', 11,
                'The 180-acre development combines all-electric homes, community solar and a car-light street plan, with the first residents expected to move in next year.',
                "**AUSTIN, Texas** — Oakfield Residential has broken ground on Cedar Run, a 1,200-home net-zero community on 180 acres in north-east Austin. The development is designed to produce as much energy as it consumes over the course of a year.\n\n## How the numbers add up\n\nEvery home is all-electric with rooftop solar and a heat-pump system, and the community shares a 9-megawatt solar array and battery installation. Streets prioritise walking, cycling and a shuttle link to the regional rail line, with parking consolidated at the edges of each neighbourhood.\n\n\"Buyers want lower bills and a neighbourhood they can walk around,\" said Marcus Reed, President of Oakfield Residential. \"Cedar Run delivers both without asking them to compromise on space.\"\n\n## Timeline\n\nThe first phase of 280 homes is scheduled for completion next year, with the full community built out over five years. Prices for the first phase will be announced in the spring.\n\n### About Oakfield Residential\n\nOakfield Residential is a Texas-based developer of master-planned communities with more than 9,000 homes delivered since 2004.",
            ],
            [
                'Lumen Street Studios Announces Global Distribution Deal for Documentary Series "Tidewater"',
                'Entertainment', 'Hannah Castellanos', 14,
                'The six-part series on coastal communities adapting to rising seas will premiere in 42 territories this autumn.',
                "**LOS ANGELES** — Lumen Street Studios has signed a global distribution agreement for \"Tidewater,\" its six-part documentary series following coastal communities in Bangladesh, Louisiana, the Netherlands and Fiji as they adapt to rising sea levels.\n\n## A series three years in the making\n\nFilmed over three years, \"Tidewater\" follows engineers, fishermen, mayors and schoolchildren as their towns rebuild, relocate or hold the line. The series premiered at a documentary festival in Amsterdam this spring, where it received the audience award.\n\n\"These are not stories about the future,\" said Hannah Castellanos, Executive Producer. \"They are about decisions people are making right now, and we wanted audiences everywhere to see them.\"\n\n## Release\n\nThe series will be available in 42 territories this autumn, with localised versions in 11 languages. An accompanying educational programme for secondary schools will launch alongside the release.\n\n### About Lumen Street Studios\n\nLumen Street Studios is an independent production company based in Los Angeles specialising in long-form documentary.",
            ],
            [
                'Brightpath Learning Partners With 300 Schools to Deliver Free Financial Literacy Curriculum',
                'Education', 'Grace Mwangi', 17,
                'The programme reaches 48,000 students across the United Kingdom this academic year, with lesson plans, teacher training and progress tracking included.',
                "**MANCHESTER** — Brightpath Learning has partnered with 300 secondary schools across the United Kingdom to deliver its financial literacy curriculum free of charge during the current academic year, reaching an estimated 48,000 students.\n\n## What schools receive\n\nThe programme includes 24 ready-to-teach lessons covering budgeting, borrowing, saving, tax and consumer rights, along with teacher training and a progress dashboard. Lessons are mapped to the national curriculum and can be delivered in personal development, maths or citizenship periods.\n\n\"Teachers tell us they want to teach this but have no time to build it,\" said Grace Mwangi, Founder of Brightpath Learning. \"So we built it for them, and we removed the cost.\"\n\n## Funding\n\nThe rollout is funded by a consortium of building societies and credit unions and will be independently evaluated by a university research team, with results published next summer.\n\n### About Brightpath Learning\n\nBrightpath Learning develops classroom resources for life skills education and works with more than 1,100 schools.",
            ],
            [
                'Voltara Mobility Opens Its 500th Fast-Charging Station and Launches Fleet Subscription Plan',
                'Automotive', 'Tomas Weber', 21,
                'The milestone station in Munich adds 24 ultra-fast bays, while the new plan gives commercial fleets fixed monthly pricing across the whole network.',
                "**MUNICH** — Voltara Mobility has opened its 500th fast-charging station, a 24-bay ultra-fast site beside the A9 motorway in Munich, and announced a subscription plan that gives commercial fleets fixed monthly pricing across its entire European network.\n\n## Built for fleets\n\nThe Voltara Fleet plan replaces per-session pricing with a single monthly rate per vehicle, with reserved bays available at the company's 80 busiest sites. Fleet managers get consolidated invoicing, driver-level reporting and API access for route planning.\n\n\"Fleet operators do not want surprises on their energy bill,\" said Tomas Weber, Chief Commercial Officer. \"A fixed rate makes electrification a planning decision rather than a gamble.\"\n\n## Network growth\n\nVoltara has added 140 stations in the past twelve months and plans to reach 800 sites across nine countries by the end of next year.\n\n### About Voltara Mobility\n\nVoltara Mobility operates one of Europe's largest fast-charging networks and is headquartered in Munich.",
            ],
            [
                'Ledgerwave Secures Regulatory Approval to Offer Tokenised Bond Settlement in Singapore',
                'Blockchain', 'Wei Lin Tan', 25,
                'The approval allows institutional clients to settle corporate bond trades on Ledgerwave\'s permissioned network in minutes rather than two days.',
                "**SINGAPORE** — Ledgerwave has received regulatory approval to operate a tokenised bond settlement service for institutional clients in Singapore. The service settles corporate bond trades on a permissioned distributed ledger in minutes, compared with the two-day cycle common today.\n\n## How it works\n\nBonds are issued as digital tokens with the legal terms embedded, and cash legs settle through a regulated digital-cash facility. Both sides of the trade are completed at the same moment, removing settlement risk and reducing the collateral participants must hold.\n\n\"Faster settlement is not about novelty, it is about freeing up capital,\" said Wei Lin Tan, Chief Executive of Ledgerwave. \"Every day a trade sits unsettled is a day that money cannot work.\"\n\n## Launch partners\n\nThree regional banks and two asset managers will use the service from launch, with the first live issuance expected within the quarter.\n\n### About Ledgerwave\n\nLedgerwave provides settlement infrastructure for regulated financial institutions and is headquartered in Singapore.",
            ],
            [
                'Fieldstone Foods Commits to 100% Recyclable Packaging Across Its Snack Range by Next Year',
                'Lifestyle', 'Elena Rossi', 30,
                'The move removes 2,400 tonnes of mixed-material packaging annually and follows an 18-month redesign of the company\'s best-selling lines.',
                "**MILAN** — Fieldstone Foods has committed to making the packaging of its entire snack range fully recyclable by the end of next year, a change the company says will remove 2,400 tonnes of mixed-material packaging from circulation annually.\n\n## Redesigning the best-sellers first\n\nThe company spent 18 months redesigning its six best-selling products, replacing multi-layer films with mono-material alternatives that can be processed by standard kerbside recycling. The new packs reach shelves across Italy, France and Spain this month.\n\n\"Shoppers should not need a chemistry degree to recycle a bag of crisps,\" said Elena Rossi, Head of Sustainability. \"Our job was to make the right choice the easy one.\"\n\n## Next steps\n\nThe remaining 22 products will move to the new packaging in stages, with progress reported quarterly on the company's website.\n\n### About Fieldstone Foods\n\nFieldstone Foods makes baked and roasted snacks sold in 19 countries and employs 2,100 people.",
            ],
            [
                'Summit Peak Apparel Reports Record Quarter Driven by Direct-to-Consumer Growth',
                'Fashion', 'James Whitaker', 34,
                'Revenue rose 27 percent year on year as the outdoor brand expanded its own stores and repair programme.',
                "**DENVER** — Summit Peak Apparel today reported record quarterly revenue of $118 million, up 27 percent year on year, driven by growth in its own stores and online channel and strong demand for its repair and resale programme.\n\n## Direct channels lead\n\nDirect-to-consumer sales now account for 61 percent of revenue, up from 48 percent two years ago. The company opened four stores during the quarter and plans six more before the holiday season.\n\n\"Customers want gear that lasts and a brand that will fix it when it does not,\" said James Whitaker, Chief Executive. \"That relationship is what is driving these numbers.\"\n\n## Repair programme\n\nThe company's repair service handled 41,000 items during the quarter, and its resale platform sold more than 60,000 pre-owned pieces.\n\n### About Summit Peak Apparel\n\nSummit Peak Apparel designs outdoor clothing and equipment and is headquartered in Denver, Colorado.",
            ],
            [
                'Clearwater Economic Institute Publishes Outlook: Services Growth to Offset Manufacturing Slowdown',
                'Economics', 'Dr. Nadia Haddad', 40,
                'The institute\'s autumn outlook forecasts 1.9 percent growth next year, with professional services and tourism carrying most of the expansion.',
                "**GENEVA** — The Clearwater Economic Institute has published its autumn outlook, forecasting growth of 1.9 percent next year as expansion in professional services and tourism offsets a slowdown in manufacturing output.\n\n## Key findings\n\nThe report expects manufacturing to contract slightly in the first half of the year before stabilising, while services employment continues to rise. Inflation is projected to return to target by mid-year, allowing central banks to ease borrowing costs gradually.\n\n\"The headline number hides two different economies,\" said Dr. Nadia Haddad, Chief Economist. \"Policymakers should plan for both.\"\n\n## Risks\n\nThe institute flags energy prices, trade restrictions and a weaker-than-expected housing recovery as the main downside risks to the forecast.\n\n### About the Clearwater Economic Institute\n\nThe Clearwater Economic Institute is an independent research organisation publishing economic analysis for governments, businesses and the public.",
            ],
        ];
    }
}
