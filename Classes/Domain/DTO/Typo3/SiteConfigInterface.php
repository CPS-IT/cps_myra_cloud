<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\DTO\Typo3;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

interface SiteConfigInterface extends SiteConfigExternalIdentifierInterface
{
    public function getSite(): SiteInterface;

    //public function getFullyNamed(): array;
}