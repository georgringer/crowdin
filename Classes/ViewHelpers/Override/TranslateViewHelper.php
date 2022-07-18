<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\ViewHelpers\Override;

use GeorgRinger\Crowdin\ExtensionConfiguration;
use GeorgRinger\Crowdin\Xclass\LanguageServiceXclassed;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{


    /** @var ExtensionConfiguration */
    protected static $extensionConfiguration;


    /**
     * Return array element by key.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @throws Exception
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $key = $arguments['key'];
        $id = $arguments['id'];
        $default = $arguments['default'];
        $extensionName = $arguments['extensionName'];
        $translateArguments = $arguments['arguments'];

        // Use key if id is empty.
        if ($id === null) {
            $id = $key;
        }

        if ((string)$id === '') {
            throw new Exception('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = $renderingContext->getRequest();
        $extensionName = $extensionName ?? $request->getControllerExtensionName();
        try {
            $value = static::translate($id, $extensionName, $translateArguments, $arguments['languageKey'], $arguments['alternativeLanguageKeys']);
        } catch (\InvalidArgumentException $e) {
            $value = null;
        }
        if ($value === null) {
            $value = $default ?? $renderChildrenClosure();
            if (!empty($translateArguments)) {
                $value = vsprintf($value, $translateArguments);
            }
        }
        return $value;
    }


    protected static function translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys)
    {
        if (!self::$extensionConfiguration) {
            self::$extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        }

        if (self::$extensionConfiguration->isUsedForCore()) {
            $isCoreExt = false;
            foreach (LanguageServiceXclassed::CORE_EXTENSIONS as $extension) {
                if (str_contains($id, 'EXT:' . $extension)) {
                    $isCoreExt = true;
                }
            }
            if ($isCoreExt) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        } elseif (self::$extensionConfiguration->getCrowdinIdentifier()) {
            if (str_contains($id, 'EXT:' . self::$extensionConfiguration->getExtensionKey())) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        }

        return parent::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }

}
