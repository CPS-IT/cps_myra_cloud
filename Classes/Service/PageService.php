<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Service;

use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageInterface;
use CPSIT\CpsMyraCloud\Domain\Repository\PageRepository;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\SingletonInterface;

readonly class PageService implements SingletonInterface
{
    /**
     * @param PageRepository $pageRepository
     */
    public function __construct(
        private PageRepository $pageRepository
    )
    {}

    /**
     * @param int $pageUid
     * @return PageInterface|null
     */
    public function getPage(int $pageUid): ?PageInterface
    {
        if ($pageUid > 0) {
            $pageData = null;
            try {
                $pageData = $this->pageRepository->getPageWithUid($pageUid);
            } catch (Exception) {} finally {
                return $pageData;
            }
        }

        return null;
    }
}
