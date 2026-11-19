// Ids of fields added in this builder session whose meta key still follows the label.
window.wpuf_meta_key_follows_label = window.wpuf_meta_key_follows_label || {};

Vue.component('field-text-meta', {
    template: '#tmpl-wpuf-field-text-meta',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        // The Events Calendar reads these exact, case-sensitive keys, so a field already mapped to one stays locked.
        is_integration_key: function () {
            var key = this.editing_form_field[this.option_field.name];

            return 'string' === typeof key && /^_(Event|Venue|Organizer)[A-Z]/.test(key);
        },

        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                // Meta keys must be valid identifiers: no spaces or special
                // characters. Case is kept, because integrations rely on
                // case-sensitive keys such as The Events Calendar's _EventCost.
                if ('string' === typeof value) {
                    value = value.replace(/\W/g, '_');
                }

                // A key typed by hand stops following the label.
                if (!this.setting_from_label) {
                    delete window.wpuf_meta_key_follows_label[this.editing_form_field.id];
                }

                this.update_value(this.option_field.name, value);
            }
        }
    },

    created: function () {
        if ('yes' === this.editing_form_field.is_meta) {
            // Only a field added in this session (or one without a key) follows its label.
            // Saved and template fields keep their key when the label changes.
            if (!this.value) {
                window.wpuf_meta_key_follows_label[this.editing_form_field.id] = true;
                this.set_from_label(this.editing_form_field.label);
            }

            wpuf_form_builder.event_hub.$on('field-text-keyup', this.meta_key_autocomplete);
        }
    },

    beforeDestroy: function () {
        wpuf_form_builder.event_hub.$off('field-text-keyup', this.meta_key_autocomplete);
    },

    methods: {
        set_from_label: function (label) {
            this.setting_from_label = true;
            this.value = String(label || '').replace(/\W/g, '_').toLowerCase();
            this.setting_from_label = false;
        },

        meta_key_autocomplete: function (e, label_vm) {
            if (
                'label' === label_vm.option_field.name &&
                parseInt(this.editing_form_field.id) === parseInt(label_vm.editing_form_field.id) &&
                window.wpuf_meta_key_follows_label[this.editing_form_field.id]
            ) {
                this.set_from_label(label_vm.value);
            }
        }
    }
});
