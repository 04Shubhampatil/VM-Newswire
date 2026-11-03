<?php

namespace App\Models;

use Database\Factories\PressReleaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\SafeHtml;
use App\Support\SafeMarkdown;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A blog post shown in the public Blogs section (/blogs). The table keeps its original
 * "press_releases" name from when the section was the Newsroom.
 */
#[Fillable(['title', 'slug', 'category', 'author', 'excerpt', 'body', 'image_path', 'meta_title', 'meta_description', 'faqs', 'is_published', 'published_at'])]
class PressRelease extends Model
{
    /** @use HasFactory<PressReleaseFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'Media Relations', 'Press Release Writing', 'PR Strategy', 'Industry Insights', 'Communications', 'Distribution & Reporting',
    ];

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * This post's own Q&A, with blank rows dropped.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    public function faqList(): array
    {
        return array_values(array_filter(
            array_map(fn ($row) => ['question' => trim((string) ($row['question'] ?? '')), 'answer' => trim((string) ($row['answer'] ?? ''))], $this->faqs ?? []),
            fn ($row) => $row['question'] !== '' && $row['answer'] !== '',
        ));
    }

    protected static function booted(): void
    {
        static::saving(function (PressRelease $post) {
            if (blank($post->slug)) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
            if ($post->is_published && $post->published_at === null) {
                $post->published_at = now();
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title), 90, '') ?: 'post';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Visible on the public site: published flag set and the publish date not in the future. */
    public function scopePublished(Builder $query): void
    {
        $query->where($query->qualifyColumn('is_published'), true)
            ->whereNotNull($query->qualifyColumn('published_at'))
            ->where($query->qualifyColumn('published_at'), '<=', now());
    }

    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc($query->qualifyColumn('published_at'))->orderByDesc($query->qualifyColumn('id'));
    }

    /** Full URL for the cover image: bundled asset ("images/…") or an upload on the public disk. */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (blank($this->image_path)) {
                return null;
            }

            return str_starts_with($this->image_path, 'images/')
                ? asset($this->image_path)
                : Storage::disk(config('vmnewswire.posters.disk'))->url($this->image_path);
        });
    }

    /** Short teaser for cards: the excerpt, or the first sentences of the body. */
    protected function teaser(): Attribute
    {
        return Attribute::get(function () {
            if (filled($this->excerpt)) {
                return Str::limit(trim($this->excerpt), 150);
            }
            $text = $this->hasHtmlBody()
                ? strip_tags(str_replace('>', '> ', (string) $this->rendered_body))
                : preg_replace('/[#*_>`\[\]]+/', '', (string) $this->body);

            return Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode($text))), 150);
        });
    }

    /** True when the body was saved by the visual editor (HTML) rather than written as Markdown. */
    public function hasHtmlBody(): bool
    {
        return SafeHtml::looksLikeHtml($this->body);
    }

    /** The body as safe HTML for the public article page. */
    protected function renderedBody(): Attribute
    {
        return Attribute::get(fn () => $this->hasHtmlBody() ? SafeHtml::render($this->body) : SafeMarkdown::render($this->body));
    }

    /** The body as HTML for the admin editor (older Markdown posts are converted on the way in). */
    protected function editorHtml(): Attribute
    {
        return Attribute::get(fn () => (string) $this->rendered_body);
    }

    protected function authorName(): Attribute
    {
        return Attribute::get(fn () => $this->author ?: 'VM Newswire');
    }

    /** Estimated reading time at about 220 words per minute; null for an empty body. */
    protected function readingMinutes(): Attribute
    {
        return Attribute::get(function () {
            $words = str_word_count(strip_tags((string) $this->body));

            return $words > 0 ? max(1, (int) ceil($words / 220)) : null;
        });
    }

    protected function seoTitle(): Attribute
    {
        return Attribute::get(fn () => $this->meta_title ?: $this->title);
    }

    protected function seoDescription(): Attribute
    {
        return Attribute::get(fn () => $this->meta_description ?: Str::limit($this->teaser, 155));
    }
}
