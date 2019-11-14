<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Service\DownloadCrowdinTranslationService;
use GeorgRinger\Crowdin\Utility\FileHandling;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadCrowdinTranslationsCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Download CORE translations')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
            ->addArgument('branch', InputArgument::REQUIRED, 'Branch');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->showProjectIdentifier($io);

        $languages = $input->getArgument('language');
        $languageList = $languages === '*' ? DownloadPootleCoreTranslationsCommand::LANGUAGE_LIST : FileHandling::trimExplode(',', $languages, true);

        foreach ($languageList as $language) {
            try {
                $service = new DownloadCrowdinTranslationService();

                $service->downloadPackage(
                    $language,
                    $input->getArgument('branch') ?? ''
                );

                $io->success(sprintf('Data has been downloaded for %s!', $language));
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
    }
}
