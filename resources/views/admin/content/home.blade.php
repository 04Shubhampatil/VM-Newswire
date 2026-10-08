@php
    $stripLogosData = collect($settings->mediaStripLogos())->map(function (array $item) {
        $item['logo_url'] = \App\Services\SiteSettings::logoUrl($item['logo'] ?? null);

        return $item;
    })->values()->all();
    $confidenceCards = collect($settings->confidenceCards())->map(function (array $card) {
        $card['logo_url'] = \App\Services\SiteSettings::logoUrl($card['logo'] ?? null);

        return $card;
    })->values()->all();
    $journalistsImageUrl = $settings->get('journalists_image') ? Storage::disk(config('vmnewswire.posters.disk'))->url($settings->get('journalists_image')) : null;
    $highlightedCount = \App\Models\MediaOutlet::active()->where('is_highlighted', true)->count();
@endphp
<x-layouts.admin title="Website Content" description="Edit the copy and images shown on the public website. Changes go live as soon as you save." :breadcrumbs="[['Website Content', route('admin.content.edit')], ['Home page', null]]">
    <x-slot:actions>
        <x-button :href="route('home')" variant="secondary" size="sm" target="_blank" icon-left="external">Preview homepage</x-button>
    </x-slot:actions>

    @include('admin.content.partials.tabs')

    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="home">

        <x-admin.panel title="1 · Hero" description="The first section visitors see. Leave a field empty to use the default wording.">
            <div class="grid gap-5 md:grid-cols-2">
                <x-admin.input name="hero_eyebrow" label="Eyebrow (small line above the headline)" :value="$settings->get('hero_eyebrow')" maxlength="80" />
                <x-admin.input name="hero_reach_label" label="Reach card line" :value="$settings->get('hero_reach_label')" maxlength="60" help="Shown under “Global Reach” on the hero photo. The outlets card uses the network size from Settings." />
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <x-admin.input name="hero_headline" label="Headline" :value="$settings->get('hero_headline')" maxlength="120" />
                <x-admin.input name="hero_headline_highlight" label="Headline, second part" :value="$settings->get('hero_headline_highlight')" maxlength="120" help="Appended to the headline, e.g. “The News Starts” + “Here”." />
            </div>
            <x-admin.textarea name="hero_text" label="Intro text" :value="$settings->get('hero_text')" rows="3" maxlength="400" />
            <div class="grid gap-5 md:grid-cols-2">
                <x-admin.input name="hero_primary_label" label="Primary button label" :value="$settings->get('hero_primary_label')" maxlength="40" help="Opens the enquiry form." />
                <x-admin.input name="hero_secondary_label" label="Secondary button label" :value="$settings->get('hero_secondary_label')" maxlength="40" help="Links to the media network." />
            </div>
        </x-admin.panel>

        @php
            $homepageOutlets = \App\Models\MediaOutlet::active()->orderByDesc('created_at')->orderByDesc('id')->get(['id', 'name', 'category', 'is_highlighted', 'poster_path']);
            $cardLimit = \App\Support\CatalogCache::HOMEPAGE_CARD_LIMIT;
        @endphp
        <x-admin.panel title="2 · News cards" :description="'The card row under the hero. Tick the outlets to show; the '.$cardLimit.' most recently added of them appear, newest first. Each card\'s poster, name, category and short description come from the outlet.'">
            <div x-data="{ picked: {{ $homepageOutlets->where('is_highlighted', true)->count() }} }" class="flex flex-col gap-3">
                <input type="hidden" name="homepage_outlets_present" value="1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[13px] text-muted"><span x-text="picked"></span> selected · showing <span x-text="Math.min(picked, {{ $cardLimit }})"></span> of {{ $cardLimit }} cards
                        <span x-show="picked > {{ $cardLimit }}" x-cloak class="text-[#7a5b00]"> · only the {{ $cardLimit }} newest selected outlets are shown</span></p>
                    <a href="{{ route('admin.media.create') }}" class="admin-link">Add a new outlet →</a>
                </div>
                @if ($homepageOutlets->isEmpty())
                    <div class="rounded-[10px] border border-dashed border-line px-6 py-8 text-center text-[13px] text-muted">No active outlets yet. Add outlets under Media Network first.</div>
                @else
                    <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($homepageOutlets as $outlet)
                            <li>
                                <label class="flex cursor-pointer items-center gap-3 rounded-[10px] border border-line px-3 py-2.5 transition hover:border-[#b9c7d4] has-[:checked]:border-teal has-[:checked]:bg-teal-soft/40">
                                    <input type="checkbox" name="homepage_outlets[]" value="{{ $outlet->id }}" @checked($outlet->is_highlighted) @change="picked += $event.target.checked ? 1 : -1" class="size-4 accent-navy-900">
                                    <span class="flex min-w-0 grow flex-col">
                                        <span class="truncate text-[14px] font-semibold text-heading">{{ $outlet->name }}</span>
                                        <span class="text-[12px] text-muted">{{ $outlet->category }}@unless ($outlet->poster_path) · <span class="text-[#7a5b00]">no poster</span>@endunless</span>
                                    </span>
                                    <a href="{{ route('admin.media.edit', $outlet) }}" class="admin-link shrink-0 text-[12px]" @click.stop>Edit</a>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_240px]">
                <x-admin.input name="news_heading" label="Section heading (optional)" :value="$settings->get('news_heading')" maxlength="160" help="Leave empty for no heading, as in the original design. Wrap a phrase in *asterisks* to colour it teal." />
                <x-admin.input name="news_button_label" label="Button label" :value="$settings->get('news_button_label')" maxlength="40" help="Links to the media network." />
            </div>
        </x-admin.panel>

        <x-admin.panel title="3 · Packages section" description="The heading above the package slider. Package names and prices are managed under Packages.">
            <div class="grid gap-5 md:grid-cols-[220px_minmax(0,1fr)]">
                <x-admin.input name="packages_eyebrow" label="Eyebrow" :value="$settings->get('packages_eyebrow')" maxlength="80" />
                <x-admin.input name="packages_heading" label="Heading" :value="$settings->get('packages_heading')" maxlength="160" help="Wrap a phrase in *asterisks* to colour it teal. A new line becomes a line break on desktop." />
            </div>
            <x-admin.input name="packages_text" label="Short description" :value="$settings->get('packages_text')" maxlength="300" />
        </x-admin.panel>

        <x-admin.panel title="4 · Confidence cards" description="The “Deliver Your News with Confidence” section: heading, button and the quote cards. Three cards fit one row; up to six are allowed.">
            <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_240px]">
                <x-admin.textarea name="confidence_heading" label="Heading" :value="$settings->get('confidence_heading')" rows="2" maxlength="160" help="A new line becomes a line break on desktop. Wrap a phrase in *asterisks* to colour it teal." />
                <x-admin.input name="confidence_button_label" label="Button label" :value="$settings->get('confidence_button_label')" maxlength="40" help="Links to the sample reports page." />
            </div>

            <div x-data="cardList(@js($confidenceCards))" class="flex flex-col gap-4">
                <input type="hidden" name="confidence_cards_present" value="1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[13px] text-muted"><span x-text="cards.length"></span> card(s). Each card can carry a logo (shown bottom-right; the VM mark is used without one). Only attribute a quote to a real customer with their permission; removing every card restores the built-in three.</p>
                    <button type="button" @click="add()" x-show="cards.length < 6" class="btn btn-secondary btn-sm"><x-icon name="plus" :size="15" />Add card</button>
                </div>
                <div class="grid gap-3 lg:grid-cols-3">
                    <template x-for="(card, index) in cards" :key="card.id">
                        <div class="flex flex-col gap-2.5 rounded-[10px] border border-line p-4">
                            <div class="flex items-center justify-between text-[12px] font-semibold text-muted">
                                <span x-text="'Card ' + (index + 1)"></span>
                                <button type="button" @click="remove(index)" class="admin-link-danger text-[12px]">Remove</button>
                            </div>
                            <textarea :name="'confidence_cards[' + index + '][quote]'" x-model="card.quote" rows="5" maxlength="400" placeholder="Quote" aria-label="Quote" class="admin-input text-[13px]"></textarea>
                            <input type="text" :name="'confidence_cards[' + index + '][name]'" x-model="card.name" maxlength="80" placeholder="Name" aria-label="Name" class="admin-input h-9 text-[13px]">
                            <input type="text" :name="'confidence_cards[' + index + '][role]'" x-model="card.role" maxlength="80" placeholder="Role or company" aria-label="Role" class="admin-input h-9 text-[13px]">
                            <div class="flex items-center gap-3 border-t border-line-soft pt-2.5">
                                <div class="flex h-12 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[8px] border border-line bg-white p-1.5">
                                    <template x-if="card.preview"><img :src="card.preview" alt="" class="max-h-full max-w-full object-contain"></template>
                                    <template x-if="! card.preview"><span class="text-[10px] font-semibold text-muted-soft uppercase">VM mark</span></template>
                                </div>
                                <div class="flex min-w-0 grow flex-col gap-1">
                                    <input type="hidden" :name="'confidence_cards[' + index + '][existing_logo]'" :value="card.existing_logo">
                                    <input type="file" :name="'confidence_card_logos[' + index + ']'" accept="image/jpeg,image/png,image/webp,image/svg+xml" @change="pick($event, index)" aria-label="Logo" class="admin-input text-[12px]">
                                    <button type="button" x-show="card.preview" @click="clearLogo(index)" class="admin-link-danger self-start text-[12px]">Remove logo</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel title="5 · Media logo strip" description="The scrolling row of media logos. Upload logos cropped tightly to the artwork; they display 56px tall in full colour.">
            <x-admin.input name="trust_strip_heading" label="Label above the logos" :value="$settings->get('trust_strip_heading')" maxlength="120" help="{network} inserts the network size from Settings." />

            <div x-data="logoList(@js($stripLogosData))" class="flex flex-col gap-4">
                <input type="hidden" name="media_strip_logos_present" value="1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[13px] text-muted"><span x-text="logos.length"></span> logo(s). With none added, the strip shows the highlighted outlets as text.</p>
                    <button type="button" @click="add()" class="btn btn-secondary btn-sm"><x-icon name="plus" :size="15" />Add logo</button>
                </div>

                <template x-if="logos.length === 0">
                    <div class="rounded-[10px] border border-dashed border-line px-6 py-8 text-center text-[13px] text-muted">No logos yet. Click “Add logo” to upload the first one.</div>
                </template>

                <div class="grid gap-3 md:grid-cols-2">
                    <template x-for="(logo, index) in logos" :key="logo.id">
                        <div class="flex gap-4 rounded-[10px] border border-line p-4">
                            <div class="flex h-16 w-28 shrink-0 items-center justify-center overflow-hidden rounded-[8px] border border-line bg-white p-2">
                                <template x-if="logo.preview"><img :src="logo.preview" alt="" class="max-h-full max-w-full object-contain"></template>
                                <template x-if="! logo.preview"><span class="text-[11px] font-semibold text-muted-soft uppercase">No file</span></template>
                            </div>
                            <div class="flex min-w-0 grow flex-col gap-2.5">
                                <input type="hidden" :name="'strip_logos[' + index + '][existing_logo]'" :value="logo.existing_logo">
                                <input type="file" :name="'strip_logo_files[' + index + ']'" accept="image/jpeg,image/png,image/webp,image/svg+xml" @change="pick($event, index)" class="admin-input text-[13px]">
                                <div class="grid gap-2.5 sm:grid-cols-2">
                                    <input type="text" :name="'strip_logos[' + index + '][name]'" x-model="logo.name" placeholder="Outlet name" aria-label="Outlet name" class="admin-input h-9 text-[13px]">
                                    <input type="url" :name="'strip_logos[' + index + '][link]'" x-model="logo.link" placeholder="https:// (optional link)" aria-label="Link" class="admin-input h-9 text-[13px]">
                                </div>
                                <button type="button" @click="remove(index)" class="admin-link-danger self-start text-[12px]">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel title="6 · Journalists & media tools" description="The “Discover Your Next Story” block with the product screenshot.">
            <div class="grid gap-5 md:grid-cols-2">
                <x-admin.input name="journalists_eyebrow" label="Eyebrow" :value="$settings->get('journalists_eyebrow')" maxlength="80" />
                <x-admin.input name="journalists_heading" label="Heading" :value="$settings->get('journalists_heading')" maxlength="120" />
            </div>
            <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_240px]">
                <x-admin.textarea name="journalists_text" label="Text" :value="$settings->get('journalists_text')" rows="3" maxlength="500" help="{company} inserts the company name from Settings." />
                <x-admin.input name="journalists_link_label" label="Link label" :value="$settings->get('journalists_link_label')" maxlength="40" help="Links to the media network." />
            </div>
            <div class="grid gap-5 border-t border-line-soft pt-5 md:grid-cols-[280px_minmax(0,1fr)]" :x-data="'posterPicker('.Js::from($journalistsImageUrl ?? asset('images/media-network-screenshot.webp')).')'">
                <div class="flex flex-col gap-2">
                    <div class="aspect-[16/10] overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                        <template x-if="preview"><img :src="preview" alt="Screenshot preview" class="size-full object-cover object-top"></template>
                    </div>
                    <p class="text-[12px] font-medium text-muted" x-text="preview && preview !== current ? 'New image (saved on submit)' : '{{ $journalistsImageUrl ? 'Current image' : 'Default screenshot' }}'"></p>
                </div>
                <div class="flex flex-col gap-4">
                    <div>
                        <label for="journalists_image" class="admin-label">Screenshot image</label>
                        <input id="journalists_image" type="file" name="journalists_image" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input">
                        <p class="admin-help">JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB. Shown inside a browser frame; a 16:10 screenshot works best. Without an upload the bundled screenshot of the Media Network page is used.</p>
                        <p class="admin-help text-danger" x-show="error" x-text="error"></p>
                        @error('journalists_image')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new image</button>
                        @if ($journalistsImageUrl)
                            <label class="flex h-9 items-center gap-2 text-[13px]"><input type="hidden" name="remove_journalists_image" value="0"><input type="checkbox" name="remove_journalists_image" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-navy-900"> Use the default screenshot again</label>
                        @endif
                    </div>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.form-actions>
            <x-slot:note>Saving publishes the changes immediately.</x-slot:note>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />Save home page</button>
        </x-admin.form-actions>
    </form>

    <script>
        (function () {
            const uid = (prefix) => prefix + Math.random().toString(36).slice(2, 11);

            window.logoList = function (initial) {
                const row = (item = {}) => ({ id: uid('logo_'), name: item.name || '', link: item.link || '', existing_logo: item.logo || '', preview: item.logo_url || '' });

                return {
                    logos: (initial || []).map(row),
                    add() { this.logos.push(row()); },
                    remove(index) { this.logos.splice(index, 1); },
                    pick(event, index) {
                        const file = event.target.files && event.target.files[0];
                        if (! file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.logos[index].preview = e.target.result; };
                        reader.readAsDataURL(file);
                    },
                };
            };

            window.cardList = function (initial) {
                const row = (item = {}) => ({ id: uid('card_'), quote: item.quote || '', name: item.name || '', role: item.role || '', existing_logo: item.logo || '', preview: item.logo_url || '' });

                return {
                    cards: (initial || []).map(row),
                    add() { if (this.cards.length < 6) this.cards.push(row()); },
                    remove(index) { this.cards.splice(index, 1); },
                    pick(event, index) {
                        const file = event.target.files && event.target.files[0];
                        if (! file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.cards[index].preview = e.target.result; };
                        reader.readAsDataURL(file);
                    },
                    clearLogo(index) {
                        this.cards[index].existing_logo = '';
                        this.cards[index].preview = '';
                        const input = document.querySelector('input[name="confidence_card_logos[' + index + ']"]');
                        if (input) input.value = '';
                    },
                };
            };
        })();
    </script>
</x-layouts.admin>
