/**
 * User Directory Free - Frontend Scripts
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initUserDirectory();
    });

    /**
     * Initialize User Directory functionality
     */
    function initUserDirectory() {
        // Handle search form submission
        $('.wpuf-ud-search-form').on('submit', function(e) {
            var searchInput = $(this).find('.wpuf-ud-search-input');
            var searchValue = searchInput.val().trim();
            
            // If empty search, remove the parameter
            if (!searchValue) {
                e.preventDefault();
                window.location.href = removeUrlParam(window.location.href, 'udsearch');
            }
        });

        // Handle pagination with loading state
        $('.wpuf-ud-pagination a').on('click', function() {
            var $grid = $(this).closest('.wpuf-user-directory').find('.wpuf-ud-grid');
            $grid.css('opacity', '0.5');
        });
    }

    /**
     * Remove a URL parameter
     *
     * @param {string} url URL to modify
     * @param {string} param Parameter to remove
     * @return {string} Modified URL
     */
    function removeUrlParam(url, param) {
        var urlParts = url.split('?');
        
        if (urlParts.length < 2) {
            return url;
        }

        var params = new URLSearchParams(urlParts[1]);
        params.delete(param);
        
        var newParams = params.toString();
        
        if (newParams) {
            return urlParts[0] + '?' + newParams;
        }
        
        return urlParts[0];
    }

})(jQuery);
