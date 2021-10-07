<?php
declare(strict_types=1);

namespace CPSIT\CpsMyraCloud\FileList;

use CPSIT\CpsMyraCloud\AdapterProvider\ExternalCacheProvider;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileAdmin;
use CPSIT\CpsMyraCloud\Domain\Enum\Typo3CacheType;
use CPSIT\CpsMyraCloud\Domain\Repository\FileRepository;
use CPSIT\CpsMyraCloud\Service\ExternalCacheService;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtilityProcessDataHookInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use CPSIT\CpsMyraCloud\Domain\DTO\Typo3\File\FileInterface as MyraFileInterface;

class FileListHook implements ExtendedFileUtilityProcessDataHookInterface, SingletonInterface
{
    private ExternalCacheService $externalCacheService;
    private FileRepository $fileRepository;
    private array $pageAlreadyCleared = [];

    /**
     * @param ExternalCacheService $externalCacheService
     * @param FileRepository $fileRepository
     */
    public function __construct(ExternalCacheService $externalCacheService, FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->externalCacheService = $externalCacheService;
    }

    /**
     * @param string $action
     * @param array $cmdArr
     * @param array $result
     * @param ExtendedFileUtility $parentObject
     */
    public function processData_postProcessAction($action, array $cmdArr, array $result, ExtendedFileUtility $parentObject)
    {
        if ($action === 'upload' && $parentObject->getExistingFilesConflictMode() === DuplicationBehavior::REPLACE) {
            $provider = ExternalCacheProvider::getDefaultProviderItem();
            if ($provider && $provider->canAutomated()) {
                $this->clearCacheForFileGroups($result);
            }
        }
    }

    /**
     * @param array $groups
     */
    private function clearCacheForFileGroups(array $groups): void
    {
        foreach ($groups as $group) {
            $this->clearCacheForFiles($group);
        }
    }

    /**
     * @param array $files
     */
    private function clearCacheForFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof FileInterface) {
                $this->clearCacheForFile($file);
            }
        }
    }

    /**
     * @param FileInterface $file
     */
    private function clearCacheForFile(FileInterface $file): void
    {
        $path = $file->getIdentifier();
        $files = $this->getProcessedFiles($file);
        $files[] = new FileAdmin($path);

        foreach ($files as $toClearFile) {
            $this->clearMyraFile($toClearFile);
        }
    }

    /**
     * @param FileInterface $file
     * @return array
     */
    private function getProcessedFiles(FileInterface $file): array
    {
        try {
            $files = $this->fileRepository->getProcessedFilesFromFile($file);
        } catch (\Exception | Exception $e) {
            $files = [];
        }

        return $files;
    }

    /**
     * @param MyraFileInterface $file
     */
    private function clearMyraFile(MyraFileInterface $file): void
    {
        // TODO: add other storages here not only (1:)
        $path = '1:/' . ltrim($file->getRawSlug(), '/');
        $crc = crc32($path);
        if (!($this->pageAlreadyCleared[$crc]??false)) {
            try {
                $this->pageAlreadyCleared[$crc] = $this->externalCacheService->clear(Typo3CacheType::RESOURCE, $path);
            } catch (\Exception $_) {
                $this->pageAlreadyCleared[$crc] = false;
            }
        }
    }
}