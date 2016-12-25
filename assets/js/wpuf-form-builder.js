;(function($) {
'use strict';

/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#wpuf-tmpl-form-fields',

    props: {
    },

    data: function () {
        return {

        };
    },

    computed: {

    },

    methods: {

    }
});

/**
 * Sidebar field options panel
 */
Vue.component('field-options', {
    template: '#wpuf-tmpl-field-options',

    props: {
    },

    data: function () {
        return {

        };
    },

    computed: {

    },

    methods: {

    }
});

/**
 * Vue main instance for the form builder
 */

if (!$('#wpuf-form-builder').length) {
    return;
}

new Vue({
    el: '#wpuf-form-builder',

    data: {
        post: wpufFormBuilder.post,
        currentPanel: 'form-fields'
    },

    mounted: function () {
        // primary nav tabs and their contents
        this.bindTabOnClick($('#wpuf-form-builder > .nav-tab-wrapper > a'), '#wpuf-form-builder');

        // secondary settings tabs and their contents
        var settingsTabs = $('#wpuf-form-builder-settings .nav-tab'),
            settingsTabContents = $('#wpuf-form-builder-settings .tab-contents .group');

        settingsTabs.first().addClass('nav-tab-active');
        settingsTabContents.first().addClass('active');

        this.bindTabOnClick(settingsTabs, '#wpuf-form-builder-settings');
    },

    methods: {
        // tabs and their contents
        bindTabOnClick: function (tabs, scope) {
            tabs.on('click', function (e) {
                e.preventDefault();

                var button = $(this),
                    tabContents = $(scope + ' > .tab-contents'),
                    groupId = button.attr('href');

                button.addClass('nav-tab-active').siblings('.nav-tab-active').removeClass('nav-tab-active');

                tabContents.children().removeClass('active');
                $(groupId).addClass('active');
            });
        },

        saveFormBuilder: function () {
            console.log('form submitted!!!');
        }
    }
});

})(jQuery);
