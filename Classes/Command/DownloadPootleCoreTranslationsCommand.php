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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DownloadPootleCoreTranslationsCommand extends Command
{
    private const LANGUAGE_LIST = ['de', 'hr', 'cs', 'da', 'nl', 'fr', 'el', 'hi', 'it', 'ja', 'km', 'ru', 'th'];

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Extract translations from translation server')
            ->addArgument('key', InputArgument::REQUIRED, 'Extension key')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
            ->addArgument('version', InputArgument::REQUIRED, 'Core version');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $version = (int)$input->getArgument('version');
        if (!in_array($version, CoreInformation::getAllVersions(), true)) {
            $io->error('Provided version is invalid');
            return;
        }

        $service = GeneralUtility::makeInstance(CoreTranslationService::class);
        $key = $input->getArgument('key');
        $languages = $input->getArgument('language');

        if ($key !== '*' && !in_array($key, CoreInformation::getCoreExtensionKeys($version), true)) {
            $io->error('No core ext provided');
        }

        $keyList = $key === '*' ? CoreInformation::getCoreExtensionKeys($version) : GeneralUtility::trimExplode(',', $key, true);
        $languageList = $languages === '*' ? self::LANGUAGE_LIST : GeneralUtility::trimExplode(',', $languages, true);

        foreach ($languageList as $language) {
            if (!in_array($language, self::LANGUAGE_LIST, true)) {
                $io->warning(sprintf('Language "%s" not supported', $language));
                continue;
            }

            $io->title(sprintf('Working on language "%s"', $language));

            $progress = new ProgressBar($output, count($keyList));
            $progress->start();

            foreach ($keyList as $key) {
                try {
                    $service->getTranslation($key, $language, $version);
                    $io->success(sprintf('Done with "%s" in "%s"', $key, $language));
                } catch (\Exception $e) {
                    $io->warning(sprintf('Error with "%s" in "%s": %s', $key, $language, $e->getMessage()));
                }
                $progress->advance();
            }
            $progress->finish();
        }
    }
}
