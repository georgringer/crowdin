# TYPO3 Extension `crowdin`

This extension is currently a proof of concept

## Install

Install this extension + `akeneo/crowdin-api` (dev-master)

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
./bin/typo3 crowdin:extractCoreTranslations about de 9
```

### Extract extension translations + upload to Crowdin

This command will download translations from translation server and upload those to Crowdin

```
# Arguments: extension-key language
./bin/typo3 crowdin:extractExtTranslations news de
```

### Download languages from Crowdin

Download language packs from Crowdin and create single zip packages

```
# Arguments: language branch
./bin/typo3 crowdin:extractExtTranslations de master
```

