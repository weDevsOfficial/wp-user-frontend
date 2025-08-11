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
        },

        isSocialField: function(fieldTemplate) {
            return fieldTemplate && fieldTemplate.endsWith('_url') && this.getSocialIcon(fieldTemplate) !== '';
        },

        getSocialIcon: function(socialType) {
            if (!socialType || !socialType.endsWith('_url')) {
                return '';
            }

            const socialIconsRegistry = {
                twitter_url: {
                    icon: '<svg class="wpuf-twitter-svg" width="20" height="25" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="X (Twitter)" role="img"><path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    name: 'Twitter'
                },
                facebook_url: {
                    icon: '<svg class="wpuf-facebook-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Facebook" role="img"><path d="M14.1061 6.68815H11.652C10.7822 6.68815 10.0752 7.3899 10.0688 8.25975L9.99768 17.8552M8.40234 11.6676H12.4046M2.08398 9.9987C2.08398 6.26675 2.08398 4.40077 3.24335 3.2414C4.40273 2.08203 6.2687 2.08203 10.0007 2.08203C13.7326 2.08203 15.5986 2.08203 16.758 3.2414C17.9173 4.40077 17.9173 6.26675 17.9173 9.9987C17.9173 13.7306 17.9173 15.5966 16.758 16.756C15.5986 17.9154 13.7326 17.9154 10.0007 17.9154C6.2687 17.9154 4.40273 17.9154 3.24335 16.756C2.08398 15.5966 2.08398 13.7306 2.08398 9.9987Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    name: 'Facebook'
                },
                linkedin_url: {
                    icon: '<svg class="wpuf-linkedin-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="LinkedIn" role="img"><path d="M4.83398 7.33203V13.1654M8.16732 9.83203V13.1654M8.16732 9.83203C8.16732 8.45128 9.28657 7.33203 10.6673 7.33203C12.0481 7.33203 13.1673 8.45128 13.1673 9.83203V13.1654M8.16732 9.83203V7.33203M4.84066 4.83203H4.83317M1.08398 8.9987C1.08398 5.26675 1.08398 3.40077 2.24335 2.2414C3.40273 1.08203 5.2687 1.08203 9.00065 1.08203C12.7326 1.08203 14.5986 1.08203 15.758 2.2414C16.9173 3.40077 16.9173 5.26675 16.9173 8.9987C16.9173 12.7306 16.9173 14.5966 15.758 15.756C14.5986 16.9154 12.7326 16.9154 9.00065 16.9154C5.2687 16.9154 3.40273 16.9154 2.24335 15.756C1.08398 14.5966 1.08398 12.7306 1.08398 8.9987Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    name: 'LinkedIn'
                },
                instagram_url: {
                    icon: '<svg class="wpuf-instagram-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Instagram" role="img"><path d="M2.24335 2.2414L1.71302 1.71107L1.71302 1.71107L2.24335 2.2414ZM15.758 2.2414L16.2883 1.71108L16.2883 1.71106L15.758 2.2414ZM15.758 15.756L16.2883 16.2864L16.2883 16.2863L15.758 15.756ZM2.24335 15.756L1.71301 16.2864L1.71303 16.2864L2.24335 15.756ZM13.5905 5.16536C14.0047 5.16536 14.3405 4.82958 14.3405 4.41536C14.3405 4.00115 14.0047 3.66536 13.5905 3.66536V5.16536ZM13.583 3.66536C13.1688 3.66536 12.833 4.00115 12.833 4.41536C12.833 4.82958 13.1688 5.16536 13.583 5.16536V3.66536ZM1.08398 8.9987H1.83398C1.83398 7.11152 1.83558 5.77159 1.97222 4.75527C2.10596 3.76052 2.35657 3.18884 2.77368 2.77173L2.24335 2.2414L1.71302 1.71107C0.97076 2.45333 0.641695 3.39432 0.485593 4.5554C0.332392 5.6949 0.333984 7.15392 0.333984 8.9987H1.08398ZM2.24335 2.2414L2.77368 2.77173C3.19079 2.35462 3.76248 2.104 4.75722 1.97026C5.77354 1.83362 7.11347 1.83203 9.00065 1.83203V1.08203V0.332031C7.15588 0.332031 5.69685 0.330438 4.55735 0.48364C3.39627 0.639742 2.45529 0.968807 1.71302 1.71107L2.24335 2.2414ZM9.00065 1.08203V1.83203C10.8878 1.83203 12.2277 1.83362 13.2441 1.97026C14.2388 2.104 14.8105 2.35462 15.2277 2.77174L15.758 2.2414L16.2883 1.71106C15.546 0.968806 14.605 0.639742 13.4439 0.48364C12.3044 0.330438 10.8454 0.332031 9.00065 0.332031V1.08203ZM15.758 2.2414L15.2276 2.77172C15.6447 3.18883 15.8954 3.76052 16.0291 4.75526C16.1657 5.77158 16.1673 7.11152 16.1673 8.9987H16.9173H17.6673C17.6673 7.15392 17.6689 5.6949 17.5157 4.5554C17.3596 3.39433 17.0306 2.45334 16.2883 1.71108L15.758 2.2414ZM16.9173 8.9987H16.1673C16.1673 10.8859 16.1657 12.2258 16.0291 13.2421C15.8954 14.2369 15.6447 14.8086 15.2276 15.2257L15.758 15.756L16.2883 16.2863C17.0306 15.5441 17.3596 14.6031 17.5157 13.442C17.6689 12.3025 17.6673 10.8435 17.6673 8.9987H16.9173ZM15.758 15.756L15.2277 15.2257C14.8105 15.6428 14.2388 15.8934 13.2441 16.0271C12.2277 16.1638 10.8878 16.1654 9.00065 16.1654V16.9154V17.6654C10.8454 17.6654 12.3044 17.667 13.4439 17.5138C14.605 17.3577 15.546 17.0286 16.2883 16.2864L15.758 15.756ZM9.00065 16.9154V16.1654C7.11347 16.1654 5.77354 16.1638 4.75722 16.0271C3.76247 15.8934 3.19078 15.6428 2.77367 15.2257L2.24335 15.756L1.71303 16.2864C2.4553 17.0286 3.39628 17.3577 4.55735 17.5138C5.69685 17.667 7.15588 17.6654 9.00065 17.6654V16.9154ZM2.24335 15.756L2.77369 15.2257C2.35658 14.8086 2.10596 14.2369 1.97222 13.2421C1.83558 12.2258 1.83398 10.8859 1.83398 8.9987H1.08398H0.333984C0.333984 10.8435 0.332392 12.3025 0.485593 13.442C0.641695 14.6031 0.970759 15.5441 1.71301 16.2864L2.24335 15.756ZM12.7507 8.9987H12.0007C12.0007 10.6556 10.6575 11.9987 9.00065 11.9987V12.7487V13.4987C11.4859 13.4987 13.5007 11.484 13.5007 8.9987H12.7507ZM9.00065 12.7487V11.9987C7.3438 11.9987 6.00065 10.6556 6.00065 8.9987H5.25065H4.50065C4.50065 11.484 6.51537 13.4987 9.00065 13.4987V12.7487ZM5.25065 8.9987H6.00065C6.00065 7.34184 7.3438 5.9987 9.00065 5.9987V5.2487V4.4987C6.51537 4.4987 4.50065 6.51342 4.50065 8.9987H5.25065ZM9.00065 5.2487V5.9987C10.6575 5.9987 12.0007 7.34184 12.0007 8.9987H12.7507H13.5007C13.5007 6.51342 11.4859 4.4987 9.00065 4.4987V5.2487ZM13.5905 4.41536V3.66536H13.583V4.41536V5.16536H13.5905V4.41536Z" fill="#079669"/></svg>',
                    name: 'Instagram'
                },
                // Fallback pattern for future social platforms
                // Format: platform_url -> generates a generic icon with the platform name
            };

            if (socialIconsRegistry[socialType]) {
                return socialIconsRegistry[socialType].icon;
            }
        }
    }
});
