/**
 * Only proceed if current page is a form builder page
 */
if (!$('#wpuf-form-builder').length) {
    return;
}

/**
 * Vuex Store data
 */
var wpuf_form_builder_store = new Vuex.Store({
    state: {
        i18n: wpuf_form_builder.i18n,
        post: wpuf_form_builder.post,
        form_fields: wpuf_form_builder.form_fields,
        panel_sections: wpuf_form_builder.panel_sections,
        field_settings: wpuf_form_builder.field_settings,
        current_panel: 'form-fields',
        editing_field_id: 0, // editing form field id
    },

    mutations: {
        // set the current panel
        set_current_panel: function (state, panel) {
            if ('field-options' !== state.current_panel &&
                'field-options' === panel &&
                state.form_fields.length
            ) {
                state.editing_field_id = state.form_fields[0].id;
            }

            state.current_panel = panel;
        },

        // add show property to every panel section
        panel_add_show_prop: function (state) {
            state.panel_sections.map(function (section, index) {
                if (!section.hasOwnProperty('show')) {
                    Vue.set(state.panel_sections[index], 'show', true);
                }
            });
        },

        // toggle panel sections
        panel_toggle: function (state, index) {
            state.panel_sections[index].show = !state.panel_sections[index].show;
        },

        // open field settings panel
        open_field_settings: function (state, field_id) {
            var field = state.form_fields.filter(function(item) {
                return parseInt(field_id) === parseInt(item.id);
            });

            if (field.length) {
                state.editing_field_id = field[0].id;
                state.current_panel = 'field-options';
            }
        },

        // update editing field value
        update_editing_form_field: function (state, payload) {
            var editing_field = _.find(state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(payload.editing_field_id);
            });

            editing_field[payload.field_name] = payload.value;
        }
    }
});

/**
 * The main form builder vue instance
 */
new Vue({
    el: '#wpuf-form-builder',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.root_mixins),

    store: wpuf_form_builder_store,

    computed: {
        current_panel: function () {
            return this.$store.state.current_panel;
        },

        post: function () {
            return this.$store.state.post;
        },
    },

    created: function () {
        this.$store.commit('panel_add_show_prop');

        /**
         * This is the event hub we'll use in every
         * component to communicate between them
         */
        wpuf_form_builder.event_hub = new Vue();
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
                    group_id = button.attr('href');

                button.addClass('nav-tab-active').siblings('.nav-tab-active').removeClass('nav-tab-active');

                tab_contents.children().removeClass('active');
                $(group_id).addClass('active');
            });
        },

        // set current sidebar panel
        set_current_panel: function (panel) {
            this.$store.commit('set_current_panel', panel);
        },

        // save form builder data
        save_form_builder: function () {
            console.log('form submitted!!!');
        }
    }
});
