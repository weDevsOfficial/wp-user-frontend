Vue.component('field-text-meta', {
    template: '#tmpl-wpuf-field-text-meta',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                // Meta keys must be valid identifiers: lowercase, no spaces or
                // special characters (mirrors the auto-generated key). Prevents
                // invalid keys like "radio button" that break the field wrapper
                // CSS class and the input id / label "for" association.
                if ('string' === typeof value) {
                    value = value.replace(/\W/g, '_').toLowerCase();
                }

                this.update_value(this.option_field.name, value);
            }
        }
    },

    created: function () {
        if ('yes' === this.editing_form_field.is_meta) {
            if (!this.value) {
                this.value = this.editing_form_field.label.replace(/\W/g, '_').toLowerCase();
            }

            wpuf_form_builder.event_hub.$on('field-text-keyup', this.meta_key_autocomplete);
        }
    },

    methods: {
        meta_key_autocomplete: function (e, label_vm) {
            if (
                'label' === label_vm.option_field.name &&
                parseInt(this.editing_form_field.id) === parseInt(label_vm.editing_form_field.id)
            ) {
                this.value = label_vm.value.replace(/\W/g, '_').toLowerCase();
            }
        }
    }
});
