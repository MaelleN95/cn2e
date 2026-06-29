<?php

namespace App\Twig;

use App\Repository\SiteInformationRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class SiteInformationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private SiteInformationRepository $siteInformationRepository,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'siteInformation' => $this->siteInformationRepository->getSingleton(),
        ];
    }
}