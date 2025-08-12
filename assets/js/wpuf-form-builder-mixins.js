;(function($) {
'use strict';


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
                    title: '<span class="wpuf-text-primary">Oops...</span>',
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
                    commonClasses = 'file-selector wpuf-rounded-[6px] wpuf-btn-secondary';
                    break;

                case 'radio':
                    commonClasses = '!wpuf-mt-0 !wpuf-mr-2 wpuf-radio !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:checked:!wpuf-shadow-none focus:wpuf-shadow-primary';
                    break;

                case 'checkbox':
                    commonClasses = '!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded';
                    break;

                case 'dropdown':
                    commonClasses = 'wpuf-block wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-normal !wpuf-leading-none !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 !wpuf-text-base !leading-6';
                    break;

                default:
                    commonClasses = 'wpuf-block wpuf-min-w-full !wpuf-m-0 !wpuf-leading-none !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] wpuf-max-w-full focus:!wpuf-ring-transparent';
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

/**
 * Global mixin
 */
Vue.mixin({
    computed: {
        i18n: function () {
            return wpuf_form_builder.i18n;
        },

        is_pro_active: function () {
            return wpuf_form_builder.is_pro_active === '1';
        },

        pro_link: function () {
            return wpuf_form_builder.pro_link;
        }
    },

    methods: {
        get_random_id: function() {
            var min = 999999,
                max = 9999999999;

            return Math.floor(Math.random() * (max - min + 1)) + min;
        },

        warn: function (settings, callback) {
            settings = $.extend(true, {
                title: '',
                text: '',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d54e21',
                confirmButtonText: this.i18n.ok,
                cancelButtonText: this.i18n.cancel,
            }, settings);

            Swal.fire(settings, callback);
        },

        is_failed_to_validate: function (template) {
            var validator = this.field_settings[template] ? this.field_settings[template].validator : false;

            if (validator && validator.callback && !this[validator.callback]()) {
                return true;
            }

            return false;
        },

        has_recaptcha_api_keys: function () {
            return (wpuf_form_builder.recaptcha_site && wpuf_form_builder.recaptcha_secret) ? true : false;
        },

        has_turnstile_api_keys: function () {
            return wpuf_form_builder.turnstile_site && wpuf_form_builder.turnstile_secret;
        },

        containsField: function(field_name) {
            var self = this,
                i = 0;

            for (i = 0; i < self.$store.state.form_fields.length; i++) {
                // check if the single instance field exist in normal fields
                if (self.$store.state.form_fields[i].template === field_name) {
                    return true;
                }

                if (self.$store.state.form_fields[i].name === field_name) {
                    return true;
                }

                // check if the single instance field exist in column fields
                if (self.$store.state.form_fields[i].template === 'column_field') {
                    var innerColumnFields = self.$store.state.form_fields[i].inner_fields;

                    for (const columnFields in innerColumnFields) {
                        if (innerColumnFields.hasOwnProperty(columnFields)) {
                            var columnFieldIndex = 0;

                            while (columnFieldIndex < innerColumnFields[columnFields].length) {
                                if (innerColumnFields[columnFields][columnFieldIndex].template === field_name) {
                                    return true;
                                }
                                columnFieldIndex++;
                            }
                        }
                    }
                }

            }

            return false;
        },

        isSingleInstance: function(field_name) {
            let singleInstance = wpuf_single_objects;

            for( let instance of singleInstance ) {
                if ( field_name === instance ) {
                    return true;
                }
            }
            return false;
        }
    }
});

/**
 * Integration mixin
 *
 * @type {Object}
 */
wpuf_mixins.integration_mixin = {
    props: {
        id: String
    },

    computed: {

        integrations: function() {
            return wpuf_form_builder.integrations;
        },

        store: function() {
            return this.$store.state.integrations;
        },

        settings: function() {
            // find settings in store, otherwise take from default integration settings
            if ( this.store[this.id] ) {
                return this.store[this.id];
            }

            // we dont't have this on store, insert the default one
            // and return it. It happens only for the first time
            var defaultSettings = this.getIntegration(this.id).settings;

            this.$store.commit('updateIntegration', {
                index: this.id,
                value: defaultSettings
            });

            return defaultSettings;
        },
    },

    methods: {

        getIntegration: function(id) {
            return this.integrations[id];
        },

        insertValue: function(type, field, prop) {
            var value = ( field !== undefined ) ? '{' + type + ':' + field + '}' : '{' + type + '}';

            this.settings[prop] = this.settings[prop] + value;
        }
    }
};

/**
 * Mixin for option fields like
 * field-text, field-text-meta, field-radio etc
 */
wpuf_mixins.option_field_mixin = {
    props: {
        option_field: {
            type: Object,
            default: () => ({ key: 'value' })
        },

        editing_form_field: {
            type: Object,
            default: () => ({ key: 'value' })
        }
    },

    computed: {
        // show/hide on basis of depenedent settings
        met_dependencies: function () {
            // no 'dependencies' key
            if (!this.option_field.hasOwnProperty('dependencies')) {
                return true;
            }

            var deps = Object.keys(this.option_field.dependencies),
                i    = 0;

            // has 'dependencies' key, but no property is set
            if (!deps.length) {
                return true;
            }

            // check if dependencies met
            for (i = 0; i < deps.length; i++) {
                var required_dep_value  = this.option_field.dependencies[ deps[i] ],
                    editing_field_value = this.editing_form_field[ deps[i] ];

                if ( required_dep_value instanceof Array && required_dep_value.includes( editing_field_value ) ) {
                    return true;
                }

                if ( required_dep_value !== editing_field_value ) {
                    return false;
                }
            }

            return true;
        }
    },

    methods: {
        update_value: function(property, value) {
            this.$store.commit('update_editing_form_field', {
                editing_field_id: this.editing_form_field.id,
                field_name: property,
                value: value
            });
        },
    }
};

})(jQuery);
