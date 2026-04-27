<?php

namespace App\Support;

use Illuminate\Support\Str;

class PhotoUrl
{
    public static function resolve(?string $value): string
    {
        $fallback = asset('assets/img/blank-profile.webp');
        $photo = trim((string) $value);

        if ($photo === '') {
            return $fallback;
        }

        if (Str::startsWith($photo, ['http://', 'https://'])) {
            return $photo;
        }

        $localCandidates = [
            'storage/foto_users/' . $photo,
            'uploads/foto_users/' . $photo,
            'foto_users/' . $photo,
            ltrim($photo, '/'),
        ];

        foreach ($localCandidates as $candidate) {
            if (file_exists(public_path($candidate))) {
                return asset($candidate);
            }
        }

        $cloudUrl = (string) (config('cloudinary.cloud_url') ?: env('CLOUDINARY_URL'));
        $cloudName = parse_url($cloudUrl, PHP_URL_HOST);

        if (!empty($cloudName)) {
            $publicId = ltrim($photo, '/');
            return "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
        }

        return $fallback;
    }
}
