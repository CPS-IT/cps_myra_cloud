<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\DTO\Typo3;

use CPSIT\CpsMyraCloud\Traits\DomainListParserTrait;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class Typo3SiteConfig implements SiteConfigInterface
{
    use DomainListParserTrait;

    private array $myraDomainList;

    /**
     * @param SiteInterface $site
     */
    public function __construct(SiteInterface $site)
    {
        $domainList = [];
        if (method_exists($site, 'getConfiguration')) {
            $domainListString = $site->getConfiguration()['myra_host']??'';
            $domainList = $this->parseCommaList($domainListString);
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