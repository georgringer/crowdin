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
      that.toggleInContextLocalization(event.target.checked);
    });
  };

  CrowdinMenu.setCurrentExtension = function (extension) {
    this.showSpinner();
    $.ajax({
      url: TYPO3.settings.ajaxUrls['crowdin_setextension'],
      type: 'post',
      data: {
        extension: extension
      },
      cache: false,
      success: function (data) {
        location.reload()
      }
    });
  }

  CrowdinMenu.toggleInContextLocalization = function (enable) {
    this.showSpinner();
    $.ajax({
      url: TYPO3.settings.ajaxUrls['crowdin_toggletranslation'],
      type: 'post',
      data: {
        enable: enable
      },
      cache: false,
      success: function (data) {
        location.reload()
      }
    });
  }

  CrowdinMenu.showSpinner = function () {
    var $toolbarItemIcon = $(this.options.toolbarIconSelector, this.options.containerSelector);

    Icons.getIcon('spinner-circle-light', Icons.sizes.small).done(function(spinner) {
      $toolbarItemIcon.replaceWith(spinner);
    });
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
