<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\Domain\Repository;

use BR\Toolkit\Typo3\VersionWrapper\InstanceUtility;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileAdmin;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\SingletonInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileInterface as MyraFileInterface;

class FileRepository implements SingletonInterface
{
    /**
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
     * @param FileInterface $file
     * @return MyraFileInterface[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function getProcessedFilesFromFile(FileInterface $file): array
    {
        $uid = (int)$file->getProperty('uid');
        if ($uid <= 0) {
            return [];
        }

        $qb = $this->getQueryBuilder('sys_file_processedfile');
        $qb->select('identifier');
        $qb->from('sys_file_processedfile');

        $qb->where(
            $qb->expr()->eq('original', $qb->createNamedParameter($uid, ParameterType::INTEGER))
        );

        $files = [];
        foreach ($qb->execute()->fetchAllAssociative() as $row) {
            if (!empty($row['identifier'])) {
                $files[] = new FileAdmin($row['identifier']);
            }
        }

        return $files;
    }
}