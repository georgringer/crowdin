<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Backend\ToolbarItems;

use FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceXclassed;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

class CrowdinToolbarItem implements ToolbarItemInterface
{
    private readonly int $typo3Version;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly IconFactory  $iconFactory
    )
    {
        $this->typo3Version = (new Typo3Version())->getMajorVersion();

        if ($this->typo3Version >= 12) {
            $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
                JavaScriptModuleInstruction::create('@friendsoftypo3/crowdin/toolbar.js')
                    ->invoke('create', [
                        // options go here...
                    ])
            );
        } else {
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Crowdin/Toolbar/CrowdinMenu');
        }
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function getItem(): string
    {
        $title = 'Crowdin';

        $crowdin = [];
        $crowdin[] = '<span title="' . htmlspecialchars($title) . '">' . $this->getSpriteIcon('crowdin-toolbar-icon', 'inline') . '</span>';

        return implode(LF, $crowdin);
    }

    public function getDropDown(): string
    {
        $entries = [];

        $extensions = $this->getExtensionsCompatibleWithCrowdin();

        foreach ($extensions as $extension) {
            $icon = isset($extension['icon'])
                ? '<img src="' . htmlspecialchars($extension['icon']) . '" alt="' . htmlspecialchars($extension['name']) . '" style="width:16px">'
                : $this->getSpriteIcon($extension['iconIdentifier']);
            if ($this->typo3Version >= 12) {
                $entries[] = '<li>';
                $entries[] = '  <a href="#" class="crowdin-extension dropdown-item" role="menuitem" data-extension="' . $extension['key'] . '">';
                $entries[] = '    <span class="dropdown-item-columns">';
                $entries[] = '      <span class="dropdown-item-column dropdown-item-column-icon" aria-hidden="true">' .
                    $icon . '</span>';
                $entries[] = '      <span class="dropdown-item-column dropdown-item-column-title">' .
                    htmlspecialchars($extension['name']) . '</span>';
                $entries[] = '    </span>';
                $entries[] = '  </a>';
                $entries[] = '</li>';
            } else {
                $entries[] = '<div class="dropdown-table-row">';
                $entries[] = '  <div class="dropdown-table-column dropdown-table-column-top dropdown-table-icon">';
                $entries[] = $icon;
                $entries[] = '  </div>';
                $entries[] = '  <div class="dropdown-table-column">';
                $entries[] = '<a href="#" class="crowdin-extension" data-extension="' . $extension['key'] . '">'
                    . htmlspecialchars($extension['name']) . '</a>';
                $entries[] = '  </div>';
                $entries[] = '</div>';
            }
        }

        $content = '';
        if ($this->typo3Version >= 12) {
            $content .= '<p class="h3 dropdown-headline" id="crowdin-dropdown-headline">Crowdin</p>';
            $content .= '<hr class="dropdown-divider" aria-hidden="true">';
            $content .= '<nav class="t3js-crowdinmenu">';
            $content .= '<ul class="dropdown-list" role="menu" aria-labelledby="crowdin-dropdown-headline">';
            $content .= implode(LF, $entries);
            $content .= '</ul>';
            $content .= '</nav>';
        } else {
            $content .= '<h3 class="dropdown-headline">Crowdin</h3>';
            $content .= '<hr />';
            $content .= '<div class="dropdown-table">' . implode('', $entries) . '</div>';
        }

        return $content;
    }

    protected function getExtensionsCompatibleWithCrowdin(): array
    {
        $extensions = [];

        // TYPO3 Core is always compatible with Crowdin
        $extensions['_'] = [
            'key' => 'typo3',
            'name' => 'TYPO3 Core Extensions',  // TODO: translate!
            'iconIdentifier' => 'actions-brand-typo3',
        ];

        $labelsDirectory = Environment::getVarPath() . '/labels/t3';

        if (is_dir($labelsDirectory)) {
            $compatibleExtensions = GeneralUtility::get_dirs($labelsDirectory);

            $listUtility = GeneralUtility::makeInstance(ListUtility::class);
            $availableExtensions = $listUtility->getAvailableExtensions();
            $thirdPartyExtensions = array_diff_key($availableExtensions, array_flip(LanguageServiceXclassed::CORE_EXTENSIONS));

            foreach ($thirdPartyExtensions as $extension) {
                if (in_array($extension['key'], $compatibleExtensions)) {
                    $extensions[$extension['key']] = [
                        'key' => $extension['key'],
                        'name' => $extension['title'],
                        'icon' => $extension['icon'],
                    ];
                }
            }
        }

        // Sort extensions by extension key (TYPO3 Core always first)
        ksort($extensions);

        return $extensions;
    }

    protected function getSpriteIcon(
        string $iconName,
        ?string $alternativeMarkupIdentifier = null
    ): string
    {
        $iconSize = $this->typo3Version >= 13
            ? \TYPO3\CMS\Core\Imaging\IconSize::SMALL
            : \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL;
        $icon = $this->iconFactory->getIcon($iconName, $iconSize)->render($alternativeMarkupIdentifier);

        return $icon;
    }

    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function hasDropDown(): bool
    {
        return true;
    }

    public function getIndex(): int
    {
        return 25;
    }
}
