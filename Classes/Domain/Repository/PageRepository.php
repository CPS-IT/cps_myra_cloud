<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\Page;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\PageInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;

readonly class PageRepository implements SingletonInterface
{
    public function __construct(
        private ConnectionPool $connectionPool
    )
    {}

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable('pages');
    }

    /**
     * @param int $pageUid
     * @return PageInterface|null
     * @throws Exception
     */
    public function getPageWithUid(int $pageUid): ?PageInterface
    {
        $qb = $this->getQueryBuilder();
        $qb->getRestrictions()->removeAll();
        $qb->select('p.uid', 'p.title', 'p.hidden', 'p.doktype', 'p.slug');
        $qb->from('pages', 'p');
        $qb->where(
            $qb->expr()->eq('p.uid', $qb->createNamedParameter($pageUid, ParameterType::INTEGER)),
            $qb->expr()->eq('p.deleted', $qb->createNamedParameter(0, ParameterType::INTEGER)),
            $qb->expr()->in('p.doktype', $qb->createNamedParameter([1, 4, 5], ArrayParameterType::INTEGER))
        );

        $qb->orderBy('uid', 'ASC');
        $result = $qb->executeQuery()->fetchAssociative();

        if ($result !== false) {
            return new Page(
                uid: (int)$result['uid'],
                title: $result['title'],
                hidden: (bool)$result['hidden'],
                dokType: (int)$result['doktype'],
                slug: $result['slug']
            );
        }

        return null;
    }
}
