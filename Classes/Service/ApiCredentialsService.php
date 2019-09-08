<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use GeorgRinger\Crowdin\Configuration\Project;
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
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function get(): Project
    {
        $entry = $this->registry->get('crowdin', 'credentials-current');
        if ($entry === null) {
            throw new NoApiCredentialsException('No api credentials provided', 1566643810);
        }

        return Project::initializeByJson($entry);
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
        $project = GeneralUtility::makeInstance(Project::class, $project, $key);
        $this->registry->set('crowdin', 'credentials-current', $project->__toString());
        $this->registry->set('crowdin', 'credentials-' . $project->getIdentifier(), $project->__toString());
    }

    /**
     * @param string $identifier project identifier
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function switchTo(string $identifier): Project
    {
        $entry = $this->registry->get('crowdin', 'credentials-' . $identifier);
        if ($entry === null) {
            throw new NoApiCredentialsException('No project found', 1567968195);
        }

        $project = Project::initializeByJson($entry);
        $this->registry->set('crowdin', 'credentials-current', $project->__toString());

        return $project;
    }

    public function reset(): void
    {
        $this->registry->remove('crowdin', 'credentials');
    }
}
