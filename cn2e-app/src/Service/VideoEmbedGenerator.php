<?php

namespace App\Service;

class VideoEmbedGenerator
{
    private const PROVIDERS = [
        'youtube.com',
        'youtu.be',
        'dailymotion.com',
        'vimeo.com',
    ];

    public function generate(string $url): ?string
    {
        if (!$this->isAllowedUrl($url)) {
            return null;
        }

        $embedUrl = $this->getEmbedUrl($url);

        if (!$embedUrl) {
            return null;
        }

        return $embedUrl;
    }

    private function isAllowedUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return false;
        }

        foreach (self::PROVIDERS as $provider) {
            if ($host === $provider || str_ends_with($host, '.' . $provider)) {
                return true;
            }
        }

        return false;
    }

    private function getEmbedUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return match (true) {
            str_contains($host, 'youtube.com') => $this->youtube($url),
            str_contains($host, 'youtu.be') => $this->youtubeShared($url),
            str_contains($host, 'dailymotion.com') => $this->dailymotion($url),
            str_contains($host, 'vimeo.com') => $this->vimeo($url),
            default => null,
        };
    }

    private function youtube(string $url): ?string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        if (!isset($params['v'])) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $params['v'];
    }

    private function youtubeShared(string $url): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');

        if (!$path) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $path;
    }

    private function dailymotion(string $url): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');

        $parts = explode('/', $path);

        if (!isset($parts[1])) {
            return null;
        }

        return 'https://www.dailymotion.com/embed/video/' . $parts[1];
    }

    private function vimeo(string $url): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');

        if (!$path || !is_numeric($path)) {
            return null;
        }

        return 'https://player.vimeo.com/video/' . $path;
    }
}