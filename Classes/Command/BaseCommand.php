<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use GeorgRinger\Crowdin\Configuration\Project;
use GeorgRinger\Crowdin\Service\ConfigurationService;
use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    /** @var ConfigurationService */
    protected $configurationService;

    public function setupConfigurationService(string $projectIdentifier): void
    {
        $this->configurationService = new ConfigurationService($projectIdentifier);
    }

    protected function getConfigurationService(): ConfigurationService
    {
        return $this->configurationService;
    }

    protected function getProject(): Project
    {
        return $this->configurationService->getProject();
    }
}
