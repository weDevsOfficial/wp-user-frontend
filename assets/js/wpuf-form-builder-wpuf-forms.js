;(function($) {
    'use strict';

    /**
     * Only proceed if current page is a 'Post Forms' form builder page
     */
    if (!$('#wpuf-form-builder.wpuf-form-builder-post').length) {
        return;
    }

    window.mixin_builder_stage = {
        data: function () {
            return {
                post_form_settings: {
                    submit_text: '',
                    draft_post: false,
                }
            };
        },

        mounted: function () {
            var self = this;

            // submit button text
            this.post_form_settings.submit_text = $('[name="wpuf_settings[submit_text]"]').val();

            $('[name="wpuf_settings[submit_text]"]').on('change', function () {
                self.post_form_settings.submit_text = $(this).val();
            });

            // draft post text
            this.post_form_settings.draft_post = $('[type="checkbox"][name="wpuf_settings[draft_post]"]').is(':checked') ? true : false;
            $('[type="checkbox"][name="wpuf_settings[draft_post]"]').on('change', function () {
                self.post_form_settings.draft_post = $(this).is(':checked') ? true : false;
            });
        }
    };
})(jQuery);
