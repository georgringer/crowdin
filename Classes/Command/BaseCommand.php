<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Service\BaseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BaseCommand extends Command
{
    protected function showProjectIdentifiere(SymfonyStyle $io): void
    {
        $baseService = GeneralUtility::makeInstance(BaseService::class);
        $io->title(sprintf('Project %s', $baseService->getProjectIdentifier()));
    }
}
