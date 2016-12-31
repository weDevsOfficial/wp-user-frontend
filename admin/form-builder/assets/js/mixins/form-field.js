/**
 * Mixin for form fields like
 * field-text_field, field_textarea etc
 */
wpuf_mixins.form_field_mixin = {
    methods: {
        class_names: function(type_class) {
            return [
                type_class,
                this.required_class(),
                'wpuf_' + this.field.name + '_' + this.form_id
            ];
        },

        required_class: function () {
            return ('yes' === this.required) ? 'required' : '';
        },
    }
};
