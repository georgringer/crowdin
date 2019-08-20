<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use FriendsOfTYPO3\TtAddress\Service\GeocodeService;
use GeorgRinger\Crowdin\Service\TranslationServerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ExtractT3DownloadsCommand extends Command
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
            ->addArgument('version', InputArgument::OPTIONAL, 'Core version');
    }

    /**
     * Geocode all records
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $service = GeneralUtility::makeInstance(TranslationServerService::class);
        if (in_array($input->getArgument('key'), TranslationServerService::CORE_EXTENSIONS, true)
            && !$input->getArgument('version')) {
            $io->error('For core extensions, provide version');
        }

        $version = (int)$input->getArgument('version');
        if (!in_array($version, [8,9,10], true)) {
            $io->error('Provided version is invalid');
        }

        $service->getTranslation($input->getArgument('key'), $input->getArgument('language'), $version);
    }

    /**
     * @param string $key Google Maps key
     * @return GeocodeService
     */
    protected function getGeocodeService(string $key)
    {
        return GeneralUtility::makeInstance(GeocodeService::class, $key);
    }
}
