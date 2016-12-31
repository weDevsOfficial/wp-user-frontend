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
    }
};
