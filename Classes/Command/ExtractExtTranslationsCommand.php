<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\Crowdin\Service\ExtTranslationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ExtractExtTranslationsCommand extends Command
{

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
            ->addArgument('branch', InputArgument::REQUIRED, 'Target branch', 'master');
    }

    /**
     * Geocode all records
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extensionKey = $input->getArgument('key');
        $io = new SymfonyStyle($input, $output);

        $service = GeneralUtility::makeInstance(ExtTranslationService::class);

        $languages = GeneralUtility::trimExplode(',', $input->getArgument('language'), true);
        foreach ($languages as $language) {
            try {
                $service->getTranslation($extensionKey, $language, $input->getArgument('branch'));

                $io->success(sprintf('Translations for %s in %s has been downloaded', $extensionKey, $language));
            } catch (\Exception $e) {
                $io->error(sprintf('Error fetching translations for %s in %s: %s', $extensionKey, $language, $e->getMessage()));
            }
        }
    }
}
