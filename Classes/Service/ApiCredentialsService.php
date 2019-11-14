<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use GeorgRinger\Crowdin\Configuration\Project;
use GeorgRinger\Crowdin\Exception\NoApiCredentialsException;

class ApiCredentialsService extends ConfigurationService
{
    /**
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function get(): Project
    {
        $projectName = $this->configuration['current'] ?? null;
        if ($projectName === null) {
            throw new NoApiCredentialsException('No api credentials provided', 1566643810);
        }

        $data = $this->configuration['projects'][$projectName] ?? null;
        if ($data === null) {
            throw new NoApiCredentialsException(sprintf('No configuration found for "%s"', $projectName), 1566643811);
        }

        return Project::initializeByArray($projectName, $data);
    }

    /**
     * @return string
     * @throws NoApiCredentialsException
     */
    public function getCurrentProjectName(): string
    {
        $project = $this->get();
        return $project->getIdentifier();
    }

    public function set(string $project, string $key): void
    {
        $this->configuration['current'] = $project;
        $this->configuration['projects'][$project] = [
            'key' => $key
        ];
        $this->persistConfiguration();
    }

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
