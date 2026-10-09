@php $url = route('newsroom.show', $release->slug); @endphp
<article class="nrx-row">
    <a href="{{ $url }}" class="nrx-row-media" tabindex="-1" aria-hidden="true">
        @if ($release->image_url)
            <img src="{{ $release->image_url }}" alt="" loading="lazy" width="320" height="180">
        @else
            <span class="nr-card-placeholder"><x-icon name="file" :size="22" :stroke="1.5" /></span>
        @endif
    </a>
    <div class="nrx-row-body">
        <span class="nrx-cat">{{ $release->category }}</span>
        <h3 class="nrx-row-title"><a href="{{ $url }}">{{ $release->title }}</a></h3>
        <p class="nrx-meta"><span>By {{ $release->author_name }}</span><time datetime="{{ $release->published_at?->toDateString() }}">{{ $release->published_at?->format('d/m/Y') }}</time></p>
    </div>
    <a href="{{ $url }}" class="nrx-link nrx-row-link">Read more <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
</article>
