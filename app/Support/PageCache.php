<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Full-page cache for anonymous visitors on the public site.
 *
 * Shared hosting allows only a handful of simultaneous database connections, so a cached page is
 * served without touching the database at all. Entries are keyed by a version number that every
 * content change bumps (see flush()), so an edit in the admin is visible on the next request.
 */
class PageCache
{
    public const TTL_SECONDS = 600;

    private const VERSION_KEY = 'pagecache:version';

    public static function key(string $url): string
    {
        return 'page:'.self::version().':'.sha1($url);
    }

    public static function version(): int
    {
        try {
            return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
        } catch (Throwable) {
            return 1;
        }
    }

    /** Invalidate every cached page: the version changes, old entries expire by TTL. */
    public static function flush(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, self::version() + 1);
        } catch (Throwable) {
            // The site must keep working if the cache store is unavailable.
        }
    }
}
