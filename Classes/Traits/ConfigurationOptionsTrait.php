<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Traits;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

trait ConfigurationOptionsTrait
{
    protected function getConfigurationOption(string $name, string $defaultValue): string
    {
        $user = $this->getBackendUser();
        return $user !== null ? $user->uc['crowdin'][$name] ?? $defaultValue : $defaultValue;
    }

    protected function setConfigurationOption(string $name, string $value): void
    {
        $user = $this->getBackendUser();
        $user->uc['crowdin'][$name] = $value;
        $user->writeUC();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
