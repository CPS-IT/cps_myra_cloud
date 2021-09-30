/**
 * Module: TYPO3/CMS/CPSMyraCloud/ContextMenuActions
 *
 * JavaScript to handle the click action of the "Hello World" context menu item
 * @exports TYPO3/CMS/CPSMyraCloud/ContextMenuActions
 */
define(['TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Notification'], function (AjaxRequest, Typo3Notification) {
    'use strict';

    /**
     * @exports TYPO3/CMS/CPSMyraCloud/ContextMenuActions
     */
    let ContextMenuActions = {};

    ContextMenuActions.ClearPageViaContextMenu = function (table, id) {
        let type = 0; // UNKNOWN
        if (table === 'pages')
            type = 1; // PAGE

        ContextMenuActions.ClearExternalCache(type, id);
    }

    /**
     * @param {int} type
     * @param id
     */
    ContextMenuActions.ClearExternalCache = function (type, id) {
        if (type > 0) {
            let errMsg = 'An error occurred while clearing the cache. It is likely not all caches were cleared as expected.';
            let errTitle = 'An error occurred';
            try {
                let request = new AjaxRequest(TYPO3.settings.ajaxUrls.external_cache_clear)
                    .withQueryArguments({id: id, type: type})
                    .get()
                    .then(async function (response) {
                        let res = await response.resolve();
                        try {
                            if (res.hasOwnProperty('status') && res.status) {
                                Typo3Notification.success('Cache Clear', 'Successful', 2);
                            } else if (res.hasOwnProperty('status') && !res.status) {
                                Typo3Notification.error(errTitle, res.message);
                            } else {
                                Typo3Notification.error(errTitle, errMsg);
                            }
                        } catch (err) {
                            Typo3Notification.error(errTitle, errMsg);
                        }
                    }).catch(e => {
                        Typo3Notification.error(errTitle, errMsg);
                    });
            } catch (e) {
                Typo3Notification.error(errTitle, errMsg);
            }
        }
    };

    return ContextMenuActions;
});