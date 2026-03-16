/**
 * User Directory Free - Profile File Tabs
 *
 * Handles tab switching for grouped file types on the user profile files tab.
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since WPUF_SINCE
 */

(function() {
    'use strict';

    function initFileTabs() {
        var tabButtons = document.querySelectorAll('.wpuf-file-tab-btn-2');
        var fileGroups = document.querySelectorAll('.wpuf-file-group-2');

        if (!tabButtons.length || !fileGroups.length) {
            return;
        }

        tabButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                var targetType = this.getAttribute('data-tab');

                // Update button states
                tabButtons.forEach(function(btn) {
                    btn.classList.remove('!wpuf-border-b-green-500', '!wpuf-text-green-600');
                    btn.classList.add('!wpuf-border-b-transparent', '!wpuf-text-green-500');
                    btn.removeAttribute('data-active');
                });

                this.classList.remove('!wpuf-border-b-transparent', '!wpuf-text-green-500');
                this.classList.add('!wpuf-border-b-green-500', '!wpuf-text-green-600');
                this.setAttribute('data-active', 'true');

                // Show/hide file groups
                fileGroups.forEach(function(group) {
                    if (group.getAttribute('data-type') === targetType) {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                    }
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFileTabs);
    } else {
        initFileTabs();
    }
})();
