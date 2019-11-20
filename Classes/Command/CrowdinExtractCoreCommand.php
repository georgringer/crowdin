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

class CrowdinExtractCoreCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Download CORE translations')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
            ->addArgument('branch', InputArgument::REQUIRED, 'Branch');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));

        $io = new SymfonyStyle($input, $output);
        $project = $this->getProject();
        $io->title(sprintf('Project %s', $project->getIdentifier()));

        $languages = $input->getArgument('language') ?? '*';
        $languageList = $languages === '*' ? $project->getLanguages() : FileHandling::trimExplode(',', $languages, true);

        $service = new DownloadCrowdinTranslationService($this->getProject()->getIdentifier());
        foreach ($languageList as $language) {
            try {
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