Vue.component('builder-stage', {
    template: '#tmpl-wpuf-builder-stage',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        }
    },

    mounted: function () {
        var self = this;

        // bind jquery ui sortable
        $('#form-preview-stage .wpuf-form').sortable({
            placeholder: 'form-preview-stage-dropzone',
            items: '.field-items',
            handle: '.control-buttons .move',
            scroll: true,
            update: function (event, ui) {
                var item    = ui.item[0],
                    data    = item.dataset,
                    source  = data.source,
                    toIndex = parseInt($(ui.item).index()),
                    payload = {
                        toIndex: toIndex
                    };

                if ('panel' === source) {
                    // prepare the payload to add new form element
                    var field_template  = ui.item[0].dataset.formField,
                        field           = $.extend(true, {}, self.field_settings[field_template].field_props);

                    // add a random integer id
                    field.id = self.get_random_id();

                    payload.field = field;

                    // add new form element
                    self.$store.commit('add_form_field_element', payload);

                    // remove button from stage
                    $(this).find('.button.ui-draggable.ui-draggable-handle').remove();

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

            this.$store.commit('clone_form_field_element', payload);
        },

        delete_field: function(index) {
            var self = this;

            self.warn({
                text: self.i18n.delete_field_warn_msg,
                confirmButtonText: self.i18n.yes_delete_it,
                cancelButtonText: self.i18n.no_cancel_it,
            }, function (is_confirm) {
                if (is_confirm) {
                    self.$store.commit('delete_form_field_element', index);
                }
            });
        },
    }
});
