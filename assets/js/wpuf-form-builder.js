;(function($) {
    'use strict';

    /**
     * Only proceed if current page is a form builder page
     */
    if (!$('#wpuf-form-builder').length) {
        return;
    }

    if (!Array.prototype.hasOwnProperty('swap')) {
        Array.prototype.swap = function (from, to) {
            this.splice(to, 0, this.splice(from, 1)[0]);
        };
    }

    // check if an element is visible in browser viewport
    function is_element_in_viewport (el) {
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }

        var rect = el.getBoundingClientRect();

        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
            rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
        );
    }

    /**
     * Vuex Store data
     */
    var wpuf_form_builder_store = new Vuex.Store({
        state: {
            post: wpuf_form_builder.post,
            form_fields: wpuf_form_builder.form_fields,
            panel_sections: wpuf_form_builder.panel_sections,
            field_settings: wpuf_form_builder.field_settings,
            notifications: wpuf_form_builder.notifications,
            current_panel: 'form-fields',
            editing_field_id: 0, // editing form field id
        },

        mutations: {
            set_form_fields: function (state, form_fields) {
                Vue.set(state, 'form_fields', form_fields);
            },

            // set the current panel
            set_current_panel: function (state, panel) {
                if ('field-options' !== state.current_panel &&
                    'field-options' === panel &&
                    state.form_fields.length
                ) {
                    state.editing_field_id = state.form_fields[0].id;
                }

                state.current_panel = panel;

                // reset editing field id
                if ('form-fields' === panel) {
                    state.editing_field_id = 0;
                }
            },

            // add show property to every panel section
            panel_add_show_prop: function (state) {
                state.panel_sections.map(function (section, index) {
                    if (!section.hasOwnProperty('show')) {
                        Vue.set(state.panel_sections[index], 'show', true);
                    }
                });
            },

            // toggle panel sections
            panel_toggle: function (state, index) {
                state.panel_sections[index].show = !state.panel_sections[index].show;
            },

            // open field settings panel
            open_field_settings: function (state, field_id) {
                var field = state.form_fields.filter(function(item) {
                    return parseInt(field_id) === parseInt(item.id);
                });

                if ('field-options' === state.current_panel && field[0].id === state.editing_field_id) {
                    return;
                }

                if (field.length) {
                    state.editing_field_id = 0;
                    state.current_panel = 'field-options';

                    setTimeout(function () {
                        state.editing_field_id = field[0].id;
                    }, 400);
                }
            },

            update_editing_form_field: function (state, payload) {
                var editing_field = _.find(state.form_fields, function (item) {
                    return parseInt(item.id) === parseInt(payload.editing_field_id);
                });

                editing_field[payload.field_name] = payload.value;
            },

            // add new form field element
            add_form_field_element: function (state, payload) {
                state.form_fields.splice(payload.toIndex, 0, payload.field);

                // bring newly added element into viewport
                Vue.nextTick(function () {
                    var el = $('#form-preview-stage .wpuf-form .field-items').eq(payload.toIndex);

                    if (el && !is_element_in_viewport(el.get(0))) {
                        $('#builder-stage section').scrollTo(el, 800, {offset: -50});
                    }
                });
            },

            // sorting inside stage
            swap_form_field_elements: function (state, payload) {
                state.form_fields.swap(payload.fromIndex, payload.toIndex);
            },

            clone_form_field_element: function (state, payload) {
                var field = _.find(state.form_fields, function (item) {
                    return parseInt(item.id) === parseInt(payload.field_id);
                });

                var clone = $.extend(true, {}, field),
                    index = parseInt(payload.index) + 1;

                clone.id   = payload.new_id;
                clone.name = clone.name + '_copy';

                state.form_fields.splice(index, 0, clone);
            },

            // delete a field
            delete_form_field_element: function (state, index) {
                state.current_panel = 'form-fields';
                state.form_fields.splice(index, 1);
            },

            // set fields for a panel section
            set_panel_section_fields: function (state, payload) {
                var section = _.find(state.panel_sections, function (item) {
                    return item.id === payload.id;
                });

                section.fields = payload.fields;
            },

            // notifications
            addNotification: function(state, payload) {
                state.notifications.push(payload);
            },

            deleteNotification: function(state, index) {
                state.notifications.splice(index, 1);
            },

            cloneNotification: function(state, index) {
                var clone = $.extend(true, {}, state.notifications[index]);

                index = parseInt(index) + 1;
                state.notifications.splice(index, 0, clone);
            },

            // update by it's property
            updateNotificationProperty: function(state, payload) {
                state.notifications[payload.index][payload.property] = payload.value;
            },

            updateNotification: function(state, payload) {
                state.notifications[payload.index] = payload.value;
            }
        }
    });

    /**
     * The main form builder vue instance
     */
    new Vue({
        el: '#wpuf-form-builder',

        mixins: wpuf_form_builder_mixins(wpuf_mixins.root),

        store: wpuf_form_builder_store,

        data: {
            is_form_saving: false,
            is_form_saved: false,
            is_form_switcher: false,
            post_title_editing: false,
            isDirty: false
        },

        computed: {
            current_panel: function () {
                return this.$store.state.current_panel;
            },

            post: function () {
                return this.$store.state.post;
            },

            form_fields_count: function () {
                return this.$store.state.form_fields.length;
            },

            form_fields: function () {
                return this.$store.state.form_fields;
            },

            notifications: function() {
                return this.$store.state.notifications;
            }
        },

        watch: {
            form_fields: {
                handler: function() {
                    this.isDirty = true;
                },
                deep: true
            }
        },

        created: function () {
            this.$store.commit('panel_add_show_prop');

            /**
             * This is the event hub we'll use in every
             * component to communicate between them
             */
            wpuf_form_builder.event_hub = new Vue();
        },

        mounted: function () {
            // primary nav tabs and their contents
            this.bind_tab_on_click($('#wpuf-form-builder > fieldset > .nav-tab-wrapper > a'), '#wpuf-form-builder');

            // secondary settings tabs and their contents
            var settings_tabs = $('#wpuf-form-builder-settings .nav-tab'),
                settings_tab_contents = $('#wpuf-form-builder-settings .tab-contents .group');

            settings_tabs.first().addClass('nav-tab-active');
            settings_tab_contents.first().addClass('active');

            this.bind_tab_on_click(settings_tabs, '#wpuf-form-builder-settings');

            var clipboard = new window.Clipboard('.form-id');
            $(".form-id").tooltip();

            var self = this;

            clipboard.on('success', function(e) {
                // Show copied tooltip
                $(e.trigger)
                    .attr('data-original-title', 'Copied!')
                    .tooltip('show');

                // Reset the copied tooltip
                setTimeout(function() {
                    $(e.trigger).tooltip('hide')
                    .attr('data-original-title', self.i18n.copy_shortcode);
                }, 1000);

                e.clearSelection();
            });

            window.onbeforeunload = function () {
                if ( self.isDirty ) {
                    return self.i18n.unsaved_changes;
                }
            };
        },

        methods: {
            // tabs and their contents
            bind_tab_on_click: function (tabs, scope) {
                tabs.on('click', function (e) {
                    e.preventDefault();

                    var button = $(this),
                        tab_contents = $(scope + ' > fieldset > .tab-contents'),
                        group_id = button.attr('href');

                    button.addClass('nav-tab-active').siblings('.nav-tab-active').removeClass('nav-tab-active');

                    tab_contents.children().removeClass('active');
                    $(group_id).addClass('active');
                });
            },

            // switch form
            switch_form: function () {
                this.is_form_switcher = (this.is_form_switcher) ? false : true;
            },

            // set current sidebar panel
            set_current_panel: function (panel) {
                this.$store.commit('set_current_panel', panel);
            },

            // save form builder data
            save_form_builder: function () {
                var self = this;

                if (_.isFunction(this.validate_form_before_submit) && !this.validate_form_before_submit()) {

                    this.warn({
                        text: this.validation_error_msg
                    });

                    return;
                }

                self.is_form_saving = true;
                self.set_current_panel('form-fields');

                wp.ajax.send('wpuf_form_builder_save_form', {
                    data: {
                        form_data: $('#wpuf-form-builder').serialize(),
                        form_fields: JSON.stringify(self.form_fields),
                        notifications: JSON.stringify(self.notifications)
                    },

                    success: function (response) {
                        if (response.form_fields) {
                            self.$store.commit('set_form_fields', response.form_fields);
                        }

                        self.is_form_saving = false;
                        self.is_form_saved = true;

                        setTimeout(function(){
                            self.isDirty = false;
                        }, 500);

                        toastr.success(self.i18n.saved_form_data);
                    },

                    error: function () {
                        self.is_form_saving = false;
                    }
                });
            }
        }
    });

    var SettingsTab = {
        init: function() {
            $(function() {
                $('.datepicker').datetimepicker();
                $('.wpuf-ms-color').wpColorPicker();
            });

            $('#wpuf-metabox-settings').on('change', 'select[name="wpuf_settings[redirect_to]"]', this.settingsRedirect);
            $('#wpuf-metabox-settings-update').on('change', 'select[name="wpuf_settings[edit_redirect_to]"]', this.settingsRedirect);
            $('select[name="wpuf_settings[redirect_to]"]').change();
            $('select[name="wpuf_settings[edit_redirect_to]"]').change();

            // Form settings: Payment
            $('#wpuf-metabox-settings-payment').on('change', 'input[type=checkbox][name="wpuf_settings[payment_options]"]', this.settingsPayment);
            $('input[type=checkbox][name="wpuf_settings[payment_options]"]').trigger('change');

            // pay per post
            $('#wpuf-metabox-settings-payment').on('change', 'input[type=checkbox][name="wpuf_settings[enable_pay_per_post]"]', this.settingsPayPerPost);
            $('input[type=checkbox][name="wpuf_settings[enable_pay_per_post]"]').trigger('change');

            // force pack purchase
            $('#wpuf-metabox-settings-payment').on('change', 'input[type=checkbox][name="wpuf_settings[force_pack_purchase]"]', this.settingsForcePack);
            $('input[type=checkbox][name="wpuf_settings[force_pack_purchase]"]').trigger('change');

            // Form settings: Submission Restriction

            // Form settings: Guest post
            $('#wpuf-metabox-submission-restriction').on('change', 'input[type=checkbox][name="wpuf_settings[guest_post]"]', this.settingsGuest);
            $('input[type=checkbox][name="wpuf_settings[guest_post]"]').trigger('change');
            $('#wpuf-metabox-submission-restriction').on('change', 'input[type=checkbox][name="wpuf_settings[role_base]"]', this.settingsRoles);
            $('input[type=checkbox][name="wpuf_settings[role_base]"]').trigger('change');

            // From settings: User details
            $('#wpuf-metabox-submission-restriction').on('change', 'input[type=checkbox][name="wpuf_settings[guest_details]"]', this.settingsGuestDetails);

            // From settings: schedule form
            $('#wpuf-metabox-submission-restriction').on('change', 'input[type=checkbox][name="wpuf_settings[schedule_form]"]', this.settingsRestriction);
            $('input[type=checkbox][name="wpuf_settings[schedule_form]"]').trigger('change');

            // From settings: limit entries
            $('#wpuf-metabox-submission-restriction').on('change', 'input[type=checkbox][name="wpuf_settings[limit_entries]"]', this.settingsLimit);
            $('input[type=checkbox][name="wpuf_settings[limit_entries]"]').trigger('change');

            this.changeMultistepVisibility($('.wpuf_enable_multistep_section :input[type="checkbox"]'));
            var self = this;
            $('.wpuf_enable_multistep_section :input[type="checkbox"]').click(function() {
                self.changeMultistepVisibility($(this));
            });
        },

        settingsGuest: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-guest').show();
                table.find('tr.show-if-not-guest').hide();

                $('input[type=checkbox][name="wpuf_settings[guest_details]"]').trigger('change');

            } else {
                table.find('tr.show-if-guest').hide();
                table.find('tr.show-if-not-guest').show();
            }
        },

        settingsRoles: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-roles').show();
            } else {
                table.find('tr.show-if-roles').hide();
            }
        },

        settingsGuestDetails: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-details').show();
            } else {
                table.find('tr.show-if-details').hide();
            }
        },

        settingsPayment: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-payment').show();
                table.find('tr.show-if-force-pack').hide();

            } else {
                table.find('tr.show-if-payment').hide();

            }
        },

        settingsPayPerPost: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-pay-per-post').show();

            } else {
                table.find('tr.show-if-pay-per-post').hide();

            }
        },

        settingsForcePack: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-force-pack').show();

            } else {
                table.find('tr.show-if-force-pack').hide();

            }
        },

        settingsRestriction: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-schedule').show();
            } else {
                table.find('tr.show-if-schedule').hide();

            }
        },

        settingsLimit: function (e) {
            e.preventDefault();

            var table = $(this).closest('table');

            if ( $(this).is(':checked') ) {
                table.find('tr.show-if-limit-entries').show();
            } else {
                table.find('tr.show-if-limit-entries').hide();

            }
        },

        settingsRedirect: function(e) {
            e.preventDefault();

            var $self = $(this),
                $table = $self.closest('table'),
                value = $self.val();

            switch( value ) {
                case 'post':
                    $table.find('tr.wpuf-page-id, tr.wpuf-url, tr.wpuf-same-page').hide();
                    break;

                case 'page':
                    $table.find('tr.wpuf-page-id').show();
                    $table.find('tr.wpuf-same-page').hide();
                    $table.find('tr.wpuf-url').hide();
                    break;

                case 'url':
                    $table.find('tr.wpuf-page-id').hide();
                    $table.find('tr.wpuf-same-page').hide();
                    $table.find('tr.wpuf-url').show();
                    break;

                case 'same':
                    $table.find('tr.wpuf-page-id').hide();
                    $table.find('tr.wpuf-url').hide();
                    $table.find('tr.wpuf-same-page').show();
                    break;
            }
        },

        changeMultistepVisibility: function(target) {
            if (target.is(':checked')) {
                $('.wpuf_multistep_content').show();
            } else {
                $('.wpuf_multistep_content').hide();
            }
        }
    };

    // on DOM ready
    $(function() {
        resizeBuilderContainer();

        $("#collapse-menu").click(function () {
            resizeBuilderContainer();
        });

        function resizeBuilderContainer() {
            if ($(document.body).hasClass('folded')) {
                $("#wpuf-form-builder").css("width", "calc(100% - 80px)");
            } else {
                $("#wpuf-form-builder").css("width", "calc(100% - 200px)");
            }
        }

        SettingsTab.init();
    });

    // Mobile view menu toggle
    $('#wpuf-form-builder').on('click', '#wpuf-toggle-field-options, #wpuf-toggle-show-form, .control-buttons .fa-pencil, .ui-draggable-handle', function() {
        $('#wpuf-toggle-field-options').toggleClass('hide');
        $('#wpuf-toggle-show-form').toggleClass('show');
        $('#builder-form-fields').toggleClass('show');
    });

})(jQuery);
