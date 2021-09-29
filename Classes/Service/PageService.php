<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Service;

use BR\Toolkit\Typo3\Cache\CacheService;
use Fr\MyraCloud\Domain\DTO\Typo3\PageInterface;
use Fr\MyraCloud\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;

class PageService implements SingletonInterface
{
    private PageRepository $pageRepository;
    private CacheService $cacheService;

    /**
     * @param PageRepository $pageRepository
     * @param CacheService $cacheService
     */
    public function __construct(PageRepository $pageRepository, CacheService $cacheService)
    {
        $this->pageRepository = $pageRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * @param int $pageUid
     * @return PageInterface|null
     */
    public function getPage(int $pageUid): ?PageInterface
    {
        if ($pageUid > 0) {
            try {
                return $this->cacheService->cache(
                    'PageService_page_uid_' . $pageUid,
                    fn(): ?PageInterface => $this->pageRepository->getPageWithUid($pageUid),
                    'PAGE_CONTEXT',
                    0
                );
            } catch (\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            }
        }

        return null;
    }
}