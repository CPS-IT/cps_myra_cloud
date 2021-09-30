<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Typo3;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class Typo3SiteConfig implements SiteConfigInterface
{
    private string $externalId = '';

    /**
     * @param SiteInterface $site
     */
    public function __construct(SiteInterface $site)
    {
        if (method_exists($site, 'getConfiguration'))
            $this->externalId = $site->getConfiguration()['myra_host']??'';
    }

    /**
     * @return string
     */
    public function getExternalIdentifier(): string
    {
        return $this->externalId;
    }
}