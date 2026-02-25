Vue.component('builder-stage-v4-1', {
    template: '#tmpl-wpuf-builder-stage-v4-1',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage).concat(wpuf_mixins.add_form_field),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        hidden_fields: function () {
            return this.$store.state.form_fields.filter(function (item) {
                return 'custom_hidden_field' === item.template;
            });
        },

        editing_form_id: function () {
            return this.$store.state.editing_field_id;
        },
    },

    mounted: function () {
        var self = this,
            in_column_field = false;

        // bind jquery ui sortable
        $('#form-preview-stage, #form-preview-stage .wpuf-form.sortable-list').sortable({
            placeholder: 'form-preview-stage-dropzone',
            items: '.field-items',
            handle: '.field-buttons .move',
            scroll: true,
            over: function() {
                in_column_field = false;

                // if the field drop in column field, then stop field rendering in the builder stage
                $(".wpuf-column-inner-fields" ).on( "drop", function(event) {
                    var targetColumn = event.currentTarget.classList,
                        isColumnExist = $.inArray(".wpuf-column-inner-fields", targetColumn);

                    if ( isColumnExist ) {
                        in_column_field = true;
                    }
                } );
            },
            update: function (e, ui) {
                var item    = ui.item[0],
                    data    = item.dataset,
                    source  = data.source,
                    toIndex = parseInt($(ui.item).index()),
                    payload = {
                        toIndex: toIndex
                    };

                if ('panel' === source) {
                    // add new form element
                    self.$store.state.index_to_insert = parseInt(toIndex);

                    if ( ! in_column_field ) {
                        var field_template  = ui.item[0].dataset.formField;
                        self.add_form_field(field_template);
                    }

                    // remove button from stage
                    $(this).find('.wpuf-field-button').remove();

                } else if ('stage' === source) {
                    payload.fromIndex = parseInt(data.index);

                    self.$store.commit('swap_form_field_elements', payload);
                }

            }
        });
    },

    methods: {
        open_field_settings: function(field_id) {
            this.$store.commit('open_field_settings', field_id);
        },

        clone_field: function(field_id, index) {
            var payload = {
                field_id: field_id,
                index: index,
                new_id: this.get_random_id()
            };

            // single instance checking
            var field = _.find(this.$store.state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(payload.field_id);
            });

            // check if these are already inserted
            if ( this.isSingleInstance( field.template ) && this.containsField( field.template ) ) {
                Swal.fire({
                    title: "Oops...",
                    text: "You already have this field in the form"
                });
                return;
            }

            this.$store.commit('clone_form_field_element', payload);
        },

        delete_field: function(index) {
            var self = this;
            const icon_delete  = wpuf_admin_script.asset_url + '/images/delete-icon-rounded.svg';
            const delete_icon_html = '<img src="' + icon_delete + '" alt="delete">';

            (Swal.fire({
                title: self.i18n.delete_field_warn_title,
                html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' +  self.i18n.delete_field_warn_msg + '</span>',
                iconHtml: delete_icon_html,
                showCancelButton: true,
                confirmButtonText: self.i18n.yes_delete_it,
                cancelButtonText: self.i18n.no_cancel_it,
                cancelButtonColor: '#fff',
                confirmButtonColor: '#EF4444',
                reverseButtons: true
            })).then((result) => {
                if (result.isConfirmed) {
                    self.$store.commit('delete_form_field_element', index);
                }
            });
        },

        delete_hidden_field: function (field_id) {
            var i = 0;

            for (i = 0; i < this.form_fields.length; i++) {
                if (parseInt(field_id) === parseInt(this.form_fields[i].id)) {
                    this.delete_field(i);
                }
            }
        },

        is_pro_feature: function (template) {
            return ( this.field_settings[template] && this.field_settings[template].pro_feature ) ? true : false;
        },

        is_template_available: function (field) {
            var template = field.template;

            if (this.field_settings[template]) {
                if (this.is_pro_preview(template)) {
                    return false;
                }

                return true;
            }

            // for example see 'mixin_builder_stage' mixin's 'is_taxonomy_template_available' method
            if (_.isFunction(this['is_' + template + '_template_available'])) {
                return this['is_' + template + '_template_available'].call(this, field);
            }

            return false;
        },

        is_full_width: function (template) {
            if (this.field_settings[template] && this.field_settings[template].is_full_width) {
                return true;
            }

            return false;
        },

        is_invisible: function (field) {
            return ( field.recaptcha_type && 'invisible_recaptcha' === field.recaptcha_type ) ? true : false;
        },

        get_field_name: function (template) {
            return this.field_settings[template].title;
        },

        openRepeatFieldPicker(fieldId) {
            // Find the repeat field component by ref and call openFieldPicker()
            const refName = 'repeatFieldComponent_' + fieldId;
            const comp = this.$refs[refName];
            // Vue 2: $refs[refName] is an array if used in v-for, so get first
            if (Array.isArray(comp) && comp.length > 0) {
                comp[0].openFieldPicker();
            } else if (comp && typeof comp.openFieldPicker === 'function') {
                comp.openFieldPicker();
            }
        },

        hiddenClasses: function() {
            return [
                'hidden',           // Tailwind: display: none
                'wpuf_hidden_field',
                'screen-reader-text'
            ];
        },

        /**
         * Filter CSS classes to prevent hiding fields in the builder
         * Removes classes that would make the field invisible or hidden in the backend
         * while preserving them for frontend rendering
         *
         * @param {string} cssClasses - Space-separated CSS class names
         * @return {string} Filtered CSS classes safe for builder
         */
        filter_builder_css_classes: function(cssClasses) {
            if (!cssClasses || typeof cssClasses !== 'string') {
                return '';
            }

            // Split classes, filter out forbidden ones, and rejoin
            var classes = cssClasses.split(/\s+/).filter(function(className) {
                return className && this.hiddenClasses().indexOf(className.toLowerCase()) === -1;
            }.bind(this));

            return classes.join(' ');
        },

        /**
         * Check if field has CSS classes that would hide it on the frontend
         * Used to display a visual indicator in the builder
         *
         * @param {string} cssClasses - Space-separated CSS class names
         * @return {boolean} True if field has hiding CSS classes
         */
        has_hidden_css_class: function(cssClasses) {
            if (!cssClasses || typeof cssClasses !== 'string') {
                return false;
            }

            var hiddenClasses = this.hiddenClasses();
            var classes = cssClasses.toLowerCase().split(/\s+/);

            for (var i = 0; i < hiddenClasses.length; i++) {
                if (classes.indexOf(hiddenClasses[i]) !== -1) {
                    return true;
                }
            }

            return false;
        },
    }
});
