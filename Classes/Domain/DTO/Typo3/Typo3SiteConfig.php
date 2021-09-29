<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Typo3;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class Typo3SiteConfig implements SiteConfigInterface
{
    private SiteInterface $site;

    /**
     * @param SiteInterface $site
     */
    public function __construct(SiteInterface $site)
    {
        $this->site = $site;
    }

    public function getSite(): SiteInterface
    {
        return $this->site;
    }

    /**
     * @return string
     */
    public function getExternalIdentifier(): string
    {
        $site = $this->getSite();
        if (method_exists($site, 'getConfiguration'))
            return $site->getConfiguration()['myra_host']??'';

        return '';
    }
}