<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use GeorgRinger\Crowdin\Configuration\Project;
use GeorgRinger\Crowdin\Exception\NoApiCredentialsException;

class ApiCredentialsService extends ConfigurationService
{

    /**
     * @param string $identifier project identifier
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function switchTo(string $identifier): Project
    {
        $entry = $this->configuration['projects'][$identifier] ?? null;
        if ($entry === null) {
            throw new NoApiCredentialsException('No project found', 1567968195);
        }

        $project = Project::initializeByArray($identifier, $entry);
        $this->configuration['current'] = $identifier;
        $this->persistConfiguration();

        return $project;
    }
}
