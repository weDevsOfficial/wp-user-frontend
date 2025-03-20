
wpuf_mixins.add_form_field = {
    methods: {
        add_form_field: function (field_template) {
            var payload = {};
            var event_type = event.type;

            if( 'click' === event_type ){
                payload.toIndex = this.$store.state.index_to_insert === 0 ? this.$store.state.form_fields.length : this.$store.state.index_to_insert;
            }

            if ( 'mouseup' === event_type ){
                payload.toIndex = this.$store.state.index_to_insert === 0 ? 0 : this.$store.state.index_to_insert;
            }

            this.$store.state.index_to_insert = 0;

            // check if these are already inserted
            if ( this.isSingleInstance( field_template ) && this.containsField( field_template ) ) {
                Swal.fire({
                    title: '<span class="wpuf-text-orange-400">Oops...</span>',
                    html: '<p class="wpuf-text-gray-500 wpuf-text-xl wpuf-m-0 wpuf-p-0">You already have this field in the form</p>',
                    imageUrl: wpuf_form_builder.asset_url + '/images/oops.svg',
                    showCloseButton: true,
                    padding: '1rem',
                    width: '35rem',
                    customClass: {
                        confirmButton: "!wpuf-flex focus:!wpuf-shadow-none !wpuf-bg-primary",
                        closeButton: "wpuf-absolute"
                    },
                });
                return;
            }

            var field = $.extend(true, {}, this.$store.state.field_settings[field_template].field_props);

            field.id = this.get_random_id();

            if (!field.name && field.label) {
                field.name = field.label.replace(/\W/g, '_').toLowerCase();

                var same_template_fields = this.form_fields.filter(function (form_field) {
                   return (form_field.template === field.template);
                });

                if (same_template_fields.length) {
                    field.name += '_' + same_template_fields.length;
                }
            }

            payload.field = field;

            // add new form element
            this.$store.commit('add_form_field_element', payload);
        },

        is_pro_preview: function (template) {
            var is_pro_active = wpuf_form_builder.is_pro_active === '1';

            return (!is_pro_active && this.field_settings[template] && this.field_settings[template].pro_feature);
        },
    },

    computed: {
        action_button_classes: function() {
            return 'wpuf-p-2 hover:wpuf-cursor-pointer hover:wpuf-text-white wpuf-flex';
        }
    },
};
