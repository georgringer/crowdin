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
use GeorgRinger\Crowdin\Service\InfoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SetApiCredentialsCommand extends Command
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Set API credentials')
            ->addArgument('project', InputArgument::REQUIRED, 'Project Identifier')
            ->addArgument('key', InputArgument::REQUIRED, 'Key');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $apiCredentialsService = GeneralUtility::makeInstance(ApiCredentialsService::class);
        $apiCredentialsService->set($input->getArgument('project'), $input->getArgument('key'));

        $io->success('API credentials have been successfully set!');
        $io->caution('However... hold on and wait for a 1st test!');

        $infoService = GeneralUtility::makeInstance(InfoService::class);
        try {
            $data = $infoService->get();
            $data->getContents();
            $io->success('Yes it works!');
        } catch (\Exception $e) {
            $io->error('Sorry, seems there is a problem: ' . $e->getMessage());
        }
    }
}
