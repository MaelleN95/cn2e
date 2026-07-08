<?php

namespace App\Twig;

use App\Service\VideoEmbedGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VideoExtension extends AbstractExtension
{
    public function __construct(
        private VideoEmbedGenerator $generator
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'video_embed',
                [$this, 'generate'],
                [
                    'is_safe' => ['html']
                ]
            ),
        ];
    }

    public function generate(?string $url): string
    {
        if (!$url) {
            return '';
        }

        $embedUrl = $this->generator->generate($url);

        if (!$embedUrl) {
            return '';
        }

        return sprintf(
            '<iframe
                class="h-full w-full"
                src="%s"
                title="Lecteur vidéo"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen>
            </iframe>',
            htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8')
        );
    }
}