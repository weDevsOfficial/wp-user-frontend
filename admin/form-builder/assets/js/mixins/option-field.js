/**
 * Mixin for option fields like
 * field-text, field-text-meta, field-radio etc
 */
wpuf_mixins.option_field_mixin = {
    props: {
        option_field: {
            type: Object,
            default: {}
        },

        editing_form_field: {
            type: Object,
            default: {}
        }
    },

    methods: {
        update_value: function(property, value) {
            this.$store.commit('update_editing_form_field', {
                editing_field_id: this.editing_form_field.id,
                field_name: property,
                value: value
            });
        },
    }
};
