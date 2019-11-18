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

To enable in-context localization: Switch your user to Language *Crowdin In-Context Localization*

## API-Reference / Commands

### Convert XML to XLF

```
# Arguments: file name
./bin/typo3 crowdin:convertXml2Xlf ./path-to-a-xml-file.xml
```

### Set API Key

The API key is added to the registry, so it must only be set once.

```
# Arguments: project-identifier api-key
./bin/typo3 crowdin:setApiCredentials typo3-cms 123456
```

By using the following command you can switch between projects without readding the API key again

```
# Arguments: project-identifier
./bin/typo3 crowdin:switchApiCredentials another-project
```

### Trigger build a project

This triggers the build of a project. Only if a project has been built, it can be exported later

```
# Arguments: branch
./bin/typo3 crowdin:build master
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

### Extract languages from Crowdin of core

Download language packs from Crowdin and create single zip packages

```
# Arguments: language branch
./bin/typo3 crowdin:crowdin:extractCore de master
```

### Extract languages from Crowdin of extension

Download language packs from Crowdin and create single zip packages

```
./bin/typo3 crowdin:crowdin:extractExt
```

### Status

Get translation status

```bash
./bin/typo3 crowdin:status
```

Result
```
 ------------------------------- --------------
  name                            Progress (%)
 ------------------------------- --------------
  Croatian - hr                   69
  Czech - cs                      22
  Danish - da                     94
  Dutch - nl                      82
  French - fr                     50 / 49
  German - de                     92
  Greek - el                      5
  Hindi - hi                      50
  Italian - it                    46
  Japanese - ja                   8
  Khmer - km                      40
  Portuguese, Brazilian - pt-BR   0
  Russian - ru                    75
  Spanish - es-ES                 0
  Thai - th                       43
 ------------------------------- --------------
```

### Cleanup

Cleanup the temporary files

```
./bin/typo3 crowdin:clean
```

