Vue.component('field-visibility', {
    template: '#tmpl-wpuf-field-visibility',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    computed: {
        selected: {
            get: function () {

                return this.editing_form_field[this.option_field.name].selected;
            },

            set: function (value) {

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: {
                        selected: value,
                        choices: [],
                    }
                });
            }
        },

        choices: {
            get: function () {
                return this.editing_form_field[this.option_field.name].choices;
            },

            set: function (value) {

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: {
                        selected: this.selected,
                        choices: value,
                    }
                });
            }
        },

    },

    methods: {

    },

    watch: {

    }
});