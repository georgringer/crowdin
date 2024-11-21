<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Xclass;

use FriendsOfTYPO3\Crowdin\UserConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageServiceXclassed extends LanguageService
{
    /** @var UserConfiguration */
    protected $userConfiguration;

    public const CORE_EXTENSIONS = [
        'about',
        'adminpanel',
        'backend',
        'belog',
        'beuser',
        'core',
        'dashboard',
        'extbase',
        'extensionmanager',
        'felogin',
        'filelist',
        'filemetadata',
        'fluid',
        'fluid_styled_content',
        'form',
        'frontend',
        'impexp',
        'indexed_search',
        'info',
        'install',
        'linkvalidator',
        'lowlevel',
        'opendocs',
        'reactions',
        'recordlist',
        'recycler',
        'redirects',
        'reports',
        'rte_ckeditor',
        'scheduler',
        'seo',
        'setup',
        'styleguide',
        'sys_note',
        't3editor',
        'tstemplate',
        'viewpage',
        'webhooks',
        'workspaces',
    ];

    public function sL($input): string
    {
        $this->reinitLanguage($input);

        return parent::sL($input);
    }

    protected function includeLanguageFileRaw($fileRef)
    {
        $this->reinitLanguage($fileRef);

        return parent::includeLanguageFileRaw($fileRef);
    }

    protected function readLLfile($fileRef): array
    {
        $this->reinitLanguage($fileRef);

        return parent::readLLfile($fileRef);
    }

    protected function reinitLanguage($path): void
    {
        if (!is_string($path)) {
            return;
        }
        $this->loadUserConfiguration();
        if ($this->userConfiguration->usedForCore) {
            $isCoreExt = false;
            foreach (self::CORE_EXTENSIONS as $extension) {
                if (str_contains($path, 'EXT:' . $extension)) {
                    $isCoreExt = true;
                }
            }
            if ($isCoreExt) {
                $this->lang = 't3';
            } else {
                $this->lang = 'default';
            }
        } elseif ($this->userConfiguration->crowdinIdentifier) {
            if (str_contains($path, 'EXT:' . $this->userConfiguration->extensionKey)) {
                $this->lang = 't3';
            } else {
                $this->lang = 'default';
            }
        }
    }

    protected function loadUserConfiguration(): void
    {
        if (!$this->userConfiguration) {
            $this->userConfiguration = GeneralUtility::makeInstance(UserConfiguration::class);
        }
    }
}
