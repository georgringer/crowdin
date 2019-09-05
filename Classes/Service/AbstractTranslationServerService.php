<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\UploadTranslation;
use GuzzleHttp\Client;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class AbstractTranslationServerService extends AbstractService
{
    public function upload($absoluteLanguagePath, string $language, bool $isSystemExtension, string $targetBranch)
    {
        $translations = GeneralUtility::getAllFilesAndFoldersInPath([],
            $absoluteLanguagePath, 'xlf',
            true,
            5);

        $finalFiles = [];
        foreach ($translations as $translation) {
            if (is_dir($translation)) {
                continue;
            }
            if (is_file($translation)) {
                $fileInfo = pathinfo($translation);
                if (!StringUtility::beginsWith($fileInfo['filename'], $language . '.')) {
                    continue;
                }
                $key = str_replace('/' . $language . '.', '/', $translation);
                if ($isSystemExtension) {
                    $originalFile = str_replace($absoluteLanguagePath, Environment::getBackendPath() . '/sysext/', $key);
                    $key = str_replace($absoluteLanguagePath, 'typo3/sysext/', $key);

                    if (!is_file($originalFile)) {
//                        continue;
                    }
                }
                $key = sprintf('/%s/', $targetBranch) . $key;
                $finalFiles[$key] = $translation;
            }
        }
        if (!empty($finalFiles)) {
            $chunks = array_chunk($finalFiles, 15, true);

            foreach ($chunks as $chunk) {
                /** @var UploadTranslation $api */
                $api = $this->client->api('upload-translation');
                $api->setLocale($language);
                $api->setEqualSuggestionsImported(true);
                $api->setImportsAutoApproved(true);

                foreach ($chunk as $crowdinFile => $localFile) {
                    $api->addTranslation($localFile, $crowdinFile);
                }
                $result = $api->execute();
            }
        }
    }

    protected function processFiles(string $absolutePathToFile): void
    {
        $deprecatedFiles = GeneralUtility::getAllFilesAndFoldersInPath([],
            $absolutePathToFile, '',
            true,
            5,
            '.*(xlf)');

        foreach ($deprecatedFiles as $result) {
            if (is_file($result)) {
                unlink($result);
            }
        }
    }

    protected function downloadPackage(string $url, string $key, string $language, int $version = 0): string
    {
        $versionString = $version > 0 ? ('v' . $version . '-') : '';
        $absolutePathToZipFile = Environment::getVarPath() . '/transient/download/' . $versionString . $key . '-l10n-' . $language . '.zip';
        if (!is_file($absolutePathToZipFile)) {
            GeneralUtility::mkdir_deep(Environment::getVarPath() . '/transient/download/');
            $languagePackContent = $this->getRemoteContent($url);
            if (!$languagePackContent) {
                throw new \UnexpectedValueException(sprintf('Error while downloading language pack: %s', $url), 1566269621);
            }
            $operationResult = GeneralUtility::writeFileToTypo3tempDir($absolutePathToZipFile, $languagePackContent) === null;
            if ($operationResult && $operationResult != 1) {
                throw new \UnexpectedValueException(sprintf('Error extracting language pack: %s, url: %s', $operationResult, $url), 1566266319);
            }
        }
        return $absolutePathToZipFile;
    }

    protected function getRemoteContent(string $url): string
    {
        $httpOptions = $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
        $client = GeneralUtility::makeInstance(Client::class);
        $response = $client->request('get', $url, ['force_ip_resolve' => 'v4']);
        if ($response->getStatusCode() !== 200) {
            throw new \UnexpectedValueException('Download failed', 1566267706);
        }

        return $response->getBody()->getContents();
    }
}
