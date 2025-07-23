/**
 * Field template: Cloudflare Turnstile
 */
Vue.component('form-cloudflare_turnstile', {
    template: '#tmpl-wpuf-form-cloudflare_turnstile',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        has_turnstile_api_keys: function () {
            return wpuf_form_builder.turnstile_site && wpuf_form_builder.turnstile_secret;
        },

        no_api_keys_msg: function () {
            return wpuf_form_builder.field_settings.turnstile.validator.msg;
        },

        turnstile_image: function () {
            var base_url = wpuf_form_builder.asset_url + '/images/cloudflare-placeholder-';

            if (this.field.turnstile_theme === 'dark') {
                base_url += 'dark';
            } else {
                base_url += 'light';
            }

            if (this.field.turnstile_size === 'compact') {
                base_url += '-compact';
            }

            return base_url + '.png';
        }
    }
});
