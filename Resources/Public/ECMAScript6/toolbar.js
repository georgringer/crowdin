/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

import Icons from '@typo3/backend/icons.js';

/**
 * Module: @friendsoftypo3/crowdin/toolbar
 * @exports @friendsoftypo3/crowdin/toolbar
 */
class Toolbar {
  create(options) {
    this.options = options || {};

    this.selectors = {
      containerSelector: '#friendsoftypo3-crowdin-backend-toolbaritems-crowdintoolbaritem',
      toolbarIconSelector: '.dropdown-toggle span.t3js-icon',
    };

    this.initialize();
  }

  initialize() {
    const that = this;

    document.querySelectorAll('a.crowdin-extension').forEach(function (item, idx) {
      item.addEventListener('click', function (event) {
        event.preventDefault();
        that.setCurrentExtension(item.dataset.extension);
      }.bind(this));
    });

    document.querySelector(this.selectors.containerSelector + ' [data-formengine-input-name="crowdin_enable"]').addEventListener('change', function (event) {
      event.preventDefault();
      that.toggleInPlaceTranslation(event.target.checked);
    }.bind(this));
  }

  setCurrentExtension(extension) {
    this.showSpinner();
    fetch(TYPO3.settings.ajaxUrls['crowdin_setextension'],
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            extension: extension
          })
        })
        .then(response => response.json())
        .then(data => {
            location.reload()
        });
  }

  toggleInPlaceTranslation(enable) {
    this.showSpinner();
    fetch(TYPO3.settings.ajaxUrls['crowdin_toggletranslation'],
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            enable: enable
          })
        })
        .then(response => response.json())
        .then(data => {
            location.reload()
        });
  }

  showSpinner() {
    const iconSelector = this.selectors.containerSelector + ' ' + this.selectors.toolbarIconSelector;

    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then(function (icon) {
      document.querySelector(iconSelector).outerHTML = icon;
    });
  }
}

export default new Toolbar();
