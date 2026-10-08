@php
    $tabs = [
        ['Home page', route('admin.content.edit'), 'home'],
        ['About page', route('admin.content.section', 'about'), 'about'],
        ['Media Network page', route('admin.content.section', 'media-network'), 'media-network'],
        ['Footer & legal', route('admin.content.section', 'footer-legal'), 'footer-legal'],
    ];
@endphp
<x-admin.tabs :items="collect($tabs)->map(fn ($tab) => [$tab[0], $tab[1], $tab[2] === $section])->all()" />
