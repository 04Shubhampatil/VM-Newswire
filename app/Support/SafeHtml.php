<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\HtmlString;

/**
 * Sanitises owner-written HTML from the admin blog editor. Only a small set of
 * formatting tags survives; the only attribute kept is a text-align style (plus
 * safe href/rel/target on links). Scripts, event handlers and unknown tags are
 * removed, so the output is safe to echo unescaped.
 */
class SafeHtml
{
    private const ALLOWED = ['p', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'a', 'blockquote', 'br', 'hr', 'sub', 'sup'];

    private const ALIGNS = ['left', 'center', 'right', 'justify'];

    /** True when the text looks like HTML saved by the editor rather than Markdown. */
    public static function looksLikeHtml(?string $text): bool
    {
        return (bool) preg_match('/^\s*<(p|h[1-6]|ul|ol|blockquote|div)[\s>]/i', (string) $text);
    }

    public static function render(?string $html): HtmlString
    {
        if (blank($html)) {
            return new HtmlString('');
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return new HtmlString('');
        }

        self::clean($body, $doc);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return new HtmlString(trim($out));
    }

    private static function clean(DOMNode $node, DOMDocument $doc): void
    {
        // Iterate over a static copy: the list changes while nodes are replaced.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }
            if (! $child instanceof DOMElement) {
                $node->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'noscript'], true)) {
                $node->removeChild($child);

                continue;
            }

            if ($tag === 'h1') {
                $tag = 'h2';
                $child = self::rename($child, 'h2', $doc);
            } elseif ($tag === 'b') {
                $tag = 'strong';
                $child = self::rename($child, 'strong', $doc);
            } elseif ($tag === 'i') {
                $tag = 'em';
                $child = self::rename($child, 'em', $doc);
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // Unknown wrapper (div, span, font…): keep its content, drop the tag.
                self::clean($child, $doc);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag);
            self::clean($child, $doc);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $align = null;
        if ($el->hasAttribute('style') && preg_match('/text-align\s*:\s*(left|center|right|justify)/i', $el->getAttribute('style'), $m)) {
            $align = strtolower($m[1]);
        }
        if ($el->hasAttribute('class') && preg_match('/ql-align-(center|right|justify)/', $el->getAttribute('class'), $m)) {
            $align = $m[1];
        }

        $href = $tag === 'a' ? trim($el->getAttribute('href')) : null;

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->name);
        }

        if ($align && in_array($align, self::ALIGNS, true) && $align !== 'left') {
            $el->setAttribute('style', 'text-align:'.$align);
        }

        if ($tag === 'a') {
            if ($href !== '' && preg_match('#^(https?://|mailto:|/|\#)#i', $href)) {
                $el->setAttribute('href', $href);
                if (preg_match('#^https?://#i', $href)) {
                    $el->setAttribute('target', '_blank');
                    $el->setAttribute('rel', 'noopener');
                }
            }
        }
    }

    private static function rename(DOMElement $el, string $tag, DOMDocument $doc): DOMElement
    {
        $new = $doc->createElement($tag);
        foreach (iterator_to_array($el->attributes) as $attr) {
            $new->setAttribute($attr->name, $attr->value);
        }
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }
        $el->parentNode->replaceChild($new, $el);

        return $new;
    }
}
