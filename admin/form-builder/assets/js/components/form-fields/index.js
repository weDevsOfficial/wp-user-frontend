/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#tmpl-wpuf-form-fields',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.form_fields),

    computed: {
        panel_sections: function () {
            return this.$store.state.panel_sections;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        form_fields: function () {
            return this.$store.state.form_fields;
        }
    },

    mounted: function () {
        // bind jquery ui draggable
        $(this.$el).find('.panel-form-field-buttons .button').draggable({
            connectToSortable: '#form-preview-stage .wpuf-form, .wpuf-column-inner-fields .wpuf-column-fields-sortable-list',
            helper: 'clone',
            revert: 'invalid',
            cancel: '.button-faded',
        }).disableSelection();
    },

    methods: {
        panel_toggle: function (index) {
            this.$store.commit('panel_toggle', index);
        },

        add_form_field: function (field_template) {
            var payload = {
                toIndex: this.$store.state.form_fields.length,
            };

            // check if these are already inserted
            if ( this.isSingleInstance( field_template ) && this.containsField( field_template ) ) {
                swal({
                    title: "Oops...",
                    text: "You already have this field in the form"
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

        is_pro_feature: function (field) {
            return this.field_settings[field].pro_feature;
        },

        alert_pro_feature: function (field) {
            var title = this.field_settings[field].title;

            swal({
                title: '<i class="fa fa-lock"></i> ' + title + ' <br>' + this.i18n.is_a_pro_feature,
                text: this.i18n.pro_feature_msg,
                type: '',
                showCancelButton: true,
                cancelButtonText: this.i18n.close,
                confirmButtonColor: '#46b450',
                confirmButtonText: this.i18n.upgrade_to_pro
            }).then(function (is_confirm) {
                if (is_confirm) {
                    window.open(wpuf_form_builder.pro_link, '_blank');
                }

            }, function() {});
        },

        alert_invalidate_msg: function (field) {
            var validator = this.field_settings[field].validator;

            if (validator && validator.msg) {
                this.warn({
                    title: validator.msg_title || '',
                    html: validator.msg,
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#46b450',
                    confirmButtonText: this.i18n.ok
                });
            }
        },

        get_invalidate_btn_class: function (field) {
            return this.field_settings[field].validator.button_class;
        }
    }
});
