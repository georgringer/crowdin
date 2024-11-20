[![Latest Stable Version](https://poser.pugx.org/friendsoftypo3/crowdin/v/stable)](https://extensions.typo3.org/extension/crowdin/)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13-orange.svg)](https://get.typo3.org/version/13)
[![TYPO3 12](https://img.shields.io/badge/TYPO3-12-orange.svg)](https://get.typo3.org/version/12)
[![TYPO3 11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/11)
[![Total Downloads](https://poser.pugx.org/friendsoftypo3/crowdin/downloads)](https://packagist.org/packages/friendsoftypo3/crowdin)
[![Monthly Downloads](https://poser.pugx.org/friendsoftypo3/crowdin/d/monthly)](https://packagist.org/packages/friendsoftypo3/crowdin)

# TYPO3 Extension `crowdin`

This extensions integrates the inplace editing of Crowdin into TYPO3.
Using this features makes it fast and simple to add translations of XLF files used in the backend.

![In-Context Localization](Resources/Public/Screenshots/crowdin-inline-localization.png)

**Important:** This extensions can **not** be used to translate content but "static" translations saved in `xlf` files.

## 1. Install

### Using composer

1. `composer req friendsoftypo3/crowdin`.
2. `./vendor/bin/typo3 crowdin:enable`

### Non composer

1. Download the extension from TER
2. `./typo3/sysext/core/bin/typo3 crowdin:enable`

### Additional information

The enable command writes the following information to `LocalConfiguration.php`:

```php
$GLOBALS['TYPO3_CONF_VARS']
    ['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
    ['SYS']['fluid']['namespaces'] => [
            'f' => [
                'TYPO3\\CMS\\Fluid\\ViewHelpers',
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override',
            ],
        ],
    ];
```

## Usage

Follow the next steps to be able to use Crowdin in the backend:

1. Switch to *Install Tool* => *Maintenance* => **Manage Language Packs**
2. Click **+  Add language** and select **Crowdin In-Context Localization [t3]**, click **Update all**.
3. Switch to the **User settings**
3. Select *Crowdin In-Context Localization*.
4. Click save button.

After the automatic reload, a Crowdin modal will be shown to log in with your Crowdin account and to
select the language you want to translate to.

To enable in-context localization: Switch your user to Language *Crowdin In-Context Localization*

## Extension Configuration

This extensions can be configured in the Install Tool using *Settings* => *Extension Configuration* => *Crowdin*.

- `Enable to translate TYPO3 core`: Enable this checkbox to translate TYPO3 and its system extensions.
- `Extension key`: If the checkbox above is **NOT** set, an extension key can be provided which can be
  translated. A full list of available extensions on Crowdin is available at
  [localize.typo3.org/xliff/status.html](https://localize.typo3.org/xliff/status.html). E.g., `news`.

