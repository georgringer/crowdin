<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

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
        return $this->getPath('rsync');
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
