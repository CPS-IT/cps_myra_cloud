/**
 * Module: TYPO3/CMS/fr_myra_cloud/ContextMenuActions
 *
 * JavaScript to handle the click action of the "Hello World" context menu item
 * @exports TYPO3/CMS/fr_myra_cloud/ContextMenuActions
 */
define(['TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Notification'], function (AjaxRequest, Typo3Notification) {
    'use strict';

    /**
     * @exports TYPO3/CMS/fr_myra_cloud/ContextMenuActions
     */
    let ContextMenuActions = {};

    /**
     * @param {string} table
     * @param {int} uid of the page
     */
    ContextMenuActions.ClearExternalCache = function (table, uid) {
        if (table === 'pages') {
            try {
                let request = new AjaxRequest(TYPO3.settings.ajaxUrls.external_cache_clear)
                    .withQueryArguments({uid: uid})
                    .get()
                    .then(async function (response) {
                        let res = await response.resolve();
                        try {
                            if (res.hasOwnProperty('status') && res.status) {
                                Typo3Notification.success('Cache Clear', 'Successful', 2);
                            } else if (res.hasOwnProperty('status') && !res.status) {
                                Typo3Notification.error('Clear Cache', res.message);
                            } else {
                                Typo3Notification.error('Clear Cache', 'Internal TYPO3 Error');
                            }
                        } catch (err) {
                            Typo3Notification.error('Clear Cache', 'Unexpected Error');
                        }
                    }).catch(e => {
                        Typo3Notification.error('Clear Cache', 'Internal TYPO3 Error');
                    });
            } catch (e) {
                Typo3Notification.error('Clear Cache', 'Internal TYPO3 Error');
            }
        }
    };

    return ContextMenuActions;
});