/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#tmpl-wpuf-form-fields',

    computed: {
        panel_sections: function () {
            return this.$store.state.panel_sections;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },
    },

    mounted: function () {
        // bind jquery ui draggable
        $(this.$el).find('.panel-form-field-buttons .button').draggable({
            connectToSortable: '#form-preview-stage .wpuf-form',
            helper: 'clone',
            revert: 'invalid',
            cancel: '',
        });
    },

    methods: {
        panel_toggle: function (index) {
            this.$store.commit('panel_toggle', index);
        },

        add_form_field: function (field_template) {
            var payload = {
                toIndex: this.$store.state.form_fields.length,
            };

            var field = $.extend(true, {}, this.$store.state.field_settings[field_template].field_props);

            field.id = this.get_random_id();

            payload.field = field;

            // add new form element
            this.$store.commit('add_form_field_element', payload);
        }
    }
});
