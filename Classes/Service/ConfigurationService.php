<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use GeorgRinger\Crowdin\Configuration\Project;
use GeorgRinger\Crowdin\Exception\NoApiCredentialsException;
use GeorgRinger\Crowdin\Utility\FileHandling;

class ConfigurationService
{
    private $configurationFile = '';
    protected $configuration = [];

    public function __construct()
    {
        $this->configurationFile = __DIR__ . '/../../configuration.json';
        if (!is_file($this->configurationFile)) {
            throw new \RuntimeException(sprintf('Configuration file %s not found', $this->configurationFile));
        }

        $this->configuration = json_decode(file_get_contents($this->configurationFile), true);
    }

    /**
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function getProject(): Project
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

    public function set(string $project, string $key): void
    {
        $this->configuration['current'] = $project;
        $this->configuration['projects'][$project] = [
            'key' => $key
        ];
        $this->persistConfiguration();
    }

    public function updateSingleConfiguration(string $key, $value): bool
    {
        try {
            $projectIdentifier = $this->getProject()->getIdentifier();
            if ($value === null) {
                unset($this->configuration['projects'][$projectIdentifier][$key]);
            } else {
                $this->configuration['projects'][$projectIdentifier][$key] = $value;
            }
            $this->persistConfiguration();
            return true;
        } catch (NoApiCredentialsException $e) {
        }

        return false;
    }

    /**
     * @return string
     * @throws NoApiCredentialsException
     */
    public function getCurrentProjectName(): string
    {
        $project = $this->getProject();
        return $project->getIdentifier();
    }

    /**
     * @return bool
     * @throws NoApiCredentialsException
     */
    public function isCoreProject(): bool
    {
        return $this->getCurrentProjectName() === 'typ3-cms';
    }

    public function getPathDownloads(): string
    {
        return $this->getPath('downloads');
    }

    public function getPathExport(): string
    {
        return $this->getPath('export');
    }

    public function getPathRsync(): string
    {
        return $this->getPath('rsync');
    }

    public function getPathFinal(): string
    {
        return $this->getPath('final');
    }

    public function getPathExtracts(): string
    {
        return $this->getPath('extracts');
    }

    protected function getPath(string $key): string
    {
        $mainPath = $this->configuration['paths']['entryPath'];
        if (!is_dir($mainPath)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', $mainPath), 1573629792);
        }
        $subPath = rtrim($mainPath, '/') . '/' . trim($this->configuration['paths'][$key], '/') . '/';
        if (!is_dir($subPath)) {
            FileHandling::mkdir_deep($subPath);
//            throw new \RuntimeException(sprintf('Path "%s" does not exist', $subPath), 1573629793);
        }

        return $subPath;
    }

    protected function persistConfiguration(): void
    {
        file_put_contents($this->configurationFile, json_encode($this->configuration));
    }

}
