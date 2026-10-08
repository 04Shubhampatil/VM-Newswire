<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Renders admin-editable headings: text is escaped, a line break becomes a desktop-only <br>,
 * and *asterisked* words become the purple <em> emphasis used across the site.
 */
final class Heading
{
    public static function render(?string $text): HtmlString
    {
        $lines = collect(preg_split('/\R/', (string) $text))
            ->map(fn ($line) => preg_replace('/\*([^*]+)\*/', '<em>$1</em>', e(trim($line))))
            ->filter()
            ->join('<br class="hidden md:block"> ');

        return new HtmlString($lines);
    }
}
