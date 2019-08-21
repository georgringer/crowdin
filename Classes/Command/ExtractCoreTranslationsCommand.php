<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\Crowdin\Service\CoreTranslationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ExtractCoreTranslationsCommand extends Command
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
            ->addArgument('version', InputArgument::REQUIRED, 'Core version');
    }

    /**
     * Geocode all records
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $service = GeneralUtility::makeInstance(CoreTranslationService::class);
        $key = $input->getArgument('key');
        if ($key !== '*' && !in_array($key, CoreTranslationService::CORE_EXTENSIONS, true)) {
            $io->error('No core ext provided');
        }

        $version = (int)$input->getArgument('version');
        if (!in_array($version, [8, 9, 10], true)) {
            $io->error('Provided version is invalid');
        }

        if ($key !== '*') {
            $service->getTranslation($key, $input->getArgument('language'), $version);
        } else {
            $keyList = CoreTranslationService::CORE_EXTENSIONS;

            $progress = new ProgressBar($output, count($keyList));
            $progress->start();

            foreach ($keyList as $key) {
                try {
                    $service->getTranslation($key, $input->getArgument('language'), $version);
                    $io->success(sprintf('Done with "%s"', $key));
                } catch (\Exception $e) {
                    $io->warning(sprintf('Error with "%s"', $key));
                }
                $progress->advance();
            }
            $progress->finish();
        }
    }
}
