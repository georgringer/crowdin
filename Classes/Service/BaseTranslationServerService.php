<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\UploadTranslation;
use GeorgRinger\Crowdin\Utility\FileHandling;
use GuzzleHttp\Client;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class BaseTranslationServerService extends BaseService
{
    public const IGNORED_PATHS = [
        '/master/typo3/sysext/beuser/mod/',
        '/master/typo3/sysext/belog/mod/',
        '/master/typo3/sysext/linkvalidator/modfuncreport/',
        '/master/typo3/sysext/reports/mod/',
        '/master/typo3/sysext/form/Tests/',
        '/master/typo3/sysext/impexp/modfunc1/',
        '/master/typo3/sysext/install/mod/',
        '/master/typo3/sysext/install/report/',
        '/master/typo3/sysext/opendocs/',
        '/master/typo3/sysext/redirects/Resources/Private/Language/locallang_reports.xlf', // @todo remove when master is fetched again
        '/master/typo3/sysext/viewpage/view/'
    ];

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
                $splittedFileName = explode('.', $fileInfo['filename']);
                $last = end($splittedFileName);
                // skip bogus files like pl.locallang.1415814894.xlf
                if (is_numeric($last)) {
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

                $skipped = false;
                // skip files which don't exist anymore but are still exported
                foreach (self::IGNORED_PATHS as $ignoredPath) {
                    if (FileHandling::beginsWith($key, $ignoredPath)) {
                        $skipped = true;
                    }
                }

                if ($skipped) {
                    continue;
                }

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

//                try {
                foreach ($chunk as $crowdinFile => $localFile) {
                    $api->addTranslation($localFile, $crowdinFile);
                }
                $result = $api->execute();
//                } catch (\Exception $e) {
//                    print_r($chunk);
//                }
            }
        }
    }

    ///app/web/typo3temp/var/transient/crowdin/v10-linkvalidator-l10n-pl/linkvalidator/modfuncreport/pl.locallang.xlf
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
        $client = new Client();
        $response = $client->request('get', $url, ['force_ip_resolve' => 'v4']);
        if ($response->getStatusCode() !== 200) {
            throw new \UnexpectedValueException('Download failed', 1566267706);
        }

        return $response->getBody()->getContents();
    }
}
