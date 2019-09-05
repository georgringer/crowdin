<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use GeorgRinger\Crowdin\Exception\NoApiCredentialsException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiCredentialsService implements SingletonInterface
{
    /** @var Registry */
    protected $registry;

    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @return array
     * @throws NoApiCredentialsException
     */
    public function get(): array
    {
        $entry = $this->registry->get('crowdin', 'credentials');
        if ($entry === null) {
            throw new NoApiCredentialsException('No api credentials provided', 1566643810);
        }

        return explode('|', $entry);
    }

    /**
     * @return string
     * @throws NoApiCredentialsException
     */
    public function getProjectName(): string
    {
        $credentials = $this->get();
        return $credentials[0];
    }

    public function set(string $project, string $key): void
    {
        $this->registry->set('crowdin', 'credentials', implode('|', [$project, $key]));
    }

    public function reset(): void
    {
        $this->registry->remove('crowdin', 'credentials');
    }
}
