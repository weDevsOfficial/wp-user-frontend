/**
 * Field template: Text
 */
Vue.component('form-text_field', {
    template: '#tmpl-wpuf-form-text_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    props: {
        field: {
            type: Object,
            default: {}
        }
    },

    computed: {
        form_id: function () {
            return this.$store.state.post.ID;
        },
    },
});
