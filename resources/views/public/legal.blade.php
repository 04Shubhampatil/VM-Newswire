@use('App\Support\SafeMarkdown')
@php
    $content = $site->get($key.'_content');
    $updated = $site->get($key.'_updated_at');
@endphp
<x-layouts.public :title="$title">
    <section class="pt-9 pb-16 md:pt-16 md:pb-24 lg:pt-[72px]">
        <div class="container-site flex flex-col gap-10">
            <div class="flex max-w-[820px] flex-col gap-5">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => $title]]" />
                <p class="eyebrow">Legal</p>
                <h1 class="display text-[34px] leading-[1.1] tracking-[-0.02em] md:text-[36px] lg:text-[54px]">{{ $title }}</h1>
                @if ($updated)
                    <p class="text-sm text-muted">Last updated {{ \Illuminate\Support\Carbon::parse($updated)->format('j F Y') }}</p>
                @endif
            </div>
            <div class="max-w-[760px] border-t border-line pt-6">
                @if (filled($content))
                    <div class="prose-vmn [&_blockquote]:mb-6 [&_blockquote]:rounded-[6px] [&_blockquote]:bg-accent-soft [&_blockquote]:px-5 [&_blockquote]:py-4 [&_blockquote_p]:mb-0">{{ SafeMarkdown::render($content) }}</div>
                @else
                    <p class="text-muted">This page is being prepared. For questions, email <a href="mailto:{{ $site->get('company_email') }}" class="text-accent-ink underline">{{ $site->get('company_email') }}</a>.</p>
                @endif
            </div>
        </div>
    </section>
    <x-cta-band />
</x-layouts.public>
