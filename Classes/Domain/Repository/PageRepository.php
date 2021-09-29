<?php
declare(strict_types=1);

namespace Fr\MyraCloud\Domain\Repository;

use BR\Toolkit\Typo3\VersionWrapper\InstanceUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Fr\MyraCloud\Domain\DTO\Typo3\Page;
use Fr\MyraCloud\Domain\DTO\Typo3\PageInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageRepository
{
    /**
     * @param string $table
     * @return QueryBuilder
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    private function getQueryBuilder(string $table): QueryBuilder
    {
        /** @var QueryBuilder $query */
        $query = InstanceUtility::get(ConnectionPool::class)->getQueryBuilderForTable($table);
        return $query;
    }

    /**
     * @param int $pageUid
     * @return PageInterface|null
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function getPageWithUid(int $pageUid): ?PageInterface
    {
        $qb = $this->getQueryBuilder('pages');
        $qb->getRestrictions()->removeAll();
        $qb->select('p.uid', 'p.title', 'p.hidden', 'p.doktype', 'p.slug');
        $qb->from('pages', 'p');
        $qb->where(
            $qb->expr()->eq('p.uid', $qb->createNamedParameter($pageUid, ParameterType::INTEGER)),
            $qb->expr()->eq('p.deleted', $qb->createNamedParameter(0, ParameterType::INTEGER)),
            $qb->expr()->in('p.doktype', $qb->createNamedParameter([1, 4, 5], Connection::PARAM_INT_ARRAY))
        );

        $qb->orderBy('uid', 'ASC');
        $result = $qb->execute()->fetchAssociative();

        if ($result !== false) {
            $result['hidden'] = (bool)$result['hidden'];
            return new Page(...array_values($result));
        }

        return null;
    }
}