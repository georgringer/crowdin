define([
  'jquery',
  'TYPO3/CMS/Backend/Icons'
], function ($, Icons) {

  var CrowdinMenu = {
    options: {
      containerSelector: '#friendsoftypo3-crowdin-backend-toolbaritems-crowdintoolbaritem',
      toolbarIconSelector: '.dropdown-toggle span.t3js-icon',
    }
  };

  CrowdinMenu.initialize = function () {
    const that = this;
    $('a.crowdin-extension').on('click', function (event) {
      event.preventDefault();
      that.setCurrentExtension(event.target.dataset.extension);
    });
    $(this.options.containerSelector + ' [data-formengine-input-name="crowdin_enable"]').on('change', function (event) {
      event.preventDefault();
      that.toggleInPlaceTranslation(event.target.checked);
    });
  };

  CrowdinMenu.setCurrentExtension = function (extension) {
    console.log('Setting current extension to: ' + extension);

    var $toolbarItemIcon = $(this.options.toolbarIconSelector, this.options.containerSelector);

    Icons.getIcon('spinner-circle-light', Icons.sizes.small).done(function(spinner) {
      $toolbarItemIcon.replaceWith(spinner);
    });

    // TODO
  }

  CrowdinMenu.toggleInPlaceTranslation = function (enable) {
    console.log('Toggling in-place translation to: ' + enable);
    // TODO
  }

  /**
   * Initializes and return the Crowdin object
   */
  return function () {
    $(document).ready(function () {
      CrowdinMenu.initialize();
    });

    TYPO3.CrowdinMenu = CrowdinMenu;
    return CrowdinMenu;
  }();
});
