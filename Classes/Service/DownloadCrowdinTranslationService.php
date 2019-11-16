<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Download;
use GeorgRinger\Crowdin\Info\CoreInformation;
use GeorgRinger\Crowdin\Utility\FileHandling;

class DownloadCrowdinTranslationService extends BaseService
{
    private const FINAL_DIR = '/transient/crowdin/final/';
    private const EXPORT_DIR = '/transient/crowdin/export/';
    private const DOWNLOAD_DIR = '/transient/crowdin/download/';
    private const RSYNC_DIR = '/transient/crowdin/rsync/';

    /** @var ApiCredentialsService */
    protected $apiCredentialsService;

    public function __construct()
    {
        $this->apiCredentialsService = new ApiCredentialsService();
        parent::__construct();
    }

    public function downloadPackage(string $language, string $branch = '')
    {
        $zipFile = $this->downloadFromCrowdin($language, $branch);
        $downloadTarget = $this->configurationService->getPathDownloads() . $this->configurationService->getCurrentProjectName() . '/' . $language . '/';
        $this->unzip($zipFile, $downloadTarget);
        if ($this->configurationService->isCoreProject()) {
            $this->processDownloadDirectoryCore($downloadTarget, $language, $branch);
        } else {
            $this->processDownloadDirectoryExtension($downloadTarget, $language, $branch);
        }

        $this->moveAllToRsyncDestination();
        $this->cleanup($downloadTarget);
    }

    protected function cleanup($downloadDir)
    {
        FileHandling::rmdir($downloadTarget, true);

        $exportDir = $this->configurationService->getPathExport();
        $exportDirs = FileHandling::get_dirs($exportDir);
        foreach ($exportDirs as $dir) {
            FileHandling::rmdir($exportDir . $dir, true);
        }
        $downloadDir = FileHandling::get_dirs($this->configurationService->getPathDownloads());
        foreach ($downloadDir as $dir) {
            FileHandling::rmdir($this->configurationService->getPathDownloads() . $dir, true);
        }
    }

    protected function moveAllToRsyncDestination()
    {
        $exportPath = $this->configurationService->getPathFinal();
        $allPackages = FileHandling::getFilesInDir($exportPath, 'zip', true);

        foreach ($allPackages as $package) {
            $info = pathinfo($package);
            $split = explode('-', $info['basename']);
            $extensionName = $split[0];

            $projectSubDir = $this->configurationService->getPathRsync() . sprintf('%s/%s/%s-l10n/', $extensionName{0}, $extensionName{1}, $extensionName);
            FileHandling::mkdir_deep($projectSubDir);
            rename($package, $projectSubDir . $info['basename']);
        }
    }

    protected function processDownloadDirectoryCore(string $directory, $language, $branch)
    {
        $sysExtDir = $directory . $branch . '/typo3/sysext/';
        $sysExtList = FileHandling::get_dirs($sysExtDir);
        if (!is_array($sysExtList) || empty($sysExtList)) {
            throw new \RuntimeException(sprintf('No sysext founds in: %s', $sysExtDir), 1566422270);
        }

        $exportPath = $this->configurationService->getPathFinal();
        FileHandling::mkdir_deep($exportPath);

        foreach ($sysExtList as $extensionKey) {
            $source = $sysExtDir . $extensionKey;
            if (in_array($extensionKey, CoreInformation::getAllCoreExtensionKeys(), true)) {
                $zipPath = $exportPath . sprintf('%s-l10n-%s.v%s.zip', $extensionKey, $language, CoreInformation::getVersionForBranchName($branch));
            } else {
                $zipPath = $exportPath . sprintf('%s-l10n-%s.zip', $extensionKey, $language);
            }

            $result = $this->zipDir($source, $zipPath, $extensionKey);
        }
    }

    protected function processDownloadDirectoryExtension(string $directory, $language, $branch)
    {
        $project = $this->configurationService->getProject();
        $dir = $directory . $branch;
        $extensionKey = $project->getExtensionkey();


        $exportPath = $this->configurationService->getPathFinal();
        FileHandling::mkdir_deep($exportPath);

        $source = $dir;
        $zipPath = $exportPath . sprintf('%s-l10n-%s.zip', $extensionKey, $language);
        $result = $this->zipDir($source, $zipPath, $extensionKey);
    }

    protected function  zipDir($source, $destination, $prefix = '')
    {
        if (!empty($prefix)) {
            $prefix = trim($prefix, '/') . '/';
        }
        $zip = new \ZipArchive();
        $zip->addEmptyDir($prefix);
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir($prefix . str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString($prefix . str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString($prefix . basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    protected function unzip(string $file, string $path)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Could not extract zip "%s"', $file), 1566421924);
        }
    }

    /**
     * @param string $langage
     * @param string $branch
     */
    protected function downloadFromCrowdin(string $langage, string $branch = ''): string
    {
        $fileName = sprintf('%s.zip', $langage);

        $path = $this->configurationService->getPathExport();
        FileHandling::mkdir_deep($path);

        $downloadName = $path . $fileName;
        $finalName = $path . $this->apiCredentialsService->getCurrentProjectName() . '-' . $fileName;

        if (!is_file($finalName)) {
            /** @var Download $api */
            $api = $this->client->api('download');

            $api->setPackage($fileName);
            if ($branch) {
//                $api->setBranch($branch);
            }
            $api->setCopyDestination($path);
            $api->execute();

            rename($downloadName, $finalName);
        }

        return $finalName;
    }
}
