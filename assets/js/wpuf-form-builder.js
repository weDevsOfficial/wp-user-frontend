;(function($) {
'use strict';

/**
 * Sidebar field options panel
 */
Vue.component('field-options', {
    template: '#tmpl-wpuf-field-options',

    props: {
    },

    data: function () {
        return {
            hello: 'world'
        };
    },

    computed: {

    },

    methods: {

    }
});

/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#tmpl-wpuf-form-fields',

    props: {
    },

    data: function () {
        return {
            edi : 'amin'
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
        post: wpuf_form_builder.post,
        currentPanel: 'form-fields'
    },

    mounted: function () {
        // primary nav tabs and their contents
        this.bind_tab_on_click($('#wpuf-form-builder > .nav-tab-wrapper > a'), '#wpuf-form-builder');

        // secondary settings tabs and their contents
        var settings_tabs = $('#wpuf-form-builder-settings .nav-tab'),
            settings_tab_contents = $('#wpuf-form-builder-settings .tab-contents .group');

        settings_tabs.first().addClass('nav-tab-active');
        settings_tab_contents.first().addClass('active');

        this.bind_tab_on_click(settings_tabs, '#wpuf-form-builder-settings');
    },

    methods: {
        // tabs and their contents
        bind_tab_on_click: function (tabs, scope) {
            tabs.on('click', function (e) {
                e.preventDefault();

                var button = $(this),
                    tab_contents = $(scope + ' > .tab-contents'),
                    groupId = button.attr('href');

                button.addClass('nav-tab-active').siblings('.nav-tab-active').removeClass('nav-tab-active');

                tab_contents.children().removeClass('active');
                $(groupId).addClass('active');
            });
        },

        save_form_builder: function () {
            console.log('form submitted!!!');
        }
    }
});

})(jQuery);
