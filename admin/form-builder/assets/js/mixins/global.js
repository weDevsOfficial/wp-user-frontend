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
