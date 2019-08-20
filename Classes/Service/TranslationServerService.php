<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Crowdin\Api\Info;
use Crowdin\Api\UploadTranslation;
use GuzzleHttp\Client;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TranslationServerService extends AbstractService
{

    public const CORE_EXTENSIONS = ['about', 'adminpanel', 'belog', 'core', 'felogin', 'fluid', 'frontend', 'fluid_styled_content'];

    public function getTranslation(string $key, string $language, int $version = 0)
    {
        $isSysext = false;
        if (in_array($key, self::CORE_EXTENSIONS, true)) {
            $isSysext = true;
            $url = $this->getCoreExtensionUrl($key, $language, $version);
            $filePath = $this->downloadPackage($url, $key, $language, $version);
            $absoluteLanguagePath = Environment::getVarPath() . '/transient/crowdin/v' . $version . '-' . $key . '-l10n-' . $language . '/';

        } else {
            $url = $this->getExtensionUrl($key, $language);
            $filePath = $this->downloadPackage($url, $key, $language);
            $absoluteLanguagePath = Environment::getVarPath() . '/transient/crowdin/' . $key . '-l10n-' . $language . '/';
        }

        $this->unzipTranslationFile($filePath, $absoluteLanguagePath);
        $this->processFiles($absoluteLanguagePath);
        $this->upload($absoluteLanguagePath, $language, $isSysext);
    }

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
                    $key = str_replace($absoluteLanguagePath, 'typo3/sysext/', $key);
                }
                $key = '/master/' . $key;
                $finalFiles[$key] = $translation;
            }
        }
#

        $post_params = [];
        $request_url = 'https://api.crowdin.com/api/project/typo3-cms/upload-translation?key=bc0fd8047b2e10ec18e9f8ac4f92b995';

        foreach ($finalFiles as $r => $l) {
            $post_params['files[' . $r . ']'] = curl_file_create($l);
        }

$post_params['language'] = $language;
$post_params['import_eq_suggestions'] = 0;
$post_params['auto_approve_imported'] = 1;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);

$result = curl_exec($ch);
curl_close($ch);

print_R($finalFiles);
die;
if (!empty($finalFiles)) {
    /** @var UploadTranslation $api */
    $api = $this->client->api('upload-translation');
    $api->setLocale($language);
    $api->setImportsAutoApproved(true);

    foreach ($finalFiles as $crowdinFile => $localFile) {
        $api->addTranslation($crowdinFile, $localFile);
    }
    $result = $api->execute();
    print_r($result);
}
}

protected
function processFiles(string $absolutePathToFile): void
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

protected
function downloadPackage(string $url, string $key, string $language, int $version = 0): string
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

protected
function getRemoteContent(string $url): string
{
    $httpOptions = $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
    $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
//        $httpOptions['timeout'] = 500.0;


    $client = GeneralUtility::makeInstance(Client::class, $httpOptions);
    $response = $client->request('get', $url, ['force_ip_resolve' => 'v4']);

    if ($response->getStatusCode() !== 200) {
        throw new \UnexpectedValueException('Download failed', 1566267706);
    }
    return $response->getBody()->getContents();
}

protected
function getExtensionUrl(string $key, string $language)
{
    return sprintf('https://typo3.org/fileadmin/ter/%s/%s/%s-l10n/%s-l10n-%s.zip',
        $key{0},
        $key{1},
        $key,
        $key,
        $language
    );
}

protected
function getCoreExtensionUrl(string $key, string $language, int $version)
{
    return sprintf('https://typo3.org/fileadmin/ter/%s/%s/%s-l10n/%s-l10n-%s.v%s.zip',
        $key{0},
        $key{1},
        $key,
        $key,
        $language,
        $version
    );
}

/**
 * Unzip an language zip file
 *
 * @param string $file path to zip file
 * @param string $path path to extract to
 * @throws \RuntimeException
 */
protected
function unzipTranslationFile(string $file, string $path)
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
