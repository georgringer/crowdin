<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Download;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DownloadCrowdinTranslationService extends AbstractService
{
    public function downloadPackage(string $language, string $branch)
    {
        $zipFile = $this->downloadFromCrowdin($language, $branch);

        $downloadTarget = Environment::getVarPath() . '/transient/crowdin/export/' . $language . '/';
        $this->unzip($zipFile, $downloadTarget);

        $this->processDownloadDirectory($downloadTarget, $language, $branch);
    }

    protected function processDownloadDirectory(string $directory, $language, $branch)
    {
        $sysExtDir = $directory . 'typo3/sysext/';

        $sysExtList = GeneralUtility::get_dirs($sysExtDir);
        if (!is_array($sysExtList) || empty($sysExtList)) {
            throw new \RuntimeException(sprintf('No sysext founds in: %s', $sysExtDir), 1566422270);
        }

        $exportPath = Environment::getVarPath() . '/transient/crowdin/final/';
        GeneralUtility::mkdir_deep($exportPath);

        foreach ($sysExtList as $extensionKey) {
            $source = $sysExtDir . $extensionKey;
            $zipPath = $exportPath . sprintf('v9-%s-l10n-%s.zip', $extensionKey, $language);

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

        $path = Environment::getVarPath() . '/transient/crowdin/export/';
        GeneralUtility::mkdir_deep($path);

        $finalName = $path . $fileName;

        if (!is_file($finalName)) {
            /** @var Download $api */
            $api = $this->client->api('download');

            $api->setPackage($fileName);
            $api->setBranch($branch);
            $api->setCopyDestination($path);
            $api->execute();
        }
        return $finalName;
    }
}
