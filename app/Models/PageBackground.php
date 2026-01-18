<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageBackground extends Model
{
    protected $fillable = [
        'page_key',
        'path',
    ];

    public static function url(string $pageKey, string $defaultAssetPath): string
    {
        $pagePath = static::query()->where('page_key', $pageKey)->value('path');
        $globalPath = static::query()->where('page_key', 'global')->value('path');

        $path = $pagePath ?: $globalPath;
        if (is_string($path) && $path !== '') {
            // IMPORTANT: use relative URL to respect current host/port (avoids APP_URL mismatch)
            return '/storage/'.ltrim($path, '/');
        }

        // Default assets live in /public; keep them relative as well.
        return '/'.ltrim($defaultAssetPath, '/');
    }
}
