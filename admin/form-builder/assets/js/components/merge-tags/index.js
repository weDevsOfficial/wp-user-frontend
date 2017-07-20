Vue.component('wpuf-merge-tags', {
    template: '#tmpl-wpuf-merge-tags',
    props: {
        field: String,
        filter: {
            type: String,
            default: null
        }
    },

    data: function() {
        return {
            type: null,
        };
    },

    mounted: function() {

        // hide if clicked outside
        $('body').on('click', function(event) {
            if ( !$(event.target).closest('.wpuf-merge-tag-wrap').length) {
                $(".wpuf-merge-tags").hide();
            }
        });
    },

    computed: {
        form_fields: function () {
            var template = this.filter,
                fields = this.$store.state.form_fields;

            if (template !== null) {
                return fields.filter(function(item) {
                    return item.template === template;
                });
            }

            // remove the action/hidden fields
            return fields.filter(function(item) {
                return !_.contains( [ 'action_hook', 'custom_hidden_field'], item.template );
            });
        },
    },

    methods: {
        toggleFields: function(event) {
            $(event.target).parent().siblings('.wpuf-merge-tags').toggle('fast');
        },

        insertField: function(type, field) {
            this.$emit('insert', type, field, this.field);
        }
    }
});