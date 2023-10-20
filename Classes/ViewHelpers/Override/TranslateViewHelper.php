<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\ViewHelpers\Override;

use GeorgRinger\Crowdin\ExtensionConfiguration;
use GeorgRinger\Crowdin\Xclass\LanguageServiceXclassed;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Mvc\Request;

class TranslateViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use CompileWithRenderStatic;

    /** @var ExtensionConfiguration */
    protected static $extensionConfiguration;

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'Translation Key');
        $this->registerArgument('id', 'string', 'Translation ID. Same as key.');
        $this->registerArgument('default', 'string', 'If the given locallang key could not be found, this value is used. If this argument is not set, child nodes will be used to render the default');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string');
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('languageKey', 'string', 'Language key ("da" for example) or "default" to use. If empty, use current language.');
        // @deprecated will be removed in TYPO3 v13.0. Deprecation is triggered in LocalizationUtility
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist. Ignored in non-extbase context. Deprecated, will be removed in TYPO3 v13.0');
    }

    /**
     * Return array element by key.
     *
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @throws Exception
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $key = $arguments['key'];
        $id = $arguments['id'];
        $default = $arguments['default'];
        $extensionName = $arguments['extensionName'] ?? '';
        $translateArguments = $arguments['arguments'];

        // Use key if id is empty.
        if ($id === null) {
            $id = $key;
        }

        if ((string) $id === '') {
            throw new Exception('An argument "key" or "id" has to be provided', 1351584844);
        }

        /** @var RenderingContext $renderingContext */
        $request = $renderingContext->getRequest();

        // @TODO Better way to get request controller extensionName 
        if(!$extensionName && $request instanceof Request) {
            $extensionName =$request->getControllerExtensionName();
        }

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
                if (str_contains($id, 'EXT:'.$extension)) {
                    $isCoreExt = true;
                }
            }
            if ($isCoreExt) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        } elseif (self::$extensionConfiguration->getCrowdinIdentifier()) {
            if (str_contains($id, 'EXT:'.self::$extensionConfiguration->getExtensionKey())) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        }

        return LocalizationUtility::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }
}
