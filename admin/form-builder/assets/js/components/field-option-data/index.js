/**
 * Common settings component for option based fields
 * like select, multiselect, checkbox, radio
 */
Vue.component('field-option-data', {
    template: '#tmpl-wpuf-field-option-data',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            show_value: false,
            sync_value: true,
            options: [],
            selected: [],
            display: !this.editing_form_field.hide_option_data, // hide this field for the events calendar
            show_ai_modal: false,
            show_ai_config_modal: false,
            ai_prompt: '',
            ai_loading: false,
            ai_error: '',
            ai_generated_options: []
        };
    },

    computed: {
        field_options: function () {
            return this.editing_form_field.options;
        },

        field_selected: function () {
            return this.editing_form_field.selected;
        },

        all_ai_selected: function () {
            return this.ai_generated_options.length > 0 && this.ai_generated_options.every(function(opt) {
                return opt.selected;
            });
        }
    },

    mounted: function () {
        var self = this;

        this.set_options();

        $(this.$el).find('.option-field-option-chooser').sortable({
            items: '.option-field-option',
            handle: '.sort-handler',
            update: function (e, ui) {
                var item        = ui.item[0],
                    data        = item.dataset,
                    toIndex     = parseInt($(ui.item).index()),
                    fromIndex   = parseInt(data.index);

                self.options.swap(fromIndex, toIndex);
            }
        });
    },

    methods: {
        set_options: function () {
            var self = this;
            var field_options = $.extend(true, {}, this.editing_form_field.options);

            _.each(field_options, function (label, value) {
                self.options.push({label: label, value: value, id: self.get_random_id()});
            });

            if (this.option_field.is_multiple && !_.isArray(this.field_selected)) {
                this.selected = [this.field_selected];
            } else {
                this.selected = this.field_selected;
            }
        },

        // in case of select or radio buttons, user should deselect default value
        clear_selection: function () {
            this.selected = null;
        },

        add_option: function () {
            var count   = this.options.length,
                new_opt = this.i18n.option + '-' + (count + 1);

            this.options.push({
                label: new_opt , value: new_opt, id: this.get_random_id()
            });
        },

        delete_option: function (index) {
            if (this.options.length === 1) {
                this.warn({
                    text: this.i18n.last_choice_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.options.splice(index, 1);
        },

        set_option_label: function (index, label) {
            if (this.sync_value) {
                this.options[index].value = label.toLocaleLowerCase().replace( /\s/g, '_' );
            }
        },

        open_ai_modal: function () {
            // Check if AI is configured
            if (!wpuf_form_builder.ai_configured) {
                this.show_ai_config_modal = true;
                return;
            }
            this.show_ai_modal = true;
            this.ai_prompt = '';
            this.ai_error = '';
            this.ai_generated_options = [];
        },

        close_ai_config_modal: function () {
            this.show_ai_config_modal = false;
        },

        go_to_ai_settings: function () {
            window.location.href = wpuf_form_builder.ai_settings_url;
        },

        close_ai_modal: function () {
            this.show_ai_modal = false;
            this.ai_prompt = '';
            this.ai_error = '';
            this.ai_generated_options = [];
            this.ai_loading = false;
        },

        generate_ai_options: function () {
            var self = this;

            if (!this.ai_prompt.trim()) {
                return;
            }

            this.ai_loading = true;
            this.ai_error = '';

            var field_type = this.editing_form_field.template;

            wp.ajax.post('wpuf_ai_generate_field_options', {
                prompt: this.ai_prompt,
                field_type: field_type,
                nonce: wpuf_form_builder.nonce
            }).done(function(response) {
                // wp.ajax.post returns data directly in response (not response.data)
                // when using wp_send_json_success(['options' => $options])
                var options = response.options || (response.data && response.data.options) || [];
                
                if (options.length > 0) {
                    var mapped_options = options.map(function(opt) {
                        return {
                            label: opt.label || opt,
                            value: opt.value || opt,
                            selected: true
                        };
                    });
                    self.$set(self, 'ai_generated_options', mapped_options);
                } else {
                    self.ai_error = response.message || (response.data && response.data.message) || self.i18n.something_went_wrong;
                }
            }).fail(function(error) {
                self.ai_error = error.message || self.i18n.something_went_wrong;
            }).always(function() {
                self.ai_loading = false;
            });
        },

        select_all_ai_options: function () {
            var select_state = !this.all_ai_selected;
            this.ai_generated_options.forEach(function(opt) {
                opt.selected = select_state;
            });
        },

        import_ai_options: function () {
            var self = this;
            var selected_options = this.ai_generated_options.filter(function(opt) {
                return opt.selected;
            });

            selected_options.forEach(function(opt) {
                self.options.push({
                    label: opt.label,
                    value: opt.value,
                    id: self.get_random_id()
                });
            });

            this.close_ai_modal();
        }
    },

    watch: {
        options: {
            deep: true,
            handler: function (new_opts) {
                var options = {},
                    i = 0;

                for (i = 0; i < new_opts.length; i++) {
                    options['' + new_opts[i].value] = new_opts[i].label;
                }

                this.update_value('options', options);
            }
        },

        selected: function (new_val) {
            this.update_value('selected', new_val);
        }
    }
});
