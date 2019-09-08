<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Download;
use GeorgRinger\Crowdin\Info\CoreInformation;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $this->apiCredentialsService = GeneralUtility::makeInstance(ApiCredentialsService::class);
        parent::__construct();
    }

    public function downloadPackage(string $language, string $branch, bool $copyToL10n = false)
    {
        $zipFile = $this->downloadFromCrowdin($language, $branch);

        $downloadTarget = Environment::getVarPath() . self::DOWNLOAD_DIR . $language . '/';
        $this->unzip($zipFile, $downloadTarget);

        $this->processDownloadDirectory($downloadTarget, $language, $branch);

        $this->moveAllToRsyncDestination();
        if ($copyToL10n) {
            $this->copyToL10nDir($language);
        }
        $this->cleanup($downloadTarget);
    }

    /**
     * Copy all translations to current l10n directory
     *
     * @param string $language
     */
    protected function copyToL10nDir(string $language): void
    {
        $path = Environment::getVarPath() . self::DOWNLOAD_DIR . $language . '/typo3/sysext/';

        $coreExtensions = GeneralUtility::get_dirs($path);
        $targetDir = Environment::getLabelsPath() . '/' . $language . '/';
        GeneralUtility::mkdir_deep($targetDir);

        foreach ($coreExtensions as $coreExtension) {
            GeneralUtility::copyDirectory($path . $coreExtension, $targetDir . $coreExtension);
        }
    }

    protected function cleanup($downloadDir)
    {
        GeneralUtility::rmdir($downloadTarget, true);

        $exportDirs = GeneralUtility::get_dirs(Environment::getVarPath() . self::EXPORT_DIR);
        foreach ($exportDirs as $dir) {
            GeneralUtility::rmdir(Environment::getVarPath() . self::EXPORT_DIR . $dir, true);
        }
        $downloadDir = GeneralUtility::get_dirs(Environment::getVarPath() . self::DOWNLOAD_DIR);
        foreach ($downloadDir as $dir) {
            GeneralUtility::rmdir(Environment::getVarPath() . self::DOWNLOAD_DIR . $dir, true);
        }
    }

    protected function moveAllToRsyncDestination()
    {
        $exportPath = Environment::getVarPath() . self::FINAL_DIR;
        $allPackages = GeneralUtility::getFilesInDir($exportPath, 'zip', true);

        foreach ($allPackages as $package) {
            $info = pathinfo($package);
            $split = explode('-', $info['basename']);
            $extensionName = $split[0];

            $projectSubDir = Environment::getVarPath() . self::RSYNC_DIR . sprintf('%s/%s/%s-l10n/', $extensionName{0}, $extensionName{1}, $extensionName);
            GeneralUtility::mkdir_deep($projectSubDir);
            rename($package, $projectSubDir . $info['basename']);
        }
    }

    protected function processDownloadDirectory(string $directory, $language, $branch)
    {
        $sysExtDir = $directory . 'typo3/sysext/';

        $sysExtList = GeneralUtility::get_dirs($sysExtDir);
        if (!is_array($sysExtList) || empty($sysExtList)) {
            throw new \RuntimeException(sprintf('No sysext founds in: %s', $sysExtDir), 1566422270);
        }

        $exportPath = Environment::getVarPath() . self::FINAL_DIR;
        GeneralUtility::mkdir_deep($exportPath);

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

    protected function zipDir($source, $destination, $prefix = '')
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
    protected function downloadFromCrowdin(string $langage, string $branch): string
    {
        $fileName = sprintf('%s.zip', $langage);

        $path = Environment::getVarPath() . self::EXPORT_DIR;
        GeneralUtility::mkdir_deep($path);

        $downloadName = $path . $fileName;
        $finalName = $path . $this->apiCredentialsService->getCurrentProjectName() . '-' . $fileName;

        if (!is_file($finalName)) {
            /** @var Download $api */
            $api = $this->client->api('download');

            $api->setPackage($fileName);
            $api->setBranch($branch);
            $api->setCopyDestination($path);
            $api->execute();

            rename($downloadName, $finalName);
        }

        return $finalName;
    }
}
