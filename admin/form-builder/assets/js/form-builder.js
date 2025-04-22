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
            settings: wpuf_form_builder.form_settings,
            current_panel: 'form-fields-v4-1',
            editing_field_id: 0,
            show_custom_field_tooltip: true,
            index_to_insert: 0,
        },

        mutations: {
            set_form_fields: function (state, form_fields) {
                Vue.set(state, 'form_fields', form_fields);
            },

            set_form_settings: function (state, value) {
                Vue.set(state, 'settings', value);
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
                if ('form-fields' === panel || 'form-fields-v4-1' === panel) {
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
            open_field_settings: function ( state, field_id ) {
                var field = state.form_fields.filter( function ( item ) {
                    return parseInt( field_id ) === parseInt( item.id );
                } );

                if ('field-options' === state.current_panel && field[0].id === state.editing_field_id) {
                    return;
                }

                if (field.length) {
                    state.editing_field_id = 0;
                    state.current_panel = 'field-options';

                    setTimeout( function () {
                        state.editing_field_id = field[0].id;
                    }, 400 );
                }
            },

            update_editing_form_field: function (state, payload) {
                var i = 0;

                for (i = 0; i < state.form_fields.length; i++) {
                    // check if the editing field exist in normal fields
                    if (state.form_fields[i].id === parseInt(payload.editing_field_id)) {
                        if ( 'read_only' === payload.field_name && payload.value ) {
                            state.form_fields[i]['required'] = 'no';
                        }

                        if ( 'required' === payload.field_name && 'yes' === payload.value ) {
                            state.form_fields[i]['read_only'] = false;
                        }

                        if (payload.field_name === 'name'  && ! state.form_fields[i].hasOwnProperty('is_new') ) {
                            continue;
                        } else {
                            state.form_fields[i][payload.field_name] = payload.value;
                        }

                    }

                    // check if the editing field belong to a column field
                    if (state.form_fields[i].template === 'column_field') {
                        var innerColumnFields = state.form_fields[i].inner_fields;

                        for (const columnFields in innerColumnFields) {
                            if (innerColumnFields.hasOwnProperty(columnFields)) {
                                var columnFieldIndex = 0;

                                while (columnFieldIndex < innerColumnFields[columnFields].length) {
                                    if (innerColumnFields[columnFields][columnFieldIndex].id === parseInt(payload.editing_field_id)) {
                                       innerColumnFields[columnFields][columnFieldIndex][payload.field_name] = payload.value;
                                    }
                                    columnFieldIndex++;
                                }
                            }
                        }
                    }
                }
            },

            // add new form field element
            add_form_field_element: function (state, payload) {

                state.form_fields.splice(payload.toIndex, 0, payload.field);
                var sprintf = wp.i18n.sprintf;
                var __ = wp.i18n.__;
                // bring newly added element into viewport, do not show for reg form
                if ( window.location.search.substring(1).split('&').includes('page=wpuf-profile-forms') ) return;
                Vue.nextTick(function () {
                    var el = $('#form-preview-stage .wpuf-form .field-items').eq(payload.toIndex);
                    if ('yes' == payload.field.is_meta && state.show_custom_field_tooltip) {

                        var image_one  = wpuf_admin_script.asset_url + '/images/custom-fields/settings.png';
                        var image_two  = wpuf_admin_script.asset_url + '/images/custom-fields/advance.png';
                        var html       = '<div class="wpuf-custom-field-instruction">';
                            html      += '<div class="step-one">';
                            html      += sprintf( '<p class="wpuf-text-base">%s <span class="wpuf-text-primary">%s</span>%s"</p>', __( 'Navigate through', 'wp-user-frontend' ), __( 'WP-admin > WPUF > Settings > Frontend Posting', 'wp-user-frontend' ), __( '- there you have to check the checkbox: "Show custom field data in the post content area', 'wp-user-frontend' ) );
                            html      += '<img src="'+ image_one +'" alt="settings">';
                            html      += '</div>';
                            html      += '<div class="step-two">';
                            html      += sprintf( '<p class="wpuf-text-base">%s<span class="wpuf-text-primary">%s</span>%s<span class="wpuf-text-primary">%s</span>%s</p>', __( 'Edit the custom field inside the post form and on the right side you will see', 'wp-user-frontend' ), __( '"Advanced Options".', 'wp-user-frontend' ), __( ' Expand that, scroll down and you will see ', 'wp-user-frontend' ), __( '"Show data on post"', 'wp-user-frontend' ), __( ' - set this yes.', 'wp-user-frontend' ) );
                            html      += '<img src="' + image_two + '" alt="custom field data">';
                            html      += '</div>';
                            html      += '</div>';
                        Swal.fire({
                            title: __( 'Do you want to show custom field data inside your post ?', 'wp-user-frontend' ),
                            html: html,
                            imageUrl: wpuf_form_builder.lock_icon,
                            showCancelButton: true,
                            confirmButtonText: "Don't show again",
                            cancelButtonText: 'Okay',
                            customClass: {
                                confirmButton: '!wpuf-bg-white !wpuf-text-black !wpuf-border !wpuf-border-solid !wpuf-border-gray-300 focus:!wpuf-shadow-none',
                                cancelButton: '!wpuf-text-white',
                            },
                            cancelButtonColor: '#16a34a'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                state.show_custom_field_tooltip = false;
                            } else {

                            }
                        } );
                    }

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

                let column_field = state.form_fields.find(function (field) {
                    return field.id === payload.field_id && field.input_type === 'column_field';
                });

                if (column_field){
                    let columns = ['column-1','column-2','column-3'];
                    columns.forEach(function (column) {
                        let inner_field = clone.inner_fields[column];
                        if(inner_field.length){
                            inner_field.forEach(function (field) {
                                field.id     = Math.floor(Math.random() * (9999999999 - 999999 + 1)) + 999999;
                                field.name   = field.name + '_copy';
                                field.is_new = true;
                            });
                        }
                    });
                }

                clone.id     = payload.new_id;
                clone.name   = clone.name + '_copy';
                clone.is_new = true;

                state.form_fields.splice(index, 0, clone);
            },

            // delete a field
            delete_form_field_element: function (state, index) {
                state.current_panel = 'form-fields-v4-1';
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
            },

            // add new form field element to column field
            add_column_inner_field_element: function (state, payload) {
                var columnFieldIndex = state.form_fields.findIndex(field => field.id === payload.toWhichColumnField);

                if (state.form_fields[columnFieldIndex].inner_fields[payload.toWhichColumn] === undefined) {
                    state.form_fields[columnFieldIndex].inner_fields[payload.toWhichColumn] = [];
                } else {
                    state.form_fields[columnFieldIndex].inner_fields[payload.toWhichColumn].splice( payload.toIndex, 0, payload.field );
                }
            },

            move_column_inner_fields: function(state, payload) {
                var columnFieldIndex = state.form_fields.findIndex(field => field.id === payload.field_id),
                    innerFields  = payload.inner_fields,
                    mergedFields = [];

                Object.keys(innerFields).forEach(function (column) {
                    // clear column-1, column-2 and column-3 fields if move_to specified column-1
                    // add column-1, column-2 and column-3 fields to mergedFields, later mergedFields will move to column-1 field
                    if (payload.move_to === "column-1") {
                        innerFields[column].forEach(function(field){
                            mergedFields.push(field);
                        });

                        // clear current column inner fields
                        state.form_fields[columnFieldIndex].inner_fields[column].splice(0, innerFields[column].length);
                    }

                    // clear column-2 and column-3 fields if move_to specified column-2
                    // add column-2 and column-3 fields to mergedFields, later mergedFields will move to column-2 field
                    if (payload.move_to === "column-2") {
                        if ( column === "column-2" || column === "column-3" ) {
                            innerFields[column].forEach(function(field){
                                mergedFields.push(field);
                            });

                            // clear current column inner fields
                            state.form_fields[columnFieldIndex].inner_fields[column].splice(0, innerFields[column].length);
                        }
                    }
                });

                // move inner fields to specified column
                if (mergedFields.length !== 0) {
                    mergedFields.forEach(function(field){
                        state.form_fields[columnFieldIndex].inner_fields[payload.move_to].splice(0, 0, field);
                    });
                }
            },

            // sorting inside column field
            swap_column_field_elements: function (state, payload) {
                var columnFieldIndex = state.form_fields.findIndex(field => field.id === payload.field_id),
                    fieldObj         = state.form_fields[columnFieldIndex].inner_fields[payload.fromColumn][payload.fromIndex];

                if( payload.fromColumn !== payload.toColumn) {
                    // add the field object to the target column
                    state.form_fields[columnFieldIndex].inner_fields[payload.toColumn].splice(payload.toIndex, 0, fieldObj);

                    // remove the field index from the source column
                    state.form_fields[columnFieldIndex].inner_fields[payload.fromColumn].splice(payload.fromIndex, 1);
                }else{
                    state.form_fields[columnFieldIndex].inner_fields[payload.toColumn].swap(payload.fromIndex, payload.toIndex);
                }
            },

            // open field settings panel
            open_column_field_settings: function (state, payload) {
                var field = payload.column_field;

                if ('field-options' === state.current_panel && field.id === state.editing_field_id) {
                    return;
                }

                if (field) {
                    state.editing_field_id = 0;
                    state.current_panel = 'field-options';
                    state.editing_field_type = 'column_field';
                    state.editing_column_field_id = payload.field_id;
                    state.edting_field_column = payload.column;
                    state.editing_inner_field_index = payload.index;

                    setTimeout(function () {
                        state.editing_field_id = field.id;
                    }, 400);
                }
            },

            clone_column_field_element: function (state, payload) {
                var columnFieldIndex = state.form_fields.findIndex(field => field.id === payload.field_id);

                var field = _.find(state.form_fields[columnFieldIndex].inner_fields[payload.toColumn], function (item) {
                    return parseInt(item.id) === parseInt(payload.column_field_id);
                });

                var clone = $.extend(true, {}, field),
                    index = parseInt(payload.index) + 1;

                clone.id     = payload.new_id;
                clone.name   = clone.name + '_copy';
                clone.is_new = true;

                state.form_fields[columnFieldIndex].inner_fields[payload.toColumn].splice(index, 0, clone);
            },

            // delete a column field
            delete_column_field_element: function (state, payload) {
                var columnFieldIndex = state.form_fields.findIndex(field => field.id === payload.field_id);

                state.current_panel = 'form-fields-v4-1';
                state.form_fields[columnFieldIndex].inner_fields[payload.fromColumn].splice(payload.index, 1);
            },

            // update the panel sections
            set_panel_sections: function ( state, sections ) {
                state.panel_sections = sections;
            },

            // set default panel sections
            set_default_panel_sections: function ( state ) {
                state.panel_sections = wpuf_form_builder.panel_sections;
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
            isDirty: false,
            shortcodeCopied: false,
            logoUrl: wpuf_form_builder.asset_url + '/images/wpuf-icon-circle.svg',
            settings_titles: wpuf_form_builder.settings_titles,
            settings_items: wpuf_form_builder.settings_items,
            active_tab: 'form-editor',
            form_settings: wpuf_form_builder.form_settings,
            active_settings_tab: Object.keys(wpuf_form_builder.settings_titles[Object.keys(wpuf_form_builder.settings_titles)[0]].sub_items)[0],
            active_settings_title: wpuf_form_builder.settings_titles[Object.keys(wpuf_form_builder.settings_titles)[0]].sub_items[Object.keys(wpuf_form_builder.settings_titles[Object.keys(wpuf_form_builder.settings_titles)[0]].sub_items)[0]].label,
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
            },

            settings: function() {
                return this.$store.state.settings;
            },

            meta_field_key: function () {
                let meta_key = [];

                this.$store.state.form_fields.forEach(function (field) {
                    if ( 'yes' === field.is_meta ) {
                        meta_key.push(field.name);
                    }
                });

                return meta_key.map(function(name) { return '{' + name +'}' }).join( );
            },

            settings_titles: function() {
                return this.$store.state.settings_titles;
            },

            settings_items: function() {
                return this.$store.state.settings_items;
            },

            section_exists: function() {
                return this.settings_items[this.active_settings_tab] && this.settings_items[this.active_settings_tab].section;
            }
        },

        watch: {
            form_fields: {
                handler: function() {
                    this.isDirty = true;
                },
                deep: true
            },
            active_settings_tab: {
                // attach selectize to the dropdowns after settings tab changes
                handler: function() {
                    setTimeout(function() {
                        $('.wpuf-settings-container select').selectize({
                            plugins: ['remove_button'],
                        });
                    }, 100);
                }
            }
        },

        created: function () {
            this.$store.commit('panel_add_show_prop');

            Vue.nextTick(function () {
                // selectize for all the dropdowns
                $('.wpuf-settings-container select').selectize({
                    plugins: ['remove_button'],
                });
            });

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

            var mail_shortcodes = new window.Clipboard('.wpuf-long-help span[data-clipboard-text]');

            mail_shortcodes.on('success', function(e) {
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

            setActiveSettingsTab: function (e) {
                this.active_settings_tab = $(e.target).attr('href');
            },

            // switch form
            switch_form: function () {
                this.is_form_switcher = (this.is_form_switcher) ? false : true;
            },

            // set current sidebar panel
            set_current_panel: function (panel) {
                if (panel === 'form-fields-v4-1') {
                    this.$store.state.panel_sections = wpuf_form_builder.panel_sections;
                }
                this.$store.commit('set_current_panel', panel);
            },

            // save form builder data
            save_form_builder: function () {
                var self = this;

                if (_.isFunction(this.validate_form_before_submit) && !this.validate_form_before_submit()) {

                    this.warn({
                        title: 'Incomplete Post Form',
                        html: this.validation_error_msg,
                        reverseButtons: true,
                        customClass: {
                            cancelButton: '!wpuf-bg-white !wpuf-text-black !wpuf-border !wpuf-border-solid !wpuf-border-gray-300 focus:!wpuf-shadow-none',
                            confirmButton: '!wpuf-text-white !wpuf-bg-primary',
                        },
                    });

                    return;
                }

                self.is_form_saving = true;

                var form_id = $('#wpuf-form-builder [name="wpuf_form_id"]').val();

                if ( typeof tinyMCE !== 'undefined' && window.location.search.substring(1).split('&').includes('page=wpuf-profile-forms') ) {
                    var parentWrap = $('#wp-wpuf_verification_body_' + form_id + '-wrap');
                    if ( ! parentWrap.hasClass('tmce-active') ) {
                        $('#wpuf_verification_body_' + form_id + '-tmce').click();  // bring user to the visual editor
                        $('#wpuf_welcome_email_body_' + form_id + '-tmce').click();
                    }

                    $('textarea[name="wpuf_settings[notification][verification_body]"]').val(tinyMCE.get('wpuf_verification_body_' + form_id).getContent());
                    $('textarea[name="wpuf_settings[notification][welcome_email_body]"]').val(tinyMCE.get('wpuf_welcome_email_body_' + form_id).getContent());
                }

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

                        if (response.form_settings) {
                            self.$store.commit('set_form_settings', response.form_settings);
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
            },

            // settings field classes to add similar field classes
            setting_class_names: function(field_type) {
                switch (field_type) {
                    case 'upload_btn':
                        return 'file-selector wpuf-rounded-[6px] wpuf-btn-secondary';

                    case 'radio':
                        return '!wpuf-mt-0 !wpuf-mr-2 wpuf-radio !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:checked:!wpuf-shadow-none focus:wpuf-shadow-primary';

                    case 'checkbox':
                        return '!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded';

                    case 'dropdown':
                        return 'wpuf-block wpuf-w-full wpuf-min-w-full wpuf-text-gray-700 wpuf-font-normal !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 !wpuf-text-base !leading-6';

                    default:
                        return 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] wpuf-max-w-full focus:!wpuf-ring-transparent';
                }
            },

            switch_settings_menu: function(menu, submenu) {
                this.active_settings_tab = submenu;
                if (submenu === 'modules') {
                    this.active_settings_title = 'Modules';
                } else {
                    this.active_settings_title = this.settings_titles[menu].sub_items[submenu].label;
                }
            },

            switch_form_settings_pic_radio_item: function ( key, value ) {
                this.form_settings[key] = value;
            }
        }
    });

    var SettingsTab = {
        init: function() {
            $(function() {
                $('.datepicker').datetimepicker();
                $('.wpuf-ms-color').wpColorPicker();
            });
        },
    };

    // on DOM ready
    $(function() {
        // resizeBuilderContainer();
        //
        // $("#collapse-menu").click(function () {
        //     resizeBuilderContainer();
        // });

        function resizeBuilderContainer() {
            if ($(document.body).hasClass('folded')) {
                $("#wpuf-form-builder").css("width", "calc(100% - 80px)");
            } else {
                $("#wpuf-form-builder").css("width", "calc(100% - 200px)");
            }
        }

        SettingsTab.init();

        const dependencies = {
            // Fields and their show/hide conditions
            fields: {
                message: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'redirect_to',
                        value: 'same'
                    }],
                },
                page_id: {
                    type: 'select',
                    dependsOn: [{
                        field: 'redirect_to',
                        value: 'page'
                    }],
                },
                url: {
                    type: 'text',
                    dependsOn: [{
                        field: 'redirect_to',
                        value: 'url'
                    }],
                },
                update_message: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'edit_redirect_to',
                        value: 'same'
                    }],
                },
                edit_page_id: {
                    type: 'select',
                    dependsOn: [{
                        field: 'edit_redirect_to',
                        value: 'page'
                    }],
                },
                edit_url: {
                    type: 'text',
                    dependsOn: [{
                        field: 'edit_redirect_to',
                        value: 'url'
                    }],
                },
                guest_details: {
                    type: 'checkbox',
                    dependsOn: [{
                        field: 'post_permission',
                        value: 'guest_post'
                    }]
                },
                guest_email_verify: {
                    type: 'text',
                    dependsOn: [{
                        field: 'post_permission',
                        value: 'guest_post'
                    }]
                },
                name_label: {
                    type: 'text',
                    dependsOn: [
                        {
                            field: 'post_permission',
                            value: 'guest_post'
                        },
                        {
                            field: 'guest_details',
                            value: true
                        }
                    ]
                },
                email_label: {
                    type: 'text',
                    dependsOn: [
                        {
                            field: 'post_permission',
                            value: 'guest_post'
                        },
                        {
                            field: 'guest_details',
                            value: true
                        }
                    ]
                },
                roles: {
                    type: 'select',
                    dependsOn: [{
                        field: 'post_permission',
                        value: 'role_base'
                    }]
                },
                message_restrict: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'post_permission',
                        value: 'role_base'
                    }]
                },
                choose_payment_option: {
                    type: 'select',
                    dependsOn: [{
                        field: 'payment_options',
                        value: true
                    }]
                },
                fallback_ppp_enable: {
                    type: 'checkbox',
                    dependsOn: [
                        {
                            field: 'payment_options',
                            value: true
                        },
                        {
                            field: 'choose_payment_option',
                            value: 'force_pack_purchase'
                        }
                    ]
                },
                fallback_ppp_cost: {
                    type: 'checkbox',
                    dependsOn: [
                        {
                            field: 'payment_options',
                            value: true
                        },
                        {
                            field: 'choose_payment_option',
                            value: 'force_pack_purchase'
                        },
                        {
                            field: 'fallback_ppp_enable',
                            value: true
                        }
                    ]
                },
                pay_per_post_cost: {
                    type: 'number',
                    dependsOn: [
                        {
                            field: 'payment_options',
                            value: true
                        },
                        {
                            field: 'choose_payment_option',
                            value: 'enable_pay_per_post'
                        }
                    ]
                },
                ppp_payment_success_page: {
                    type: 'select',
                    dependsOn: [
                        {
                            field: 'payment_options',
                            value: true
                        },
                        {
                            field: 'choose_payment_option',
                            value: 'enable_pay_per_post'
                        }
                    ]
                },
                new_to: {
                    type: 'text',
                    dependsOn: [{
                        field: 'new',
                        value: true
                    }]
                },
                new_subject: {
                    type: 'text',
                    dependsOn: [{
                        field: 'new',
                        value: true
                    }]
                },
                new_body: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'new',
                        value: true
                    }]
                },
                schedule_start: {
                    type: 'text',
                    dependsOn: [{
                        field: 'schedule_form',
                        value: true
                    }]
                },
                form_pending_message: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'schedule_form',
                        value: true
                    }]
                },
                form_expired_message: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'schedule_form',
                        value: true
                    }]
                },
                limit_number: {
                    type: 'number',
                    dependsOn: [{
                        field: 'limit_entries',
                        value: true
                    }]
                },
                limit_message: {
                    type: 'textarea',
                    dependsOn: [{
                        field: 'limit_entries',
                        value: true
                    }]
                }
            }
        };

        // load only for post form settings
        if (wpuf_form_builder.form_type === 'wpuf_forms') {
            new FormDependencyHandler(dependencies);
        }

        // initially show the first tab(General) on first page load
        show_settings_for('general');

        function show_settings_for(settings) {
            $('.wpuf-settings-body').each(function() {
                if ($(this).data('settings-body') === settings) {
                    $(this).fadeIn();
                } else {
                    $(this).fadeOut();
                }
            });
        }

        $('ul.wpuf-sidebar-menu li').each(function() {
            $(this).on('click', function() {
                show_settings_for($(this).data('settings'));
            });
        });

        $('#modules-menu').on('click', function() {
            show_settings_for('modules');
        });

        $('.wpuf-pic-radio img').dblclick(function() {
            $( this ).siblings( 'input[type="radio"]' ).prop( 'checked', false );
        });
    });

    class FormDependencyHandler {
        constructor(dependencies) {
            this.dependencies = dependencies;
            this.init();
        }

        init() {
            // Get all fields that have dependencies
            const fieldsWithDependencies = Object.keys(this.dependencies.fields);

            // Initially hide all dependent fields
            fieldsWithDependencies.forEach(fieldId => {
                this.hideField(fieldId);
            });

            // Add change event listeners to all form fields that others depend on
            const uniqueControlFields = this.getUniqueControlFields();
            uniqueControlFields.forEach(fieldId => {
                this.attachFieldListener(fieldId);
            });

            // Initial check for all fields
            this.checkAllDependencies();
        }

        getUniqueControlFields() {
            // Get unique list of fields that control other fields
            const controlFields = new Set();
            Object.values(this.dependencies.fields).forEach(field => {
                field.dependsOn.forEach(dependency => {
                    controlFields.add(dependency.field);
                });
            });
            return Array.from(controlFields);
        }

        attachFieldListener(fieldId) {
            const field = $(`#${fieldId}`);

            if (field.length === 0) {
                return;
            }

            const fieldType = field.attr('type') || field.prop('tagName').toLowerCase();

            if (fieldType === 'checkbox' || fieldType === 'select') {
                field.on('change', () => this.checkAllDependencies());
            } else if (fieldType === 'radio') {
                const fieldName = field.attr('name');
                const radioFields = $(`input[name="${fieldName}"]`);

                radioFields.each((index, radio) => {
                    $(radio).on('change', () => this.checkAllDependencies());
                });
            }
        }

        checkAllDependencies() {
            Object.keys(this.dependencies.fields).forEach(fieldId => {
                this.checkFieldDependencies(fieldId);
            });
        }

        checkFieldDependencies(fieldId) {
            const fieldConfig = this.dependencies.fields[fieldId];
            const shouldShow = this.shouldFieldBeVisible(fieldConfig.dependsOn);

            if (shouldShow) {
                this.showField(fieldId);
                this.attachSelectize(fieldId);
            } else {
                this.hideField(fieldId);
            }
        }

        shouldFieldBeVisible(dependencies) {
            // All conditions must be met (AND logic)
            return dependencies.every(dep => {
                const controlField = $(`#${dep.field}`);

                if (controlField.length === 0) {
                    return;
                }

                const fieldType = controlField.attr('type') || controlField.prop('tagName').toLowerCase();

                if (fieldType === 'checkbox' || fieldType === 'radio') {
                    return controlField.is(':checked') === dep.value;
                } else if (fieldType === 'select') {
                    return controlField.val() === dep.value;
                }
                return false;
            });
        }

        showField(fieldId) {
            $(`#${fieldId}`).closest('.wpuf-input-container').fadeIn(200);
        }

        hideField(fieldId) {
            $(`#${fieldId}`).closest('.wpuf-input-container').fadeOut(200);
        }

        // if it is a select field, then attach selectize like below
        attachSelectize(fieldId) {
            if ($(`#${fieldId}`).is('select')) {
                $(`#${fieldId}`).selectize({
                    plugins: ['remove_button'],
                });
            }
        }
    }

    window.FormDependencyHandler = FormDependencyHandler;

    // Mobile view menu toggle
    $('#wpuf-form-builder').on('click', '#wpuf-toggle-field-options, #wpuf-toggle-show-form, .field-buttons .fa-pencil, .ui-draggable-handle', function() {
        $('#wpuf-toggle-field-options').toggleClass('hide');
        $('#wpuf-toggle-show-form').toggleClass('show');
        $('#builder-form-fields').toggleClass('show');
    });

    $('select#post_type').on('change', function() {
        populate_default_categories(this);
    });

    function populate_default_categories(obj) {
        var post_type = $( obj ).val();
        wp.ajax.send('wpuf_form_setting_post', {
            data: {
                post_type: post_type,
                wpuf_form_builder_setting_nonce: wpuf_form_builder.nonce
            },
            success: function (response) {
                const default_category = 'select#default_category';
                let default_category_name = default_category;

                if ( post_type !== 'post' ) {
                    default_category_name = 'select#default_' + post_type + '_cat';
                }

                const value = $(default_category_name).data('value');

                $(default_category).parent('.wpuf-my-4.wpuf-input-container').remove();
                $('select#post_type').parent('.wpuf-my-4.wpuf-input-container').after(response.data);

                if (value && ( typeof value === 'string' )) {
                    $(default_category).val(value.split(","));
                } else {
                    $(default_category).val(value);
                }

                $(default_category).selectize({
                    plugins: ['remove_button'],
                });

            },
            error: function ( error ) {
                console.log(error);
            }
        });
    }


})(jQuery);
