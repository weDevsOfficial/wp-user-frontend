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
                    commonClasses = 'file-selector wpuf-rounded-lg wpuf-btn-secondary';
                    break;

                case 'radio':
                    commonClasses = '!wpuf-mr-2 wpuf-radio !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary  focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:checked:!wpuf-shadow-none focus:wpuf-shadow-primary';
                    break;

                case 'checkbox':
                    commonClasses = 'wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 !wpuf-mt-0.5 checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none';
                    break;

                case 'dropdown':
                    commonClasses = 'wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-lg wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300';
                    break;

                default:
                    commonClasses = 'wpuf-block wpuf-min-w-full !wpuf-rounded-[6px] !wpuf-m-0 !wpuf-leading-none !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-900 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm wpuf-border !wpuf-border-gray-300 wpuf-max-w-full';
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
