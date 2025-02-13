/**
 * Mixin for form fields like
 * form-text_field, form-field_textarea etc
 */
wpuf_mixins.form_field_mixin = {
    props: {
        field: {
            type: Object,
            default: () => ({ key: 'value' })
        }
    },

    computed: {
        form_id: function () {
            return this.$store.state.post.ID;
        },

        has_options: function () {
            if (!this.field.hasOwnProperty('options')) {
                return false;
            }

            return !!Object.keys(this.field.options).length;
        },
    },

    methods: {
        class_names: function(type_class) {
            return [
                type_class,
                this.required_class(),
                'wpuf_' + this.field.name + '_' + this.form_id
            ];
        },

        builder_class_names: function(type_class) {
            var commonClasses = '';

            switch (type_class) {
                case 'upload_btn':
                    commonClasses = 'file-selector  wpuf-rounded-md wpuf-btn-secondary';
                    break;

                case 'radio':
                    commonClasses = '!wpuf-mt-0 wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 checked:focus:!wpuf-bg-primary checked:hover:!wpuf-bg-primary checked:before:!wpuf-bg-white checked:!wpuf-bg-primary';
                    break;

                case 'checkbox':
                    commonClasses = 'wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 !wpuf-mt-0.5 checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none';
                    break;

                case 'dropdown':
                    commonClasses = 'wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300';
                    break;

                default:
                    commonClasses = 'wpuf-block wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300 wpuf-max-w-full';
            }

            return [
                type_class,
                this.required_class(),
                'wpuf_' + this.field.name + '_' + this.form_id,
                commonClasses
            ];
        },

        required_class: function () {
            return ('yes' === this.required) ? 'required' : '';
        },

        is_selected: function (label) {
            if (_.isArray(this.field.selected)) {
                if (_.indexOf(this.field.selected, label) >= 0) {
                    return true;
                }

            } else if (label === this.field.selected) {
                return true;
            }

            return false;
        }
    }
};
