/**
 * Sidebar field options panel
 */
Vue.component('field-options', {
    template: '#tmpl-wpuf-field-options',

    data: function() {
        return {
            show_basic_settings: true,
            show_advanced_settings: false
        };
    },

    computed: {
        editing_field_id: function () {
            this.show_basic_settings = true;
            this.show_advanced_settings = false;

            return parseInt(this.$store.state.editing_field_id);
        },

        editing_form_field: function () {
            var self = this;
            return _.find(this.$store.state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(self.editing_field_id);
            });
        },

        settings: function() {
            var settings = this.$store.state.field_settings[this.editing_form_field.template].settings;

            return _.sortBy(settings, function (item) {
                return parseInt(item.priority);
            });
        },

        basic_settings: function () {
            return this.settings.filter(function (item) {
                return 'basic' === item.section;
            });
        },

        advanced_settings: function () {
            return this.settings.filter(function (item) {
                return 'advanced' === item.section;
            });
        },

        form_field_type_title: function() {
            return this.$store.state.field_settings[this.editing_form_field.template].title;
        }
    }
});
