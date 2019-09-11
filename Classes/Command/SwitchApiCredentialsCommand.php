<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Service\ApiCredentialsService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SwitchApiCredentialsCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Switch API credentials to specific project')
            ->addArgument('project', InputArgument::REQUIRED, 'Project Identifier');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $apiCredentialsService = new ApiCredentialsService();
        $apiCredentialsService->switchTo($input->getArgument('project'));

        $io->success('API credentials have been successfully switched!');
    }
}
