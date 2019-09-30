<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * @author Xavier Perseguers <xavier@typo3.org>
 * @author Sebastian Fischer
 */
class ConvertXmlToXlfCommand extends Command
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Convert given xml file to xlf')
            ->addArgument('file', InputArgument::REQUIRED, 'File path');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $response = $this->writeXmlAsXlfFilesInPlace($input->getArgument('file'));
            $io->success('The following files have been created:');
            foreach ($response as $item) {
                $io->text($item);
            }
        } catch (\Exception $exception) {
            $io->error('ERROR: ' . $exception->getMessage());
        }
    }

    /**
     * Function to convert llxml files
     *
     * @param string $xmlFile Absolute path to the selected ll-XML file
     * @return array feedback
     */
    protected function writeXmlAsXlfFilesInPlace(string $xmlFile): array
    {
        if (!is_file($xmlFile)) {
            throw new \RuntimeException('File ' . $xmlFile . ' does not exists!');
        }

        $languages = $this->getAvailableTranslations($xmlFile);
        $errors = [];
        foreach ($languages as $langKey) {
            $newFileName = dirname($xmlFile) . '/' . $this->localizedFileRef($xmlFile, $langKey);
            if (is_file($newFileName)) {
                throw new \RuntimeException('ERROR: Output file "' . $newFileName . '" already exists!');
            }
        }

        $output = [];
        foreach ($languages as $langKey) {
            $newFileName = dirname($xmlFile) . '/' . $this->localizedFileRef($xmlFile, $langKey);
            $output[] = $this->writeNewXliffFile($xmlFile, $newFileName, $langKey);
        }
        return $output;
    }

    /**
     * @param string $languageFile Absolute reference to the base locallang file
     * @return array
     */
    protected function getAvailableTranslations(string $languageFile): array
    {
        if (strpos($languageFile, '.xml')) {
            $ll = $this->xml2array(file_get_contents($languageFile));
            $languages = isset($ll['data']) ? array_keys($ll['data']) : [];
        }
        if (empty($languages)) {
            throw new \RuntimeException('data section not found in "' . $languageFile . '"', 1314187884);
        }
        return $languages;
    }

    /**
     * Returns localized fileRef ([langkey].locallang*.xml)
     *
     * @param string $fileRef Filename/path of a 'locallang*.xml' file
     * @param string $lang Language key
     * @return string Input filename with a '[lang-key].locallang*.xml' name if $this->lang is not 'default'
     */
    protected function localizedFileRef(string $fileRef, string $lang): string
    {
        $path = '';
        if (substr($fileRef, -4) === '.xml') {
            $lang = $lang === 'default' ? '' : $lang . '.';
            $path = $lang . pathinfo($fileRef, PATHINFO_FILENAME) . '.xlf';
        }
        return $path;
    }

    /**
     * Processing of the submitted form; Will create and write the XLIFF file and tell the new file name.
     *
     * @param string $xmlFile Absolute path to the locallang.xml file to convert
     * @param string $newFileName The new file name to write to (absolute path, .xlf ending)
     * @param string $langKey The language key
     *
     * @return string HTML text string message
     */
    protected function writeNewXliffFile(string $xmlFile, string $newFileName, string $langKey): string
    {
        $xml = $this->generateFileContent($xmlFile, $langKey);
        $result = '';
        if (!file_exists($newFileName)) {
            GeneralUtility::writeFile($newFileName, $xml);
            $result = $newFileName;
        }
        return $result;
    }

    /**
     * @param string $xmlFile
     * @param string $langKey
     *
     * @return string
     */
    protected function generateFileContent(string $xmlFile, string $langKey): string
    {
        // Initialize variables:
        $xml = [];
        $LOCAL_LANG = $this->getCombinedTranslationFileContent($xmlFile);
        $xml[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
        $xml[] = '<xliff version="1.2" xmlns:t3="http://typo3.org/schemas/xliff">';
        $xml[] = '	<file source-language="en"'
            . ($langKey !== 'default' ? ' target-language="' . $langKey . '"' : '')
            . ' datatype="plaintext" original="messages" date="'
            . gmdate('Y-m-d\TH:i:s\Z') . '"' . ' product-name="">';
        $xml[] = '		<header/>';
        $xml[] = '		<body>';
        foreach ($LOCAL_LANG[$langKey] as $key => $data) {
            if (is_array($data)) {
                $source = $data[0]['source'];
                $target = $data[0]['target'];
            } else {
                $source = $LOCAL_LANG['default'][$key];
                $target = $data;
            }
            if ($langKey === 'default') {
                $xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve">';
                $xml[] = '				<source>' . htmlspecialchars($source) . '</source>';
                $xml[] = '			</trans-unit>';
            } else {
                $xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve" approved="yes">';
                $xml[] = '				<source>' . htmlspecialchars($source) . '</source>';
                $xml[] = '				<target>' . htmlspecialchars($target) . '</target>';
                $xml[] = '			</trans-unit>';
            }
        }
        $xml[] = '		</body>';
        $xml[] = '	</file>';
        $xml[] = '</xliff>';
        return implode(LF, $xml);
    }

    /**
     * Reads/Requires locallang files and returns raw $LOCAL_LANG array
     *
     * @param string $languageFile Absolute reference to the ll-XML locallang file.
     *
     * @return array LOCAL_LANG array from ll-XML file (with all possible sub-files for languages included)
     */
    protected function getCombinedTranslationFileContent(string $languageFile): array
    {
        $LOCAL_LANG = [];

        if (strpos($languageFile, '.xml')) {
            $ll = GeneralUtility::xml2array(file_get_contents($languageFile));
            $includedLanguages = array_keys($ll['data']);

            foreach ($includedLanguages as $langKey) {
                $parser = GeneralUtility::makeInstance(LocallangXmlParser::class);
                $llang = $parser->getParsedData($languageFile, $langKey);
                unset($parser);
                $LOCAL_LANG[$langKey] = $llang[$langKey];
            }
        }
        if (empty($includedLanguages)) {
            throw new \RuntimeException('data section not found in "' . $languageFile . '"', 1314187884);
        }
        /** @noinspection PhpUndefinedVariableInspection */
        return $LOCAL_LANG;
    }

    /**
     * Converts an XML string to a PHP array.
     * This is the reverse function of array2xml()
     * This is a wrapper for xml2arrayProcess that adds a two-level cache
     *
     * @param string $string XML content to convert into an array
     * @param string $NSprefix The tag-prefix resolve, eg. a namespace like "T3:"
     * @param bool $reportDocTag If set, the document tag will be set in the key "_DOCUMENT_TAG" of the output array
     *
     * @return mixed If the parsing had errors, a string with the error message is returned.
     *  Otherwise an array with the content.
     *
     * @see array2xml(),xml2arrayProcess()
     */
    protected function xml2array(string $string, string $NSprefix = '', bool $reportDocTag = false)
    {
        return GeneralUtility::xml2array($string, $NSprefix, $reportDocTag);
    }
}
