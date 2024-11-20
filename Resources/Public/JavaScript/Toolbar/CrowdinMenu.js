define([
    'jquery',
    'TYPO3/CMS/Backend/Icons'
    ], function ($, Icons) {

    var CrowdinMenu = {
        options: {}
    };

    CrowdinMenu.initialize = function () {
        // TODO
    };

    /**
     * Initializes and return the Crowdin object
     */
    return function () {
        $(document).ready(function () {
            Crowdin.initialize();
        });

        TYPO3.CrowdinMenu = CrowdinMenu;
        return CrowdinMenu;
    }();
});
