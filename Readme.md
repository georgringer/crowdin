# TYPO3 Extension `crowdin`

Integration of crowdin into TYPO3 with the following features:

- Inplace editing
- Fill Crowdin with all translation provided by the translation server
- Package translations to follow the structure required by TYPO3 sites

## Install

Install this extension + `akeneo/crowdin-api` (dev-master)

## Usage

### Crowdin In-Context Localization

![In-Context Localization](Resources/Public/Screenshots/crowdin-inline-localization.png)

To enable in-context localization, follow this steps:

1.) Add `$GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';` to your `typo3conf/AdditionalConfiguration.php`
2.) Download the [t3 language](https://github.com/georgringer/crowdin/blob/master/Resources/Private/LanguageExport/t3.zip) and place it in your language directory. 
3.) In the extensions settings define the project identifier you want to localize.
4.) Switch your user to Language *Crowdin In-Context Localization*

## Commands

### Set API Key

The API key is added to the registry, so it must only be set once. 

```
# Arguments: project-identifier api-key
./bin/typo3 crowdin:setApiCredentials typo3-cms 123456
```

### Extract core translations + upload to Crowdin

This command will download translations from translation server and upload those to Crowdin

Instead of a single extension name, also `'*'` can be used!

```
# Arguments: extension-key language version
./bin/typo3 crowdin:downloadPootleCoreTranslation about de 9
```

### Extract extension translations + upload to Crowdin

This command will download translations from translation server and upload those to Crowdin

```
# Arguments: extension-key language
./bin/typo3 crowdin:downloadPootleExtTranslation news de
```

### Download languages from Crowdin

Download language packs from Crowdin and create single zip packages

```
# Arguments: language branch copyToL10n
./bin/typo3 crowdin:downloadCrowdinTranslations de master 0
```

