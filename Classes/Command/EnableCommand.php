<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Command;

use FriendsOfTYPO3\Crowdin\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EnableCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        GeneralUtility::makeInstance(Setup::class)->enable();
        $io = new SymfonyStyle($input, $output);
        $io->success('Crowdin enabled');
        return Command::SUCCESS;
    }
}
