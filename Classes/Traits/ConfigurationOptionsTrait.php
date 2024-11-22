<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Traits;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

trait ConfigurationOptionsTrait
{
    protected static function getConfigurationOption(string $name, string $defaultValue): string
    {
        $user = static::getBackendUser();
        return $user !== null ? $user->uc['crowdin'][$name] ?? $defaultValue : $defaultValue;
    }

    protected static function setConfigurationOption(string $name, string $value): void
    {
        $user = static::getBackendUser();
        $user->uc['crowdin'][$name] = $value;
        $user->writeUC();
    }

    protected static function getBackendUser(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
