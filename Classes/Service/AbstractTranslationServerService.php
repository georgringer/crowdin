<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\UploadTranslation;
use GuzzleHttp\Client;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractTranslationServerService extends AbstractService
{

    public const CORE_EXTENSIONS = ['about', 'adminpanel',
        'backend', 'belog', 'core', 'extbase', 'extensionmanager', 'felogin', 'filelist',
        'filemetadata', 'fluid', 'frontend', 'fluid_styled_content', 'form', 'frontend', 'impexp',
        'indexed_search', 'info', 'install', 'linkvalidator', 'lowlevel', 'opendocs', 'recordlist',
        'recycler', 'redirects', 'reports', 'rte_ckeditor', 'scheduler', 'seo', 'setup', 'sys_note', 't3editor',
        'tstemplate', 'viewpage', 'workspaces',
    ];
    public const OLD_CORE_EXTENSIONS = [
        'sys_action', 'taskcenter' // removed in 10
    ];


    public function upload($absoluteLanguagePath, string $language, bool $isSystemExtension)
    {
        $translations = GeneralUtility::getAllFilesAndFoldersInPath([],
            $absoluteLanguagePath, 'xlf',
            true,
            5);

        $finalFiles = [];
        foreach ($translations as $translation) {
            if (is_file($translation)) {
                $key = str_replace('/' . $language . '.', '/', $translation);
                if ($isSystemExtension) {
                    $originalFile = str_replace($absoluteLanguagePath, Environment::getBackendPath() . '/sysext/', $key);
                    $key = str_replace($absoluteLanguagePath, 'typo3/sysext/', $key);

                    if (!is_file($originalFile)) {
                        continue;
                    }
                }
                $key = '/master/' . $key;
                $finalFiles[$key] = $translation;
            }
        }

        if (!empty($finalFiles)) {
            /** @var UploadTranslation $api */
            $api = $this->client->api('upload-translation');
            $api->setLocale($language);
            $api->setImportsAutoApproved(true);

            foreach ($finalFiles as $crowdinFile => $localFile) {
                $api->addTranslation($localFile, $crowdinFile);
            }
            $result = $api->execute();
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
        $absolutePathToZipFile = Environment::getVarPath() . '/transient/' . $versionString . $key . '-l10n-' . $language . '.zip';
        if (!is_file($absolutePathToZipFile)) {
            GeneralUtility::mkdir_deep(Environment::getVarPath() . '/transient/');
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


        $client = GeneralUtility::makeInstance(Client::class, $httpOptions);
        $response = $client->request('get', $url, ['force_ip_resolve' => 'v4']);

        if ($response->getStatusCode() !== 200) {
            throw new \UnexpectedValueException('Download failed', 1566267706);
        }
        return $response->getBody()->getContents();
    }

    /**
     * Unzip an language zip file
     *
     * @param string $file path to zip file
     * @param string $path path to extract to
     * @throws \RuntimeException
     */
    protected function unzipTranslationFile(string $file, string $path)
    {
        $zip = zip_open($file);
        if (is_resource($zip)) {
            if (!is_dir($path)) {
                GeneralUtility::mkdir_deep($path);
            }
            while (($zipEntry = zip_read($zip)) !== false) {
                $zipEntryName = zip_entry_name($zipEntry);
//                print_r($zipEntryName)
                if (strpos($zipEntryName, '/') !== false) {
                    $zipEntryPathSegments = explode('/', $zipEntryName);
                    $fileName = array_pop($zipEntryPathSegments);
                    // It is a folder, because the last segment is empty, let's create it
                    if (empty($fileName)) {
                        GeneralUtility::mkdir_deep($path . implode('/', $zipEntryPathSegments));
                    } else {
                        $absoluteTargetPath = GeneralUtility::getFileAbsFileName($path . implode('/', $zipEntryPathSegments) . '/' . $fileName);
                        if (trim($absoluteTargetPath) !== '') {
                            $return = GeneralUtility::writeFile(
                                $absoluteTargetPath,
                                zip_entry_read($zipEntry, zip_entry_filesize($zipEntry))
                            );
                            if ($return === false) {
                                throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170845);
                            }
                        } else {
                            throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170846);
                        }
                    }
                } else {
                    throw new \RuntimeException('Extension directory missing in zip file!', 1520170847);
                }
            }
        } else {
            throw new \RuntimeException('Unable to open zip file ' . $file, 1520170848);
        }
    }

}
