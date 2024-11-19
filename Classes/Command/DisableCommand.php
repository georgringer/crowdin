<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

use GeorgRinger\Crowdin\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisableCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        GeneralUtility::makeInstance(Setup::class)->disable();
        $io = new SymfonyStyle($input, $output);
        $io->success('Crowdin disabled');
        return Command::SUCCESS;
    }
}
