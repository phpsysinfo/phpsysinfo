/* Bootstrap 4 for IE9 - v4.1.1            */
/* https://github.com/coliff/bootstrap-ie8 */

/**
 * Modified code based on remPolyfill.js (c) Nicolas Bouvrette https://github.com/nbouvrette/remPolyfill
 *
 * Customizations:
 *
 * 1) Added new method `addCallBackWhenReady` to perform callbacks once the polyfill has been applied (especially useful for
 *    onload scrolling events.
 * 2) Added REM support.
 *
 **/

/**
 * For browsers that do not support REM units, fallback to pixels.
 */
window.remPolyfill = {

    /**
     * Implement this script on a given element.
     *
     * @private
     *
     * @param {string} cssText              - The CSS text of the link element.
     */
    replaceCSS: function (cssText) {
        if (cssText) {
            // Replace all properties containing REM units with their pixel equivalents.
            return cssText.replace(
                /:invalid/g, '._invalid'
            ).replace(
                /:valid/g, '._valid'
            );
        }
    },

    /**
     * Implement this script on a given element.
     *
     * @param {HTMLLinkElement} linkElement - The link element to polyfill.
     */
    implement: function (linkElement) {
        if (!linkElement.href) {
            return;
        }

        var request = null;

        if (window.XMLHttpRequest) {
            request = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            try {
                request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (exception) {
                try {
                    request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (exception) {
                    request = null;
                }
            }
        }

        if (!request) {
            return;
        }

        request.open('GET', linkElement.href, true);
        request.onreadystatechange = function() {
            if ( request.readyState === 4 ) {
                linkElement.styleSheet.cssText = remPolyfill.replaceCSS(request.responseText);
            }
        };

        request.send(null);
    }
};

var linkElements = document.querySelectorAll('link[rel=stylesheet]');
for (var linkElementId in linkElements) {
    if (Object.prototype.hasOwnProperty.call(linkElements, linkElementId)) {
        remPolyfill.implement(linkElements[linkElementId]);
    }
}
