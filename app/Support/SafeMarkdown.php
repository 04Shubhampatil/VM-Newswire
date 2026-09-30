<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Renders owner-written Markdown. Raw HTML is stripped and unsafe links
 * (javascript:, data:, …) are removed, so the output is safe to echo unescaped.
 */
class SafeMarkdown
{
    public static function render(?string $markdown): HtmlString
    {
        if (blank($markdown)) {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]));
    }
}
