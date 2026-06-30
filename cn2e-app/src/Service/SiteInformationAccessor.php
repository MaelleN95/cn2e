<?php

namespace App\Service;

use App\Repository\SiteInformationRepository;

class SiteInformationAccessor
{
    public function __construct(
        private SiteInformationRepository $siteInformationRepository,
    ) {
    }

    public function getShortName(): string
    {
        return $this->siteInformationRepository->getSingleton()->getShortName();
    }

    public function getDisplayName(): string
    {
        return $this->siteInformationRepository->getSingleton()->getDisplayName();
    }

    public function getContactEmail(): string
    {
        return $this->siteInformationRepository->getSingleton()->getContactEmail();
    }

    public function getSenderEmail(): string
    {
        $senderEmail = $this->siteInformationRepository->getSingleton()->getSenderEmail();

        if ($senderEmail !== '') {
            return $senderEmail;
        }

        return $_ENV['CONTACT_FROM'] ?? 'contact@koji-dev.fr';
    }
}