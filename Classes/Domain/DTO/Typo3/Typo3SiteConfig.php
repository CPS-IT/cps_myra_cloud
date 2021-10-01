<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Typo3;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class Typo3SiteConfig implements SiteConfigInterface
{
    private array $myraDomainList;

    /**
     * @param SiteInterface $site
     */
    public function __construct(SiteInterface $site)
    {
        $domainList = [];
        if (method_exists($site, 'getConfiguration')) {
            $domainListString = $site->getConfiguration()['myra_host']??'';
            $rawList = explode(',', str_replace(' ', '', $domainListString));
            $domainList = array_unique(array_filter($rawList?:[]));
        }

        $this->myraDomainList = $domainList;
    }

    /**
     * @return array
     */
    public function getExternalIdentifierList(): array
    {
        return $this->myraDomainList;
    }
}