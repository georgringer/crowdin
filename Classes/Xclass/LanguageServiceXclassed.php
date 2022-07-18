<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use GeorgRinger\Crowdin\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageServiceXclassed extends LanguageService
{

    /** @var ExtensionConfiguration */
    protected $extensionConfiguration;

    public const CORE_EXTENSIONS = [
        'about', 'adminpanel',
        'backend', 'beuser', 'belog', 'core', 'extbase', 'extensionmanager', 'felogin', 'filelist',
        'filemetadata', 'fluid', 'frontend', 'fluid_styled_content', 'form', 'frontend', 'impexp',
        'indexed_search', 'info', 'install', 'linkvalidator', 'lowlevel', 'opendocs', 'recordlist',
        'recycler', 'redirects', 'reports', 'scheduler', 'seo', 'setup', 'sys_note', 't3editor',
        'tstemplate', 'viewpage', 'workspaces',
        'info', 'rsaauth', 'sys_action', 'taskcenter',
        'dashboard'
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
        $this->loadExtensionConfiguration();
        if ($this->extensionConfiguration->isUsedForCore()) {
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
        } elseif ($this->extensionConfiguration->getCrowdinIdentifier()) {
            if (str_contains($path, 'EXT:' . $this->extensionConfiguration->getExtensionKey())) {
                $this->lang = 't3';
            } else {
                $this->lang = 'default';
            }
        }
    }

    protected function loadExtensionConfiguration(): void
    {
        if (!$this->extensionConfiguration) {
            $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        }
    }
}
