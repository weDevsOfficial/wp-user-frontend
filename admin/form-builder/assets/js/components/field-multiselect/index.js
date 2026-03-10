Vue.component('field-multiselect', {
    template: '#tmpl-wpuf-field-multiselect',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                if ( ! value ) {
                    value = [];
                }

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        },

        // Dynamic options for taxonomy fields
        dynamic_options: function () {
            // Check if this is a Selection Terms field for a taxonomy
            if (this.option_field.name === 'exclude' && 
                this.editing_form_field && 
                this.editing_form_field.input_type === 'taxonomy' &&
                this.editing_form_field.name) {
                
                var taxonomy_name = this.editing_form_field.name;
                
                // Look for terms in the wp_post_types data
                if (wpuf_form_builder && wpuf_form_builder.wp_post_types) {
                    for (var post_type in wpuf_form_builder.wp_post_types) {
                        var taxonomies = wpuf_form_builder.wp_post_types[post_type];
                        
                        if (taxonomies && taxonomies.hasOwnProperty(taxonomy_name)) {
                            var tax_field = taxonomies[taxonomy_name];
                            
                            if (tax_field && tax_field.terms && tax_field.terms.length > 0) {
                                var options = {};
                                tax_field.terms.forEach(function(term) {
                                    if (term && term.term_id && term.name) {
                                        options[term.term_id] = term.name;
                                    }
                                });
                                return options;
                            }
                        }
                    }
                }
            }
            
            // Return original options if not a taxonomy field or no dynamic options found
            return this.option_field.options || {};
        }
    },

    mounted: function () {
        this.bind_selectize();
    },

    watch: {
        dynamic_options: function () {
            // Refresh selectize when options change
            this.$nextTick(function () {
                this.bind_selectize();
            });
        }
    },

    methods: {
        bind_selectize: function () {
            var self = this;

            // Destroy existing selectize if it exists
            var $select = $(this.$el).find('.term-list-selector');
            if ($select[0] && $select[0].selectize) {
                $select[0].selectize.destroy();
            }

            $select.selectize({}).on('change', function () {
                self.value = $( this ).val();
            });
        },
    },

});
