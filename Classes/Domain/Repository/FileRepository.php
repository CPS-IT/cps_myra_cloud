<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\Repository;

use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileAdmin;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\SingletonInterface;

readonly class FileRepository implements SingletonInterface
{
    public function __construct(
        private ConnectionPool $connectionPool
    )
    {}

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable('sys_file_processedfile');
    }

    /**
     * @param FileInterface $file
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getProcessedFilesFromFile(FileInterface $file): array
    {
        $uid = (int)$file->getProperty('uid');
        if ($uid <= 0) {
            return [];
        }

        $qb = $this->getQueryBuilder();
        $qb->select('identifier');
        $qb->from('sys_file_processedfile');
        $qb->where(
            $qb->expr()->eq('original', $qb->createNamedParameter($uid, ParameterType::INTEGER))
        );

        $files = [];
        foreach ($qb->executeQuery()->fetchAllAssociative() as $row) {
            if (!empty($row['identifier'])) {
                $files[] = new FileAdmin($row['identifier']);
            }
        }

        return $files;
    }
}
