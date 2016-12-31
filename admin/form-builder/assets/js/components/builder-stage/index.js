Vue.component('builder-stage', {
    template: '#tmpl-wpuf-builder-stage',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        }
    },

    methods: {
        open_field_settings: function(field_id) {
            this.$store.commit('open_field_settings', field_id);
        },

        clone_field: function(field_id) {
            console.log(field_id);
        },

        delete_field: function(field_id) {
            console.log(field_id);
        },
    }
});
