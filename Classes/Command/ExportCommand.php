<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\Crowdin\Service\ExportService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Export a project which means that the project is being built at Crowdin.')
            ->setHelp('Only if a project has been exported it is possible to get the latest translations. ')
            ->addArgument('branch', InputArgument::REQUIRED, 'If a branch is specified, only this branch is being built');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $branchName = $input->getArgument('branch') ?? '';
        $io = new SymfonyStyle($input, $output);
        $this->showProjectIdentifier($io);

        $service = new ExportService();
        $service->export($branchName);

        if ($branchName) {
            $io->success(sprintf('Project has been exported, limited to the branch *"%s"*!', $branchName));
        } else {
            $io->success('Project has been exported with *all branches*!');
        }
    }
}
