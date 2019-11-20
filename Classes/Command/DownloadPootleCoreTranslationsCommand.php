<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\Crowdin\Info\CoreInformation;
use GeorgRinger\Crowdin\Service\CoreTranslationService;
use GeorgRinger\Crowdin\Utility\FileHandling;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadPootleCoreTranslationsCommand extends BaseCommand
{
    // not 'am',
    public const LANGUAGE_LIST = ['mi', 'bs', 'et', 'he', 'pt_BR', 'fr_CA', 'gl', 'ro', 'sk', 'sl', 'es', 'sv', 'tr', 'uk', 'hu', 'is', 'lv', 'no', 'fa','fi', 'pl', 'ar', 'bg', 'de', 'hr', 'cs', 'da', 'nl', 'fr', 'el', 'hi', 'it', 'ja', 'km', 'ru', 'th', 'zh'];

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Extract translations from translation server')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->addArgument('key', InputArgument::REQUIRED, 'Extension key')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
            ->addArgument('version', InputArgument::REQUIRED, 'Core version');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));
        $io = new SymfonyStyle($input, $output);

        $version = (int)$input->getArgument('version');
        if (!in_array($version, CoreInformation::getAllVersions(), true)) {
            $io->error('Provided version is invalid');
            return;
        }

        $service = new CoreTranslationService($this->getProject()->getIdentifier());
        $key = $input->getArgument('key');
        $languages = $input->getArgument('language');

        if ($key !== '*' && !in_array($key, CoreInformation::getCoreExtensionKeys($version), true)) {
            $io->error('No core ext provided');
        }

        $keyList = $key === '*' ? CoreInformation::getCoreExtensionKeys($version) : FileHandling::trimExplode(',', $key, true);
        $languageList = $languages === '*' ? self::LANGUAGE_LIST : FileHandling::trimExplode(',', $languages, true);

        foreach ($languageList as $language) {
            if (!in_array($language, self::LANGUAGE_LIST, true)) {
                $io->warning(sprintf('Language "%s" not supported', $language));
                continue;
            }

            $io->title(sprintf('Working on language "%s" for version %s', $language, $version));

            $progress = new ProgressBar($output, count($keyList));
            $progress->start();

            foreach ($keyList as $key) {
                try {
                    $service->getTranslation($key, $language, $version);
                    $io->success(sprintf('Done with "%s" in "%s" for version %s!', $key, $language, $version));
                } catch (\Exception $e) {
                    $io->warning(sprintf('Error with "%s" in "%s": %s!', $key, $language, $e->getMessage()));
                }
                $progress->advance();
            }
            $progress->finish();
        }
    }
}
