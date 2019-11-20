<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\UploadTranslation;
use GeorgRinger\Crowdin\Info\LanguageInformation;
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
//        '/master/typo3/sysext/redirects/Resources/Private/Language/locallang_reports.xlf', // @todo remove when master is fetched again
        '/master/typo3/sysext/viewpage/view/',
        '/9.5/typo3/sysext/reports/mod/locallang.xlf',
        '/9.5/typo3/sysext/viewpage/view/locallang_mod.xlf',
        '/9.5/typo3/sysext/linkvalidator/modfuncreport/',
        '/9.5/typo3/sysext/opendocs/mod/locallang_mod.xlf',
        '/9.5/typo3/sysext/install/mod/locallang_mod.xlf',
        '/9.5/typo3/sysext/install/report/locallang.xlf',
        '/9.5/typo3/sysext/form/Tests/',
        '/9.5/typo3/sysext/impexp/modfunc1/',
        '/9.5/typo3/sysext/belog/mod/',
        '/9.5/typo3/sysext/beuser/mod/',
        '/master/typo3/sysext/indexed_search/pi/',
        '/master/typo3/sysext/indexed_search/modfunc2/', // ru
        '/master/typo3/sysext/indexed_search/modfunc1/',
        '/master/typo3/sysext/indexed_search/mod/',

        '/9.5/typo3/sysext/indexed_search/pi/',
        '/9.5/typo3/sysext/indexed_search/modfunc2/', // ru
        '/9.5/typo3/sysext/indexed_search/modfunc1/',
        '/9.5/typo3/sysext/indexed_search/mod/',
    ];

    public const SKIPPED_MAPPINGS = [
        '1415814894' => 'File sv.locallang.1415814894 for language sv, branch 9.5',
        '1415814893' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814895' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814896' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814897' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814898' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814899' => 'File sv.locallang.1415814893 for language sv, branch 9.5',
        '1415814900' => 'lowlevel sv',
        '1415815011' => 'view sv',
        '1415815012' => 'view sv',
        '1415814901' => 'opendocs lv',
        'v9-reports-l10n-pt_BR' => '1',
        'v9-reports-l10n-lv' => 1,
        'v9-scheduler-l10n-pt_BR' => 1,
        'v10-reports-l10n-pt_BR' => 1,
        'v10-reports-l10n-nl' => 1,
        'v10-scheduler-l10n-nl' => 1,
        'v10-scheduler-l10n-nl' => 1,
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
                    if (!isset(self::SKIPPED_MAPPINGS[$last])) {
//                        var_dump($last);
                        $split = explode('/', $translation);

                        if (!isset(self::SKIPPED_MAPPINGS[$split[7]])) {
                            die(sprintf('xxxFile %s for language %s, branch %s.', $translation, $language, $targetBranch));
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                $key = str_replace('/' . $language . '.', '/', $translation);
                if ($isSystemExtension) {
                    $originalFile = str_replace($absoluteLanguagePath, Environment::getBackendPath() . '/sysext/', $key);
                    $key = str_replace($absoluteLanguagePath, 'typo3/sysext/', $key);

                    if (!is_file($originalFile)) {
//                        continue;
                    }
                } else {
                    $originalFile = str_replace($absoluteLanguagePath, Environment::getBackendPath() . '/sysext/', $key);
                    $key = str_replace($absoluteLanguagePath, '', $key);
                    $key = str_replace('/master/news/', '/', $key);
                }
                $key = sprintf('/%s/', $targetBranch) . $key;

                $key = str_replace('/master/news/', '/master/', $key);

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
        //print_r($finalFiles);die;
        if (!empty($finalFiles)) {
            $chunks = array_chunk($finalFiles, 15, true);

            $language = LanguageInformation::getLanguageForCrowdin($language);

            foreach ($chunks as $chunk) {
                /** @var UploadTranslation $api */
                $api = $this->client->api('upload-translation');
                $api->setLocale($language);
                $api->setEqualSuggestionsImported(true);
                $api->setImportsAutoApproved(true);

                try {
                    foreach ($chunk as $crowdinFile => $localFile) {
                        $api->addTranslation($localFile, $crowdinFile);
                    }
                    $result = $api->execute();
                } catch (\Exception $e) {
                    print_r($chunk);
                    echo $e->getMessage();
                }
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
        $client = new Client();
        $response = $client->request('get', $url, ['force_ip_resolve' => 'v4']);
        if ($response->getStatusCode() !== 200) {
            throw new \UnexpectedValueException('Download failed', 1566267706);
        }

        return $response->getBody()->getContents();
    }
}
