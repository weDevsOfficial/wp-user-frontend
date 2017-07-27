;(function($) {
'use strict';

/**
 * Mixin for form fields like
 * form-text_field, form-field_textarea etc
 */
wpuf_mixins.form_field_mixin = {
    props: {
        field: {
            type: Object,
            default: {}
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
        }
    },

    methods: {
        class_names: function(type_class) {
            return [
                type_class,
                this.required_class(),
                'wpuf_' + this.field.name + '_' + this.form_id
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

            swal(settings, callback);
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

        containsField: function(field_name) {
            var i = 0;

            for (i = 0; i < this.$store.state.form_fields.length; i++) {
                if (this.$store.state.form_fields[i].name === field_name) {
                    return true;
                }
            }

            return false;
        },

        isSingleInstance: function(field_name) {
            var singleInstance = ['post_title', 'post_content', 'post_excerpt', 'featured_image',
                'user_login', 'first_name', 'last_name', 'nickname', 'user_email', 'user_url',
                'user_bio', 'password', 'user_avatar', 'taxonomy'];

            if ( $.inArray(field_name, singleInstance) >= 0 ) {
                return true;
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
            default: {}
        },

        editing_form_field: {
            type: Object,
            default: {}
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

                if (required_dep_value !== editing_field_value) {
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
