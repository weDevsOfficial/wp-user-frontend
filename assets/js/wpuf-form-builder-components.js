;(function($) {
'use strict';

Vue.component('builder-stage', {
    template: '#tmpl-wpuf-builder-stage',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage).concat(wpuf_mixins.add_form_field),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        hidden_fields: function () {
            return this.$store.state.form_fields.filter(function (item) {
                return 'custom_hidden_field' === item.template;
            });
        },

        editing_form_id: function () {
            return this.$store.state.editing_field_id;
        },

        pro_link: function () {
            return wpuf_form_builder.pro_link;
        }
    },

    mounted: function () {
        var self = this,
            in_column_field = false;

        // bind jquery ui sortable
        $('#form-preview-stage .wpuf-form.sortable-list').sortable({
            placeholder: 'form-preview-stage-dropzone',
            items: '.field-items',
            handle: '.field-buttons .move',
            scroll: true,
            over: function() {
                in_column_field = false;

                // if the field drop in column field, then stop field rendering in the builder stage
                $(".wpuf-column-inner-fields" ).on( "drop", function(event) {
                    var targetColumn = event.currentTarget.classList,
                        isColumnExist = $.inArray(".wpuf-column-inner-fields", targetColumn);

                    if ( isColumnExist ) {
                        in_column_field = true;
                    }
                } );
            },
            update: function (e, ui) {
                var item    = ui.item[0],
                    data    = item.dataset,
                    source  = data.source,
                    toIndex = parseInt($(ui.item).index()),
                    payload = {
                        toIndex: toIndex
                    };

                if ('panel' === source) {
                    // add new form element
                    self.$store.state.index_to_insert = parseInt(toIndex);

                    if ( ! in_column_field ) {
                        var field_template  = ui.item[0].dataset.formField;
                        self.add_form_field(field_template);
                    }

                    // remove button from stage
                    $(this).find('.button.ui-draggable.ui-draggable-handle').remove();

                } else if ('stage' === source) {
                    payload.fromIndex = parseInt(data.index);

                    self.$store.commit('swap_form_field_elements', payload);
                }
            }
        });
    },

    methods: {

        open_field_settings: function(field_id) {
            this.$store.commit('open_field_settings', field_id);
        },

        clone_field: function(field_id, index) {
            var payload = {
                field_id: field_id,
                index: index,
                new_id: this.get_random_id()
            };

            // single instance checking
            var field = _.find(this.$store.state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(payload.field_id);
            });

            // check if these are already inserted
            if ( this.isSingleInstance( field.template ) && this.containsField( field.template ) ) {
                Swal.fire({
                    title: "Oops...",
                    text: "You already have this field in the form"
                });
                return;
            }

            this.$store.commit('clone_form_field_element', payload);
        },

        delete_field: function(index) {
            var self = this;

            (Swal.fire({
                text: self.i18n.delete_field_warn_msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d54e21',
                confirmButtonText: self.i18n.yes_delete_it,
                cancelButtonText: self.i18n.no_cancel_it,
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger',
                }
            })).then((result) => {
                if (result.isConfirmed) {
                    self.$store.commit('delete_form_field_element', index);
                }
            });
        },

        delete_hidden_field: function (field_id) {
            var i = 0;

            for (i = 0; i < this.form_fields.length; i++) {
                if (parseInt(field_id) === parseInt(this.form_fields[i].id)) {
                    this.delete_field(i);
                }
            }
        },

        is_pro_feature: function (template) {
            return (this.field_settings[template] && this.field_settings[template].pro_feature) ? true : false;
        },

        is_template_available: function (field) {
            var template = field.template;

            if (this.field_settings[template]) {
                if (this.is_pro_feature(template)) {
                    return false;
                }

                return true;
            }

            // for example see 'mixin_builder_stage' mixin's 'is_taxonomy_template_available' method
            if (_.isFunction(this['is_' + template + '_template_available'])) {
                return this['is_' + template + '_template_available'].call(this, field);
            }

            return false;
        },

        is_full_width: function (template) {
            if (this.field_settings[template] && this.field_settings[template].is_full_width) {
                return true;
            }

            return false;
        },

        is_invisible: function (field) {
            return ( field.recaptcha_type && 'invisible_recaptcha' === field.recaptcha_type ) ? true : false;
        },

        get_field_name: function (template) {
            return this.field_settings[template].title;
        }
    }
});

Vue.component('builder-stage-v4-1', {
    template: '#tmpl-wpuf-builder-stage-v4-1',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage).concat(wpuf_mixins.add_form_field),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        hidden_fields: function () {
            return this.$store.state.form_fields.filter(function (item) {
                return 'custom_hidden_field' === item.template;
            });
        },

        editing_form_id: function () {
            return this.$store.state.editing_field_id;
        },
    },

    mounted: function () {
        var self = this,
            in_column_field = false;

        // bind jquery ui sortable
        $('#form-preview-stage, #form-preview-stage .wpuf-form.sortable-list').sortable({
            placeholder: 'form-preview-stage-dropzone',
            items: '.field-items',
            handle: '.field-buttons .move',
            scroll: true,
            over: function() {
                in_column_field = false;

                // if the field drop in column field, then stop field rendering in the builder stage
                $(".wpuf-column-inner-fields" ).on( "drop", function(event) {
                    var targetColumn = event.currentTarget.classList,
                        isColumnExist = $.inArray(".wpuf-column-inner-fields", targetColumn);

                    if ( isColumnExist ) {
                        in_column_field = true;
                    }
                } );
            },
            update: function (e, ui) {
                var item    = ui.item[0],
                    data    = item.dataset,
                    source  = data.source,
                    toIndex = parseInt($(ui.item).index()),
                    payload = {
                        toIndex: toIndex
                    };

                if ('panel' === source) {
                    // add new form element
                    self.$store.state.index_to_insert = parseInt(toIndex);

                    if ( ! in_column_field ) {
                        var field_template  = ui.item[0].dataset.formField;
                        self.add_form_field(field_template);
                    }

                    // remove button from stage
                    $(this).find('.wpuf-field-button').remove();

                } else if ('stage' === source) {
                    payload.fromIndex = parseInt(data.index);

                    self.$store.commit('swap_form_field_elements', payload);
                }

            }
        });
    },

    methods: {
        open_field_settings: function(field_id) {
            this.$store.commit('open_field_settings', field_id);
        },

        clone_field: function(field_id, index) {
            var payload = {
                field_id: field_id,
                index: index,
                new_id: this.get_random_id()
            };

            // single instance checking
            var field = _.find(this.$store.state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(payload.field_id);
            });

            // check if these are already inserted
            if ( this.isSingleInstance( field.template ) && this.containsField( field.template ) ) {
                Swal.fire({
                    title: "Oops...",
                    text: "You already have this field in the form"
                });
                return;
            }

            this.$store.commit('clone_form_field_element', payload);
        },

        delete_field: function(index) {
            var self = this;
            const icon_delete  = wpuf_admin_script.asset_url + '/images/delete-icon-rounded.svg';
            const delete_icon_html = '<img src="' + icon_delete + '" alt="delete">';

            (Swal.fire({
                title: self.i18n.delete_field_warn_title,
                html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' +  self.i18n.delete_field_warn_msg + '</span>',
                iconHtml: delete_icon_html,
                showCancelButton: true,
                confirmButtonText: self.i18n.yes_delete_it,
                cancelButtonText: self.i18n.no_cancel_it,
                cancelButtonColor: '#fff',
                confirmButtonColor: '#EF4444',
                reverseButtons: true
            })).then((result) => {
                if (result.isConfirmed) {
                    self.$store.commit('delete_form_field_element', index);
                }
            });
        },

        delete_hidden_field: function (field_id) {
            var i = 0;

            for (i = 0; i < this.form_fields.length; i++) {
                if (parseInt(field_id) === parseInt(this.form_fields[i].id)) {
                    this.delete_field(i);
                }
            }
        },

        is_pro_feature: function (template) {
            return ( this.field_settings[template] && this.field_settings[template].pro_feature ) ? true : false;
        },

        is_template_available: function (field) {
            var template = field.template;

            if (this.field_settings[template]) {
                if (this.is_pro_preview(template)) {
                    return false;
                }

                return true;
            }

            // for example see 'mixin_builder_stage' mixin's 'is_taxonomy_template_available' method
            if (_.isFunction(this['is_' + template + '_template_available'])) {
                return this['is_' + template + '_template_available'].call(this, field);
            }

            return false;
        },

        is_full_width: function (template) {
            if (this.field_settings[template] && this.field_settings[template].is_full_width) {
                return true;
            }

            return false;
        },

        is_invisible: function (field) {
            return ( field.recaptcha_type && 'invisible_recaptcha' === field.recaptcha_type ) ? true : false;
        },

        get_field_name: function (template) {
            return this.field_settings[template].title;
        },

        openRepeatFieldPicker(fieldId) {
            // Find the repeat field component by ref and call openFieldPicker()
            const refName = 'repeatFieldComponent_' + fieldId;
            const comp = this.$refs[refName];
            // Vue 2: $refs[refName] is an array if used in v-for, so get first
            if (Array.isArray(comp) && comp.length > 0) {
                comp[0].openFieldPicker();
            } else if (comp && typeof comp.openFieldPicker === 'function') {
                comp.openFieldPicker();
            }
        },
    }
});

Vue.component('field-checkbox', {
    template: '#tmpl-wpuf-field-checkbox',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                var value = this.editing_form_field[this.option_field.name];

                if (this.option_field.is_single_opt) {
                    var option = Object.keys(this.option_field.options)[0];

                    if (value === option) {
                        return true;

                    } else {
                        return false;
                    }
                }

                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                if (this.option_field.is_single_opt) {
                    value = value ? Object.keys(this.option_field.options)[0] : '';
                }


                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        }
    }
});

Vue.component('field-html_help_text', {
    template: '#tmpl-wpuf-field-html_help_text',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],
});

Vue.component('field-icon_selector', {
    template: '#tmpl-wpuf-field-icon_selector',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],
    
    mounted: function() {
        document.addEventListener('click', this.handleClickOutside);
    },

    data: function () {
        return {
            showIconPicker: false,
            searchTerm: '',
            icons: this.getAllIcons()
        };
    },

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        },

        selectedIconDisplay: function() {
            if (this.value) {
                var icon = this.icons.find(function(item) {
                    return item.class === this.value;
                }.bind(this));
                return icon ? icon.name : this.value;
            }
            return 'Select an icon';
        },

        filteredIcons: function() {
            var self = this;
            if (!this.icons.length) return [];
            
            if (!this.searchTerm) return this.icons;
            
            var searchLower = this.searchTerm.toLowerCase();
            return this.icons.filter(function(icon) {
                return icon.name.toLowerCase().indexOf(searchLower) !== -1 ||
                       icon.keywords.toLowerCase().indexOf(searchLower) !== -1;
            });
        }
    },

    methods: {
        getAllIcons: function() {
            // Comprehensive Font Awesome 6 icon collection
            return [
                // User & People
                { class: 'fas fa-user', name: 'User', keywords: 'person account profile' },
                { class: 'far fa-user', name: 'User Outline', keywords: 'person account profile outline' },
                { class: 'fas fa-users', name: 'Users', keywords: 'people group team' },
                { class: 'fas fa-user-circle', name: 'User Circle', keywords: 'person account profile avatar' },
                { class: 'fas fa-user-plus', name: 'Add User', keywords: 'person account new register' },
                { class: 'fas fa-user-minus', name: 'Remove User', keywords: 'person account delete' },
                { class: 'fas fa-user-check', name: 'User Check', keywords: 'person verified approve' },
                { class: 'fas fa-user-edit', name: 'Edit User', keywords: 'person account modify' },
                { class: 'fas fa-user-cog', name: 'User Settings', keywords: 'person account config' },
                { class: 'fas fa-user-graduate', name: 'Graduate', keywords: 'student education' },
                { class: 'fas fa-user-tie', name: 'Business User', keywords: 'person professional' },
                
                // Communication
                { class: 'fas fa-envelope', name: 'Email', keywords: 'mail message contact' },
                { class: 'far fa-envelope', name: 'Email Outline', keywords: 'mail message contact outline' },
                { class: 'fas fa-envelope-open', name: 'Open Email', keywords: 'mail read message' },
                { class: 'fas fa-phone', name: 'Phone', keywords: 'call telephone contact' },
                { class: 'fas fa-mobile-alt', name: 'Mobile', keywords: 'phone cell smartphone' },
                { class: 'fas fa-comment', name: 'Comment', keywords: 'message chat talk' },
                { class: 'far fa-comment', name: 'Comment Outline', keywords: 'message chat talk outline' },
                { class: 'fas fa-comments', name: 'Comments', keywords: 'messages chat conversation' },
                { class: 'fas fa-inbox', name: 'Inbox', keywords: 'mail messages receive' },
                { class: 'fas fa-paper-plane', name: 'Send', keywords: 'submit message mail' },
                { class: 'fas fa-at', name: 'At Symbol', keywords: 'email address mention' },
                { class: 'fas fa-bullhorn', name: 'Megaphone', keywords: 'announce speaker' },
                
                // Security & Privacy
                { class: 'fas fa-lock', name: 'Lock', keywords: 'secure password private' },
                { class: 'fas fa-unlock', name: 'Unlock', keywords: 'open access' },
                { class: 'fas fa-key', name: 'Key', keywords: 'password access login' },
                { class: 'fas fa-shield-alt', name: 'Shield', keywords: 'protect security safe' },
                { class: 'fas fa-eye', name: 'Eye', keywords: 'view show visible' },
                { class: 'fas fa-eye-slash', name: 'Eye Slash', keywords: 'hide invisible password' },
                { class: 'fas fa-fingerprint', name: 'Fingerprint', keywords: 'security biometric' },
                
                // Location & Navigation
                { class: 'fas fa-home', name: 'Home', keywords: 'house address main' },
                { class: 'fas fa-map-marker-alt', name: 'Location', keywords: 'pin address place' },
                { class: 'fas fa-map', name: 'Map', keywords: 'location navigation' },
                { class: 'fas fa-globe', name: 'Globe', keywords: 'world earth website' },
                { class: 'fas fa-compass', name: 'Compass', keywords: 'direction navigation' },
                { class: 'fas fa-route', name: 'Route', keywords: 'path direction way' },
                { class: 'fas fa-city', name: 'City', keywords: 'urban buildings town' },
                
                // Business & Finance
                { class: 'fas fa-building', name: 'Building', keywords: 'office company business' },
                { class: 'far fa-building', name: 'Building Outline', keywords: 'office company business outline' },
                { class: 'fas fa-briefcase', name: 'Briefcase', keywords: 'business work job' },
                { class: 'fas fa-credit-card', name: 'Credit Card', keywords: 'payment money finance' },
                { class: 'far fa-credit-card', name: 'Credit Card Outline', keywords: 'payment money finance outline' },
                { class: 'fas fa-dollar-sign', name: 'Dollar', keywords: 'money currency price' },
                { class: 'fas fa-euro-sign', name: 'Euro', keywords: 'money currency price' },
                { class: 'fas fa-chart-line', name: 'Line Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-chart-bar', name: 'Bar Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-chart-pie', name: 'Pie Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-calculator', name: 'Calculator', keywords: 'math compute numbers' },
                
                // Date & Time
                { class: 'fas fa-calendar', name: 'Calendar', keywords: 'date time schedule' },
                { class: 'far fa-calendar', name: 'Calendar Outline', keywords: 'date time schedule outline' },
                { class: 'fas fa-calendar-alt', name: 'Calendar Alt', keywords: 'date time schedule' },
                { class: 'fas fa-clock', name: 'Clock', keywords: 'time hour minute' },
                { class: 'far fa-clock', name: 'Clock Outline', keywords: 'time hour minute outline' },
                { class: 'fas fa-stopwatch', name: 'Stopwatch', keywords: 'timer time measure' },
                { class: 'fas fa-history', name: 'History', keywords: 'time past previous' },
                
                // Files & Documents
                { class: 'fas fa-file', name: 'File', keywords: 'document paper text' },
                { class: 'far fa-file', name: 'File Outline', keywords: 'document paper text outline' },
                { class: 'fas fa-file-alt', name: 'File Text', keywords: 'document text content' },
                { class: 'fas fa-file-pdf', name: 'PDF File', keywords: 'document adobe pdf' },
                { class: 'fas fa-file-word', name: 'Word File', keywords: 'document microsoft doc' },
                { class: 'fas fa-file-excel', name: 'Excel File', keywords: 'spreadsheet microsoft xls' },
                { class: 'fas fa-folder', name: 'Folder', keywords: 'directory files storage' },
                { class: 'far fa-folder', name: 'Folder Outline', keywords: 'directory files storage outline' },
                { class: 'fas fa-folder-open', name: 'Folder Open', keywords: 'directory files browse' },
                
                // Media
                { class: 'fas fa-image', name: 'Image', keywords: 'photo picture media' },
                { class: 'far fa-image', name: 'Image Outline', keywords: 'photo picture media outline' },
                { class: 'fas fa-images', name: 'Images', keywords: 'photos pictures gallery' },
                { class: 'fas fa-video', name: 'Video', keywords: 'movie film media' },
                { class: 'fas fa-music', name: 'Music', keywords: 'audio sound song' },
                { class: 'fas fa-camera', name: 'Camera', keywords: 'photo picture capture' },
                { class: 'fas fa-play', name: 'Play', keywords: 'start video audio' },
                { class: 'fas fa-pause', name: 'Pause', keywords: 'stop video audio' },
                
                // Actions & Controls
                { class: 'fas fa-plus', name: 'Plus', keywords: 'add new create more' },
                { class: 'fas fa-minus', name: 'Minus', keywords: 'remove subtract less' },
                { class: 'fas fa-times', name: 'Close', keywords: 'x remove delete cancel' },
                { class: 'fas fa-check', name: 'Check', keywords: 'approve yes correct tick' },
                { class: 'fas fa-arrow-right', name: 'Arrow Right', keywords: 'next forward direction' },
                { class: 'fas fa-arrow-left', name: 'Arrow Left', keywords: 'back previous direction' },
                { class: 'fas fa-arrow-up', name: 'Arrow Up', keywords: 'top ascend direction' },
                { class: 'fas fa-arrow-down', name: 'Arrow Down', keywords: 'bottom descend direction' },
                { class: 'fas fa-edit', name: 'Edit', keywords: 'modify change update pencil' },
                { class: 'fas fa-trash', name: 'Delete', keywords: 'remove destroy bin' },
                { class: 'fas fa-save', name: 'Save', keywords: 'store keep preserve' },
                { class: 'fas fa-download', name: 'Download', keywords: 'get receive save' },
                { class: 'fas fa-upload', name: 'Upload', keywords: 'send attach share' },
                { class: 'fas fa-search', name: 'Search', keywords: 'find look magnify' },
                { class: 'fas fa-copy', name: 'Copy', keywords: 'duplicate clone' },
                
                // Status & Indicators
                { class: 'fas fa-heart', name: 'Heart', keywords: 'love like favorite' },
                { class: 'far fa-heart', name: 'Heart Outline', keywords: 'love like favorite outline' },
                { class: 'fas fa-star', name: 'Star', keywords: 'favorite rating bookmark' },
                { class: 'far fa-star', name: 'Star Outline', keywords: 'favorite rating bookmark outline' },
                { class: 'fas fa-bookmark', name: 'Bookmark', keywords: 'save favorite mark' },
                { class: 'fas fa-bell', name: 'Bell', keywords: 'notification alert alarm' },
                { class: 'fas fa-exclamation-triangle', name: 'Warning', keywords: 'alert danger caution' },
                { class: 'fas fa-info-circle', name: 'Info', keywords: 'information help about' },
                { class: 'fas fa-question-circle', name: 'Question', keywords: 'help support faq' },
                { class: 'fas fa-thumbs-up', name: 'Thumbs Up', keywords: 'like approve good' },
                { class: 'fas fa-thumbs-down', name: 'Thumbs Down', keywords: 'dislike disapprove bad' },
                
                // Web & Technology
                { class: 'fas fa-link', name: 'Link', keywords: 'url website connection' },
                { class: 'fas fa-wifi', name: 'WiFi', keywords: 'internet connection wireless' },
                { class: 'fas fa-code', name: 'Code', keywords: 'programming development html' },
                { class: 'fas fa-database', name: 'Database', keywords: 'data storage sql' },
                { class: 'fas fa-cloud', name: 'Cloud', keywords: 'storage online server' },
                { class: 'fas fa-desktop', name: 'Desktop', keywords: 'computer monitor pc' },
                { class: 'fas fa-laptop', name: 'Laptop', keywords: 'computer notebook portable' },
                { class: 'fas fa-tablet-alt', name: 'Tablet', keywords: 'ipad device mobile' },
                
                // Shopping & Commerce
                { class: 'fas fa-shopping-cart', name: 'Shopping Cart', keywords: 'buy purchase ecommerce' },
                { class: 'fas fa-shopping-bag', name: 'Shopping Bag', keywords: 'buy purchase store' },
                { class: 'fas fa-store', name: 'Store', keywords: 'shop business retail' },
                { class: 'fas fa-gift', name: 'Gift', keywords: 'present surprise reward' },
                { class: 'fas fa-tag', name: 'Tag', keywords: 'label price category' },
                { class: 'fas fa-tags', name: 'Tags', keywords: 'labels categories prices' },
                
                // Social Media
                { class: 'fab fa-facebook', name: 'Facebook', keywords: 'social media social network' },
                { class: 'fab fa-twitter', name: 'Twitter', keywords: 'social media tweet' },
                { class: 'fab fa-instagram', name: 'Instagram', keywords: 'social media photos' },
                { class: 'fab fa-linkedin', name: 'LinkedIn', keywords: 'professional network business' },
                { class: 'fab fa-youtube', name: 'YouTube', keywords: 'video social media' },
                { class: 'fab fa-github', name: 'GitHub', keywords: 'code development git' },
                { class: 'fab fa-whatsapp', name: 'WhatsApp', keywords: 'messaging chat mobile' },
                
                // Education
                { class: 'fas fa-graduation-cap', name: 'Graduation Cap', keywords: 'education school university' },
                { class: 'fas fa-book', name: 'Book', keywords: 'read education learning' },
                { class: 'fas fa-book-open', name: 'Open Book', keywords: 'read study learning' },
                { class: 'fas fa-pen', name: 'Pen', keywords: 'write edit signing' },
                { class: 'fas fa-pencil-alt', name: 'Pencil', keywords: 'write edit draw' },
                { class: 'fas fa-school', name: 'School', keywords: 'education building learning' },
                
                // Transportation
                { class: 'fas fa-car', name: 'Car', keywords: 'vehicle auto transport' },
                { class: 'fas fa-plane', name: 'Plane', keywords: 'flight travel airplane' },
                { class: 'fas fa-train', name: 'Train', keywords: 'transport railway travel' },
                { class: 'fas fa-bus', name: 'Bus', keywords: 'transport public travel' },
                { class: 'fas fa-bicycle', name: 'Bicycle', keywords: 'bike transport cycle' },
                { class: 'fas fa-ship', name: 'Ship', keywords: 'boat transport water' },
                
                // Health & Medical
                { class: 'fas fa-stethoscope', name: 'Stethoscope', keywords: 'medical doctor health' },
                { class: 'fas fa-pills', name: 'Pills', keywords: 'medicine health medication' },
                { class: 'fas fa-hospital', name: 'Hospital', keywords: 'medical health building' },
                { class: 'fas fa-ambulance', name: 'Ambulance', keywords: 'emergency medical health' },
                
                // Form Fields Common
                { class: 'fas fa-font', name: 'Text', keywords: 'typography text field input' },
                { class: 'fas fa-paragraph', name: 'Paragraph', keywords: 'text content textarea' },
                { class: 'fas fa-list-ul', name: 'Bullet List', keywords: 'items checklist options' },
                { class: 'fas fa-list-ol', name: 'Numbered List', keywords: 'ordered items steps' },
                { class: 'fas fa-check-square', name: 'Checkbox', keywords: 'select option choice tick' },
                { class: 'fas fa-dot-circle', name: 'Radio Button', keywords: 'select option choice' },
                { class: 'fas fa-caret-down', name: 'Dropdown', keywords: 'select options menu' },
                { class: 'fas fa-calendar-day', name: 'Date Picker', keywords: 'date calendar select' },
                { class: 'fas fa-toggle-on', name: 'Toggle On', keywords: 'switch enable yes' },
                { class: 'fas fa-toggle-off', name: 'Toggle Off', keywords: 'switch disable no' },
                
                // Miscellaneous
                { class: 'fas fa-cog', name: 'Settings', keywords: 'config gear options' },
                { class: 'fas fa-magic', name: 'Magic', keywords: 'wand special effect' },
                { class: 'fas fa-lightbulb', name: 'Idea', keywords: 'innovation creativity light' },
                { class: 'fas fa-trophy', name: 'Trophy', keywords: 'award winner achievement' },
                { class: 'fas fa-flag', name: 'Flag', keywords: 'country nation mark' },
                { class: 'fas fa-fire', name: 'Fire', keywords: 'hot trending popular' },
                { class: 'fas fa-rocket', name: 'Rocket', keywords: 'launch fast speed' },
                
                // Additional Common Icons
                { class: 'fas fa-address-book', name: 'Address Book', keywords: 'contacts directory phone' },
                { class: 'fas fa-address-card', name: 'Address Card', keywords: 'id contact information' },
                { class: 'fas fa-handshake', name: 'Handshake', keywords: 'agreement deal partnership' },
                { class: 'fas fa-id-card', name: 'ID Card', keywords: 'identification badge license' },
                { class: 'fas fa-birthday-cake', name: 'Birthday Cake', keywords: 'celebration cake party' },
                { class: 'fas fa-wine-glass', name: 'Wine Glass', keywords: 'drink alcohol celebration' },
                { class: 'fas fa-coffee', name: 'Coffee', keywords: 'drink cafe beverage' },
                { class: 'fas fa-pizza-slice', name: 'Pizza', keywords: 'food restaurant meal' },
                { class: 'fas fa-hamburger', name: 'Hamburger', keywords: 'food restaurant meal' },
                { class: 'fas fa-ice-cream', name: 'Ice Cream', keywords: 'dessert food sweet' },
                { class: 'fas fa-gamepad', name: 'Gaming', keywords: 'games controller entertainment' },
                { class: 'fas fa-football-ball', name: 'Football', keywords: 'sports game ball' },
                { class: 'fas fa-basketball-ball', name: 'Basketball', keywords: 'sports game ball' },
                { class: 'fas fa-tennis-ball', name: 'Tennis Ball', keywords: 'sports game ball' },
                { class: 'fas fa-running', name: 'Running', keywords: 'exercise fitness sports' },
                { class: 'fas fa-dumbbell', name: 'Dumbbell', keywords: 'exercise fitness gym' },
                { class: 'fas fa-spa', name: 'Spa', keywords: 'wellness relaxation health' },
                { class: 'fas fa-smile', name: 'Smile', keywords: 'happy emotion face' },
                { class: 'fas fa-frown', name: 'Frown', keywords: 'sad emotion face' },
                { class: 'fas fa-meh', name: 'Neutral Face', keywords: 'neutral emotion face' },
                { class: 'fas fa-mask', name: 'Mask', keywords: 'protection health safety' },
                { class: 'fas fa-temperature-high', name: 'Temperature High', keywords: 'fever health hot' },
                { class: 'fas fa-temperature-low', name: 'Temperature Low', keywords: 'cold health cool' },
                { class: 'fas fa-snowflake', name: 'Snowflake', keywords: 'winter cold weather' },
                { class: 'fas fa-sun', name: 'Sun', keywords: 'weather sunny bright' },
                { class: 'fas fa-moon', name: 'Moon', keywords: 'night dark lunar' },
                { class: 'fas fa-cloud-rain', name: 'Rain', keywords: 'weather storm water' },
                { class: 'fas fa-umbrella', name: 'Umbrella', keywords: 'rain weather protection' },
                { class: 'fas fa-mountain', name: 'Mountain', keywords: 'nature landscape peak' },
                { class: 'fas fa-tree', name: 'Tree', keywords: 'nature forest plant' },
                { class: 'fas fa-leaf', name: 'Leaf', keywords: 'nature plant green' },
                { class: 'fas fa-seedling', name: 'Seedling', keywords: 'plant growth nature' },
                { class: 'fas fa-paw', name: 'Paw', keywords: 'animal pet dog cat' },
                { class: 'fas fa-dog', name: 'Dog', keywords: 'pet animal canine' },
                { class: 'fas fa-cat', name: 'Cat', keywords: 'pet animal feline' },
                { class: 'fas fa-fish', name: 'Fish', keywords: 'animal water sea' },
                { class: 'fas fa-horse', name: 'Horse', keywords: 'animal riding equine' },
                { class: 'fas fa-dove', name: 'Dove', keywords: 'bird peace animal' },
                { class: 'fas fa-bug', name: 'Bug', keywords: 'insect error issue' },
                { class: 'fas fa-spider', name: 'Spider', keywords: 'insect web animal' },
                { class: 'fas fa-hammer', name: 'Hammer', keywords: 'tool build construction' },
                { class: 'fas fa-wrench', name: 'Wrench', keywords: 'tool fix repair' },
                { class: 'fas fa-screwdriver', name: 'Screwdriver', keywords: 'tool fix repair' },
                { class: 'fas fa-paint-brush', name: 'Paint Brush', keywords: 'art design creative' },
                { class: 'fas fa-palette', name: 'Palette', keywords: 'art color design' },
                { class: 'fas fa-gem', name: 'Gem', keywords: 'diamond precious jewel' },
                { class: 'fas fa-crown', name: 'Crown', keywords: 'king queen royalty' },
                { class: 'fas fa-award', name: 'Award', keywords: 'medal prize achievement' },
                { class: 'fas fa-medal', name: 'Medal', keywords: 'award prize winner' },
                { class: 'fas fa-ribbon', name: 'Ribbon', keywords: 'award decoration banner' },
                
                // More Communication & Contact Icons
                { class: 'fas fa-phone-alt', name: 'Phone Alt', keywords: 'call telephone contact mobile' },
                { class: 'fas fa-phone-square', name: 'Phone Square', keywords: 'call telephone contact' },
                { class: 'fas fa-fax', name: 'Fax', keywords: 'fax machine communication' },
                { class: 'fas fa-voicemail', name: 'Voicemail', keywords: 'message voice recording' },
                { class: 'fas fa-comment-dots', name: 'Comment Dots', keywords: 'chat message conversation' },
                { class: 'fas fa-comment-slash', name: 'Comment Slash', keywords: 'no chat mute message' },
                { class: 'fas fa-sms', name: 'SMS', keywords: 'text message mobile' },
                { class: 'fas fa-mail-bulk', name: 'Mail Bulk', keywords: 'newsletter mailing list' },
                
                // Technology & Digital
                { class: 'fas fa-server', name: 'Server', keywords: 'hosting technology database' },
                { class: 'fas fa-hard-drive', name: 'Hard Drive', keywords: 'storage disk technology' },
                { class: 'fas fa-microchip', name: 'Microchip', keywords: 'processor cpu technology' },
                { class: 'fas fa-memory', name: 'Memory', keywords: 'ram chip technology' },
                { class: 'fas fa-ethernet', name: 'Ethernet', keywords: 'network cable connection' },
                { class: 'fas fa-broadcast-tower', name: 'Broadcast Tower', keywords: 'signal antenna transmission' },
                { class: 'fas fa-satellite', name: 'Satellite', keywords: 'space communication signal' },
                { class: 'fas fa-router', name: 'Router', keywords: 'network internet device' },
                { class: 'fas fa-keyboard', name: 'Keyboard', keywords: 'input device typing' },
                { class: 'fas fa-mouse', name: 'Mouse', keywords: 'input device pointer' },
                { class: 'fas fa-print', name: 'Print', keywords: 'printer document output' },
                { class: 'fas fa-scanner', name: 'Scanner', keywords: 'scan document input' },
                { class: 'fas fa-tv', name: 'Television', keywords: 'monitor screen display' },
                { class: 'fas fa-mobile', name: 'Mobile Phone', keywords: 'smartphone device' },
                { class: 'fas fa-sim-card', name: 'SIM Card', keywords: 'mobile phone card' },
                { class: 'fas fa-sd-card', name: 'SD Card', keywords: 'memory storage card' },
                { class: 'fas fa-usb', name: 'USB', keywords: 'connector cable device' },
                { class: 'fas fa-plug', name: 'Power Plug', keywords: 'electricity power connector' },
                { class: 'fas fa-battery-full', name: 'Battery Full', keywords: 'power charge full' },
                { class: 'fas fa-battery-half', name: 'Battery Half', keywords: 'power charge medium' },
                { class: 'fas fa-battery-empty', name: 'Battery Empty', keywords: 'power charge low' },
                
                // More Actions & Navigation
                { class: 'fas fa-chevron-up', name: 'Chevron Up', keywords: 'arrow up direction' },
                { class: 'fas fa-chevron-down', name: 'Chevron Down', keywords: 'arrow down direction' },
                { class: 'fas fa-chevron-left', name: 'Chevron Left', keywords: 'arrow left direction' },
                { class: 'fas fa-chevron-right', name: 'Chevron Right', keywords: 'arrow right direction' },
                { class: 'fas fa-angle-up', name: 'Angle Up', keywords: 'arrow up direction small' },
                { class: 'fas fa-angle-down', name: 'Angle Down', keywords: 'arrow down direction small' },
                { class: 'fas fa-angle-left', name: 'Angle Left', keywords: 'arrow left direction small' },
                { class: 'fas fa-angle-right', name: 'Angle Right', keywords: 'arrow right direction small' },
                { class: 'fas fa-arrows-alt', name: 'Arrows Alt', keywords: 'expand resize move' },
                { class: 'fas fa-expand', name: 'Expand', keywords: 'fullscreen enlarge grow' },
                { class: 'fas fa-compress', name: 'Compress', keywords: 'minimize shrink reduce' },
                { class: 'fas fa-expand-arrows-alt', name: 'Expand Arrows', keywords: 'resize fullscreen' },
                { class: 'fas fa-external-link-alt', name: 'External Link', keywords: 'open new window link' },
                { class: 'fas fa-share', name: 'Share', keywords: 'social media distribute' },
                { class: 'fas fa-share-alt', name: 'Share Alt', keywords: 'social media distribute network' },
                { class: 'fas fa-redo', name: 'Redo', keywords: 'repeat again forward' },
                { class: 'fas fa-undo', name: 'Undo', keywords: 'reverse back previous' },
                { class: 'fas fa-sync', name: 'Sync', keywords: 'refresh reload update' },
                { class: 'fas fa-sync-alt', name: 'Sync Alt', keywords: 'refresh reload update rotate' },
                { class: 'fas fa-sort', name: 'Sort', keywords: 'arrange order organize' },
                { class: 'fas fa-sort-up', name: 'Sort Up', keywords: 'arrange ascending' },
                { class: 'fas fa-sort-down', name: 'Sort Down', keywords: 'arrange descending' },
                { class: 'fas fa-filter', name: 'Filter', keywords: 'search refine narrow' },
                { class: 'fas fa-random', name: 'Random', keywords: 'shuffle mix reorder' },
                
                // More Form Elements
                { class: 'fas fa-sliders-h', name: 'Sliders', keywords: 'range slider controls' },
                { class: 'fas fa-bars', name: 'Menu Bars', keywords: 'hamburger navigation menu' },
                { class: 'fas fa-ellipsis-h', name: 'More Options', keywords: 'dots menu options' },
                { class: 'fas fa-ellipsis-v', name: 'More Options Vertical', keywords: 'dots menu vertical' },
                { class: 'fas fa-grip-horizontal', name: 'Grip Horizontal', keywords: 'drag handle resize' },
                { class: 'fas fa-grip-vertical', name: 'Grip Vertical', keywords: 'drag handle vertical' },
                { class: 'fas fa-asterisk', name: 'Asterisk', keywords: 'required star important' },
                { class: 'fas fa-quote-left', name: 'Quote Left', keywords: 'testimonial citation' },
                { class: 'fas fa-quote-right', name: 'Quote Right', keywords: 'testimonial citation' },
                
                // More Status & Alerts
                { class: 'fas fa-check-circle', name: 'Check Circle', keywords: 'success approved valid' },
                { class: 'fas fa-times-circle', name: 'Times Circle', keywords: 'error cancel invalid' },
                { class: 'fas fa-exclamation-circle', name: 'Exclamation Circle', keywords: 'warning alert important' },
                { class: 'fas fa-minus-circle', name: 'Minus Circle', keywords: 'remove delete subtract' },
                { class: 'fas fa-plus-circle', name: 'Plus Circle', keywords: 'add create new' },
                { class: 'fas fa-question', name: 'Question', keywords: 'help support faq' },
                { class: 'fas fa-ban', name: 'Ban', keywords: 'prohibited forbidden stop' },
                { class: 'fas fa-stop-circle', name: 'Stop Circle', keywords: 'halt pause end' },
                { class: 'fas fa-play-circle', name: 'Play Circle', keywords: 'start begin video' },
                { class: 'fas fa-pause-circle', name: 'Pause Circle', keywords: 'stop wait break' },
                { class: 'fas fa-spinner', name: 'Spinner', keywords: 'loading progress wait' },
                { class: 'fas fa-circle-notch', name: 'Loading', keywords: 'spinner progress wait' },
                
                // Location & Places
                { class: 'fas fa-map-pin', name: 'Map Pin', keywords: 'location marker point' },
                { class: 'fas fa-map-marked', name: 'Map Marked', keywords: 'location navigation marked' },
                { class: 'fas fa-map-marked-alt', name: 'Map Marked Alt', keywords: 'location navigation pins' },
                { class: 'fas fa-directions', name: 'Directions', keywords: 'navigation route path' },
                { class: 'fas fa-location-arrow', name: 'Location Arrow', keywords: 'gps position direction' },
                { class: 'fas fa-street-view', name: 'Street View', keywords: 'maps google street' },
                { class: 'fas fa-hotel', name: 'Hotel', keywords: 'accommodation lodging stay' },
                { class: 'fas fa-hospital-alt', name: 'Hospital Alt', keywords: 'medical health clinic' },
                { class: 'fas fa-university', name: 'University', keywords: 'education college school' },
                { class: 'fas fa-church', name: 'Church', keywords: 'religion worship building' },
                { class: 'fas fa-mosque', name: 'Mosque', keywords: 'religion worship islam' },
                { class: 'fas fa-synagogue', name: 'Synagogue', keywords: 'religion worship judaism' },
                { class: 'fas fa-place-of-worship', name: 'Place of Worship', keywords: 'religion church temple' },
                { class: 'fas fa-gas-pump', name: 'Gas Station', keywords: 'fuel petrol station' },
                { class: 'fas fa-parking', name: 'Parking', keywords: 'car park space' },
                { class: 'fas fa-restroom', name: 'Restroom', keywords: 'bathroom toilet facilities' },
                
                // More Social & Brands
                { class: 'fab fa-google', name: 'Google', keywords: 'search engine brand' },
                { class: 'fab fa-apple', name: 'Apple', keywords: 'brand technology ios' },
                { class: 'fab fa-microsoft', name: 'Microsoft', keywords: 'brand technology windows' },
                { class: 'fab fa-amazon', name: 'Amazon', keywords: 'brand shopping ecommerce' },
                { class: 'fab fa-skype', name: 'Skype', keywords: 'video call communication' },
                { class: 'fab fa-discord', name: 'Discord', keywords: 'gaming chat communication' },
                { class: 'fab fa-slack', name: 'Slack', keywords: 'work communication team' },
                { class: 'fab fa-telegram', name: 'Telegram', keywords: 'messaging chat secure' },
                { class: 'fab fa-viber', name: 'Viber', keywords: 'messaging chat mobile' },
                { class: 'fab fa-snapchat', name: 'Snapchat', keywords: 'social media photos' },
                { class: 'fab fa-tiktok', name: 'TikTok', keywords: 'social media video' },
                { class: 'fab fa-pinterest', name: 'Pinterest', keywords: 'social media images' },
                { class: 'fab fa-reddit', name: 'Reddit', keywords: 'social media forum' },
                { class: 'fab fa-tumblr', name: 'Tumblr', keywords: 'blogging social media' },
                { class: 'fab fa-twitch', name: 'Twitch', keywords: 'streaming gaming video' },
                { class: 'fab fa-vimeo', name: 'Vimeo', keywords: 'video streaming platform' },
                { class: 'fab fa-spotify', name: 'Spotify', keywords: 'music streaming audio' },
                { class: 'fab fa-soundcloud', name: 'SoundCloud', keywords: 'music audio streaming' },
                
                // Food & Restaurants  
                { class: 'fas fa-utensils', name: 'Utensils', keywords: 'restaurant food dining' },
                { class: 'fas fa-wine-bottle', name: 'Wine Bottle', keywords: 'alcohol drink beverage' },
                { class: 'fas fa-beer', name: 'Beer', keywords: 'alcohol drink beverage' },
                { class: 'fas fa-cocktail', name: 'Cocktail', keywords: 'drink alcohol beverage' },
                { class: 'fas fa-apple-alt', name: 'Apple Fruit', keywords: 'fruit food healthy' },
                { class: 'fas fa-lemon', name: 'Lemon', keywords: 'fruit food citrus' },
                { class: 'fas fa-carrot', name: 'Carrot', keywords: 'vegetable food healthy' },
                { class: 'fas fa-pepper-hot', name: 'Hot Pepper', keywords: 'spicy food vegetable' },
                { class: 'fas fa-bread-slice', name: 'Bread', keywords: 'food bakery carb' },
                { class: 'fas fa-cheese', name: 'Cheese', keywords: 'food dairy protein' },
                { class: 'fas fa-egg', name: 'Egg', keywords: 'food protein breakfast' },
                
                // Time & Scheduling
                { class: 'fas fa-calendar-check', name: 'Calendar Check', keywords: 'schedule completed task' },
                { class: 'fas fa-calendar-plus', name: 'Calendar Plus', keywords: 'add event schedule' },
                { class: 'fas fa-calendar-minus', name: 'Calendar Minus', keywords: 'remove event schedule' },
                { class: 'fas fa-calendar-times', name: 'Calendar Times', keywords: 'cancel event schedule' },
                { class: 'fas fa-calendar-week', name: 'Calendar Week', keywords: 'weekly schedule view' },
                { class: 'fas fa-hourglass', name: 'Hourglass', keywords: 'time waiting duration' },
                { class: 'fas fa-hourglass-start', name: 'Hourglass Start', keywords: 'time beginning start' },
                { class: 'fas fa-hourglass-half', name: 'Hourglass Half', keywords: 'time progress middle' },
                { class: 'fas fa-hourglass-end', name: 'Hourglass End', keywords: 'time finished complete' },
                { class: 'fas fa-alarm-clock', name: 'Alarm Clock', keywords: 'wake up reminder time' },
                { class: 'fas fa-business-time', name: 'Business Time', keywords: 'work schedule office' },
                
                // More File Types
                { class: 'fas fa-file-image', name: 'Image File', keywords: 'photo picture media file' },
                { class: 'fas fa-file-video', name: 'Video File', keywords: 'movie media file' },
                { class: 'fas fa-file-audio', name: 'Audio File', keywords: 'music sound media file' },
                { class: 'fas fa-file-code', name: 'Code File', keywords: 'programming development file' },
                { class: 'fas fa-file-archive', name: 'Archive File', keywords: 'zip compressed file' },
                { class: 'fas fa-file-csv', name: 'CSV File', keywords: 'spreadsheet data file' },
                { class: 'fas fa-file-powerpoint', name: 'PowerPoint File', keywords: 'presentation slide file' },
                { class: 'fas fa-file-contract', name: 'Contract File', keywords: 'legal document agreement' },
                { class: 'fas fa-file-signature', name: 'Signature File', keywords: 'signed document legal' },
                { class: 'fas fa-file-invoice', name: 'Invoice File', keywords: 'bill payment document' },
                { class: 'fas fa-file-download', name: 'Download File', keywords: 'get save receive' },
                { class: 'fas fa-file-upload', name: 'Upload File', keywords: 'send attach share' },
                
                // More Weather
                { class: 'fas fa-cloud-sun', name: 'Partly Cloudy', keywords: 'weather mixed sunny' },
                { class: 'fas fa-cloud-moon', name: 'Cloudy Night', keywords: 'weather night overcast' },
                { class: 'fas fa-bolt', name: 'Lightning', keywords: 'storm thunder weather' },
                { class: 'fas fa-wind', name: 'Wind', keywords: 'weather breeze air' },
                { class: 'fas fa-rainbow', name: 'Rainbow', keywords: 'weather colorful arc' },
                
                // More Tools & Objects
                { class: 'fas fa-toolbox', name: 'Toolbox', keywords: 'repair maintenance kit' },
                { class: 'fas fa-drill', name: 'Drill', keywords: 'tool construction repair' },
                { class: 'fas fa-saw', name: 'Saw', keywords: 'tool construction cut' },
                { class: 'fas fa-ruler', name: 'Ruler', keywords: 'measure tool length' },
                { class: 'fas fa-compass-drafting', name: 'Drafting Compass', keywords: 'drawing tool geometry' },
                { class: 'fas fa-scissors', name: 'Scissors', keywords: 'cut tool office' },
                { class: 'fas fa-paperclip', name: 'Paperclip', keywords: 'attach office document' },
                { class: 'fas fa-thumbtack', name: 'Thumbtack', keywords: 'pin attach notice' },
                { class: 'fas fa-stapler', name: 'Stapler', keywords: 'office attach papers' },
                { class: 'fas fa-tape', name: 'Tape', keywords: 'adhesive stick office' },
                { class: 'fas fa-eraser', name: 'Eraser', keywords: 'remove delete correct' },
                { class: 'fas fa-highlighter', name: 'Highlighter', keywords: 'mark important text' },
                { class: 'fas fa-marker', name: 'Marker', keywords: 'write draw color' },
                { class: 'fas fa-pen-fancy', name: 'Fancy Pen', keywords: 'write signature elegant' },
                { class: 'fas fa-feather', name: 'Feather', keywords: 'write quill pen' },
                
                // More Miscellaneous
                { class: 'fas fa-anchor', name: 'Anchor', keywords: 'naval marine ship' },
                { class: 'fas fa-wheel', name: 'Wheel', keywords: 'tire circle rotation' },
                { class: 'fas fa-cogs', name: 'Cogs', keywords: 'settings gears multiple' },
                { class: 'fas fa-puzzle-piece', name: 'Puzzle Piece', keywords: 'solution fit together' },
                { class: 'fas fa-dice', name: 'Dice', keywords: 'game random chance' },
                { class: 'fas fa-chess', name: 'Chess', keywords: 'game strategy board' },
                { class: 'fas fa-cards', name: 'Playing Cards', keywords: 'game deck cards' },
                { class: 'fas fa-music-note', name: 'Music Note', keywords: 'audio sound melody' },
                { class: 'fas fa-theater-masks', name: 'Theater Masks', keywords: 'drama performance art' },
                { class: 'fas fa-masks-theater', name: 'Drama Masks', keywords: 'comedy tragedy theater' },
                { class: 'fas fa-guitar', name: 'Guitar', keywords: 'music instrument string' },
                { class: 'fas fa-drum', name: 'Drum', keywords: 'music instrument percussion' },
                { class: 'fas fa-microphone', name: 'Microphone', keywords: 'audio record voice' },
                { class: 'fas fa-headphones', name: 'Headphones', keywords: 'audio listen music' },
                { class: 'fas fa-volume-up', name: 'Volume Up', keywords: 'audio sound loud' },
                { class: 'fas fa-volume-down', name: 'Volume Down', keywords: 'audio sound quiet' },
                { class: 'fas fa-volume-mute', name: 'Volume Mute', keywords: 'audio sound off' },
                { class: 'fas fa-volume-off', name: 'Volume Off', keywords: 'audio sound silent' }
            ];
        },

        selectIcon: function(iconClass) {
            this.value = iconClass;
            this.showIconPicker = false;
        },

        clearIcon: function() {
            this.value = '';
            this.showIconPicker = false;
        },

        togglePicker: function() {
            this.showIconPicker = !this.showIconPicker;
        },

        handleClickOutside: function(event) {
            if (!this.$el.contains(event.target)) {
                this.showIconPicker = false;
            }
        }
    },

    beforeDestroy: function() {
        document.removeEventListener('click', this.handleClickOutside);
    }
});
Vue.component('field-multiselect', {
    template: '#tmpl-wpuf-field-multiselect',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                if ( ! value ) {
                    value = [];
                }

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        },

        // Dynamic options for taxonomy fields
        dynamic_options: function () {
            // Check if this is a Selection Terms field for a taxonomy
            if (this.option_field.name === 'exclude' && 
                this.editing_form_field && 
                this.editing_form_field.input_type === 'taxonomy' &&
                this.editing_form_field.name) {
                
                var taxonomy_name = this.editing_form_field.name;
                
                // Look for terms in the wp_post_types data
                if (wpuf_form_builder && wpuf_form_builder.wp_post_types) {
                    for (var post_type in wpuf_form_builder.wp_post_types) {
                        var taxonomies = wpuf_form_builder.wp_post_types[post_type];
                        
                        if (taxonomies && taxonomies.hasOwnProperty(taxonomy_name)) {
                            var tax_field = taxonomies[taxonomy_name];
                            
                            if (tax_field && tax_field.terms && tax_field.terms.length > 0) {
                                var options = {};
                                tax_field.terms.forEach(function(term) {
                                    if (term && term.term_id && term.name) {
                                        options[term.term_id] = term.name;
                                    }
                                });
                                return options;
                            }
                        }
                    }
                }
            }
            
            // Return original options if not a taxonomy field or no dynamic options found
            return this.option_field.options || {};
        }
    },

    mounted: function () {
        this.bind_selectize();
    },

    watch: {
        dynamic_options: function () {
            // Refresh selectize when options change
            this.$nextTick(function () {
                this.bind_selectize();
            });
        }
    },

    methods: {
        bind_selectize: function () {
            var self = this;

            // Destroy existing selectize if it exists
            var $select = $(this.$el).find('.term-list-selector');
            if ($select[0] && $select[0].selectize) {
                $select[0].selectize.destroy();
            }

            $select.selectize({}).on('change', function () {
                self.value = $( this ).val();
            });
        },
    },

});

/**
 * Common settings component for option based fields
 * like select, multiselect, checkbox, radio
 */
Vue.component('field-option-data', {
    template: '#tmpl-wpuf-field-option-data',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            show_value: false,
            sync_value: true,
            options: [],
            selected: [],
            display: !this.editing_form_field.hide_option_data // hide this field for the events calendar
        };
    },

    computed: {
        field_options: function () {
            return this.editing_form_field.options;
        },

        field_selected: function () {
            return this.editing_form_field.selected;
        }
    },

    mounted: function () {
        var self = this;

        this.set_options();

        $(this.$el).find('.option-field-option-chooser').sortable({
            items: '.option-field-option',
            handle: '.sort-handler',
            update: function (e, ui) {
                var item        = ui.item[0],
                    data        = item.dataset,
                    toIndex     = parseInt($(ui.item).index()),
                    fromIndex   = parseInt(data.index);

                self.options.swap(fromIndex, toIndex);
            }
        });
    },

    methods: {
        set_options: function () {
            var self = this;
            var field_options = $.extend(true, {}, this.editing_form_field.options);

            _.each(field_options, function (label, value) {
                self.options.push({label: label, value: value, id: self.get_random_id()});
            });

            if (this.option_field.is_multiple && !_.isArray(this.field_selected)) {
                this.selected = [this.field_selected];
            } else {
                this.selected = this.field_selected;
            }
        },

        // in case of select or radio buttons, user should deselect default value
        clear_selection: function () {
            this.selected = null;
        },

        add_option: function () {
            var count   = this.options.length,
                new_opt = this.i18n.option + '-' + (count + 1);

            this.options.push({
                label: new_opt , value: new_opt, id: this.get_random_id()
            });
        },

        delete_option: function (index) {
            if (this.options.length === 1) {
                this.warn({
                    text: this.i18n.last_choice_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.options.splice(index, 1);
        },

        set_option_label: function (index, label) {
            if (this.sync_value) {
                this.options[index].value = label.toLocaleLowerCase().replace( /\s/g, '_' );
            }
        }
    },

    watch: {
        options: {
            deep: true,
            handler: function (new_opts) {
                var options = {},
                    i = 0;

                for (i = 0; i < new_opts.length; i++) {
                    options['' + new_opts[i].value] = new_opts[i].label;
                }

                this.update_value('options', options);
            }
        },

        selected: function (new_val) {
            this.update_value('selected', new_val);
        }
    }
});

Vue.component('field-option-pro-feature-alert', {
    template: '#tmpl-wpuf-field-option-pro-feature-alert',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    computed: {
        pro_link: function () {
            return wpuf_form_builder.pro_link;
        }
    }
});

/**
 * Sidebar field options panel
 */
Vue.component('field-options', {
    template: '#tmpl-wpuf-field-options',

    mixins: [wpuf_mixins.field_options, wpuf_mixins.form_field_mixin],

    data: function() {
        return {
            show_basic_settings: true,
            show_advanced_settings: false,
            show_quiz_settings: false
        };
    },

    computed: {
        editing_field_id: function () {
            this.show_basic_settings = true;
            this.show_advanced_settings = false;
            this.show_quiz_settings = false;

            return parseInt(this.$store.state.editing_field_id);
        },

        editing_form_field: function () {
            var self = this,
                i = 0;

            for (i = 0; i < self.$store.state.form_fields.length; i++) {
                // check if the editing field exist in normal fields
                if (self.$store.state.form_fields[i].id === parseInt(self.editing_field_id)) {
                    return self.$store.state.form_fields[i];
                }

                // check if the editing field belong to column field or repeat field
                if (self.$store.state.form_fields[i].template.match(/^(column|repeat)_field$/)) {
                    var innerFields = self.$store.state.form_fields[i].inner_fields;

                    // Handle column fields (inner_fields is an object with column keys)
                    if (self.$store.state.form_fields[i].template === 'column_field') {
                        for (const columnFields in innerFields) {
                            if (innerFields.hasOwnProperty(columnFields)) {
                                var columnFieldIndex = 0;

                                while (columnFieldIndex < innerFields[columnFields].length) {
                                    if (innerFields[columnFields][columnFieldIndex].id === self.editing_field_id) {
                                        return innerFields[columnFields][columnFieldIndex];
                                    }
                                    columnFieldIndex++;
                                }
                            }
                        }
                    }
                    
                    // Handle repeat fields (inner_fields is an array)
                    if (self.$store.state.form_fields[i].template === 'repeat_field') {
                        if (Array.isArray(innerFields)) {
                            for (var repeatFieldIndex = 0; repeatFieldIndex < innerFields.length; repeatFieldIndex++) {
                                if (innerFields[repeatFieldIndex].id === self.editing_field_id) {
                                    return innerFields[repeatFieldIndex];
                                }
                            }
                        }
                    }
                }

            }
        },

        settings: function() {
            if (!this.editing_form_field) {
                return [];
            }
            
            var settings = [],
                template = this.editing_form_field.template;

            if (_.isFunction(this['settings_' + template])) {
                settings = this['settings_' + template].call(this, this.editing_form_field);
            } else {
                settings = this.$store.state.field_settings[template].settings;
            }

            return _.sortBy(settings, function (item) {
                return parseInt(item.priority);
            });
        },

        basic_settings: function () {
            return this.settings.filter(function (item) {
                return 'basic' === item.section;
            });
        },

        advanced_settings: function () {
            return this.settings.filter(function (item) {
                return 'advanced' === item.section;
            });
        },

        quiz_settings: function () {
            return this.settings.filter(function (item) {
                return 'quiz' === item.section;
            });
        },

        form_field_type_title: function() {
            if (!this.editing_form_field) {
                return '';
            }
            
            var template = this.editing_form_field.template;

            if (_.isFunction(this['form_field_' + template + '_title'])) {
                return this['form_field_' + template + '_title'].call(this, this.editing_form_field);
            }

            return this.$store.state.field_settings[template].title;
        },

        form_settings: function () {
            return this.$store.state.settings;
        }
    },

    watch: {
        form_settings: function () {
            return this.$store.state.settings;
        }
    }
});

Vue.component('field-radio', {
    template: '#tmpl-wpuf-field-radio',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        }
    }
});

Vue.component('field-range', {
    template: '#tmpl-wpuf-field-range',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.update_value(this.option_field.name, value);
            }
        },

        minColumn: function () {
            return this.editing_form_field.min_column;
        },

        maxColumn: function () {
            return this.editing_form_field.max_column;
        }
    },

    methods: {
    }
});

Vue.component('field-select', {
    template: '#tmpl-wpuf-field-select',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    data: function () {
        return {
            showOptions: false,
            selectedOption: 'Select an option',
        };
    },

    mounted: function() {
        // Initialize selectedOption when component mounts
        this.initializeSelectedOption();
    },

    watch: {
        value: {
            handler: function(newVal) {
                // Update selectedOption when value changes
                
                this.initializeSelectedOption();
            },
            immediate: true
        },
        'editing_form_field': {
            handler: function(newVal, oldVal) {
                // When the entire editing_form_field object changes (like on data load)
                this.initializeSelectedOption();
            },
            deep: true
        },
        'option_field.options': {
            handler: function(newVal) {
                // When options change, reinitialize
                this.initializeSelectedOption();
            },
            deep: true
        }
    },

    methods: {
        initializeSelectedOption: function() {
            var self = this;
            this.$nextTick(function() {
                // Get the current value
                var currentValue = self.editing_form_field[self.option_field.name];
                
                if (currentValue && self.option_field.options && self.option_field.options[currentValue]) {
                    self.selectedOption = self.option_field.options[currentValue];
                } else if (!currentValue && self.option_field.default && self.option_field.options && self.option_field.options[self.option_field.default]) {
                    // If no value but there's a default, show the default
                    self.selectedOption = self.option_field.options[self.option_field.default];
                    // Also set the value to default if there's no current value
                    if (!currentValue) {
                        self.value = self.option_field.default;
                    }
                } else {
                    self.selectedOption = 'Select an option';
                }
            });
        }
    },

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        },
    }
});

Vue.component('field-text', {
    template: '#tmpl-wpuf-field-text',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.update_value(this.option_field.name, value);
            }
        }
    },

    methods: {
        on_focusout: function (e) {
            wpuf_form_builder.event_hub.$emit('field-text-focusout', e, this);
        },
        on_keyup: function (e) {
            wpuf_form_builder.event_hub.$emit('field-text-keyup', e, this);
        }
    }
});

Vue.component('field-text-meta', {
    template: '#tmpl-wpuf-field-text-meta',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.update_value(this.option_field.name, value);
            }
        }
    },

    created: function () {
        if ('yes' === this.editing_form_field.is_meta) {
            if (!this.value) {
                this.value = this.editing_form_field.label.replace(/\W/g, '_').toLowerCase();
            }

            wpuf_form_builder.event_hub.$on('field-text-keyup', this.meta_key_autocomplete);
        }
    },

    methods: {
        meta_key_autocomplete: function (e, label_vm) {
            if (
                'label' === label_vm.option_field.name &&
                parseInt(this.editing_form_field.id) === parseInt(label_vm.editing_form_field.id)
            ) {
                this.value = label_vm.value.replace(/\W/g, '_').toLowerCase();
            }
        }
    }
});

Vue.component('field-textarea', {
    template: '#tmpl-wpuf-field-textarea',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.update_value(this.option_field.name, value);
            }
        }
    },
});

Vue.component('field-visibility', {
    template: '#tmpl-wpuf-field-visibility',

    mixins: [
        wpuf_mixins.option_field_mixin,
        wpuf_mixins.form_field_mixin,
    ],

    computed: {
        selected: {
            get: function () {

                return this.editing_form_field[this.option_field.name].selected;
            },

            set: function (value) {

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: {
                        selected: value,
                        choices: [],
                    }
                });
            }
        },

        choices: {
            get: function () {
                return this.editing_form_field[this.option_field.name].choices;
            },

            set: function (value) {

                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: {
                        selected: this.selected,
                        choices: value,
                    }
                });
            }
        },

    },

    methods: {

    },

    watch: {
    	selected: function (new_val) {
            this.update_value('selected', new_val);
        }
    }
});

/**
 * Field template: Checkbox
 */
Vue.component('form-checkbox_field', {
    template: '#tmpl-wpuf-form-checkbox_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

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

/**
 * Field template: Column Field
 */
const mixins = [
    wpuf_mixins.form_field_mixin,
    wpuf_mixins.add_form_field
];

if (window.wpuf_forms_mixin_builder_stage) {
    mixins.push(window.wpuf_forms_mixin_builder_stage);
}

if (window.weforms_mixin_builder_stage) {
    mixins.push(window.weforms_mixin_builder_stage);
}

Vue.component('form-column_field', {
    template: '#tmpl-wpuf-form-column_field',

    mixins: mixins,

    mounted() {
        this.resizeColumns(this.field.columns);

        // bind jquery ui draggable
        var self = this,
            sortableFields = $(self.$el).find('.wpuf-column-inner-fields .wpuf-column-fields-sortable-list'),
            sortableTriggered = 1,
            columnFieldArea = $('.wpuf-field-columns'),
            columnFields = $(self.$el).find(".wpuf-field-columns .wpuf-column-inner-fields");

        columnFieldArea.mouseenter(function() {
            self.resizeColumns(self.field.columns);
        });

        columnFieldArea.mouseleave(function() {
            columnFields.unbind( "mouseup" );
            columnFields.unbind( "mousemove" );
        });

        // bind jquery ui sortable
        $(sortableFields).sortable({
            placeholder: 'form-preview-stage-dropzone',
            connectWith: sortableFields,
            items: '.column-field-items',
            handle: '.wpuf-column-field-control-buttons .move',
            scroll: true,
            stop: function( event, ui ) {
                var item        = ui.item[0];
                var data        = item.dataset;
                var data_source = data.source;

                if ('panel' === data_source) {
                    var payload = {
                        toIndex: parseInt($(ui.item).index()),
                        field_template: data.formField,
                        to_column: $(this).parent().data('column')
                    };

                    self.add_column_inner_field(payload);

                    // remove button from stage
                    $(this).find('.wpuf-field-button').remove();
                }
            },
            update: function (e, ui) {
                var item    = ui.item[0],
                    data    = item.dataset,
                    source  = data.source,
                    toIndex = parseInt($(ui.item).index()),
                    payload = {
                        toIndex: toIndex
                    };

                if ( 'column-field-stage' === source) {
                    payload.field_id   = self.field.id;
                    payload.fromIndex  = parseInt(item.attributes['column-field-index'].value);
                    payload.fromColumn = item.attributes['in-column'].value;
                    payload.toColumn   = $(item).parent().parent().attr('class').split(' ')[0];

                    // when drag field one column to another column, sortable event trigger twice and try to swap field twice.
                    // So the following conditions are needed to check and run swap_column_field_elements commit only once
                    if (payload.fromColumn !== payload.toColumn && sortableTriggered === 1) {
                        sortableTriggered = 0;
                    }else{
                        sortableTriggered++;
                    }

                    if (payload.fromColumn === payload.toColumn) {
                        sortableTriggered = 1;
                    }

                    if (sortableTriggered === 1) {
                        self.$store.commit('swap_column_field_elements', payload);
                    }
                }
            }
        });
    },

    computed: {
        column_fields: function () {
            return this.field.inner_fields;
        },

        innerColumns() {
            return this.field.columns;
        },

        editing_form_id: function () {
            return this.$store.state.editing_field_id;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        action_button_classes: function() {
            return 'hover:wpuf-cursor-pointer hover:wpuf-text-white wpuf-flex wpuf-mr-2';
        },

        columnClasses: function() {
            var columns_count = parseInt( this.field.columns );
            var columns = [];

            for (var i = 1; i <= columns_count; i++) {
                columns.push('column-' + i);
            }

            return columns;
        }
    },

    methods: {
        is_template_available: function (field) {
            var template = field.template;

            if (this.field_settings[template]) {
                if (this.is_pro_preview(template)) {
                    return false;
                }

                return true;
            }

            // for example see 'mixin_builder_stage' mixin's 'is_taxonomy_template_available' method
            if (_.isFunction(this['is_' + template + '_template_available'])) {
                return this['is_' + template + '_template_available'].call(this, field);
            }

            return false;
        },

        is_pro_feature: function (template) {
            return (this.field_settings[template] && this.field_settings[template].pro_feature) ? true : false;
        },

        get_field_name: function (template) {
            return this.field_settings[template].title;
        },

        is_full_width: function (template) {
            if (this.field_settings[template] && this.field_settings[template].is_full_width) {
                return true;
            }

            return false;
        },

        is_invisible: function (field) {
            return ( field.recaptcha_type && 'invisible_recaptcha' === field.recaptcha_type ) ? true : false;
        },

        isAllowedInColumnField: function(field_template) {
            var restrictedFields = ['column_field', 'custom_hidden_field', 'step_start'];

            if ( $.inArray(field_template, restrictedFields) >= 0 ) {
                return true;
            }

            return false;
        },

        add_column_inner_field(data) {
            var payload = {
                toWhichColumnField: this.field.id,
                toWhichColumnFieldMeta: this.field.name,
                toIndex: data.toIndex,
                toWhichColumn: data.to_column
            };

            if (this.isAllowedInColumnField(data.field_template)) {
                Swal.fire({
                    title: '<span class="wpuf-text-primary">Oops...</span>',
                    html: '<p class="wpuf-text-gray-500 wpuf-text-xl wpuf-m-0 wpuf-p-0">You cannot add this field as inner column field</p>',
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

            // check if these are already inserted
            if ( this.isSingleInstance( data.field_template ) && this.containsField( data.field_template ) ) {
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

            var field = $.extend(true, {}, this.$store.state.field_settings[data.field_template].field_props),
            form_fields = this.$store.state.form_fields;

            field.id = this.get_random_id();

            if ('yes' === field.is_meta && !field.name && field.label) {
                field.name = field.label.replace(/\W/g, '_').toLowerCase();

                var same_template_fields = form_fields.filter(function (form_field) {
                    return (form_field.template === field.template);
                });

                if (same_template_fields) {
                    field.name += '_' + this.get_random_id();
                }
            }

            payload.field = field;

            // add new form element
            this.$store.commit('add_column_inner_field_element', payload);
        },

        moveFieldsTo(column) {
            var payload = {
                field_id: this.field.id,
                move_to : column,
                inner_fields: this.getInnerFields()
            };

            // clear inner fields & push mergedFields to column-1
            this.$store.commit('move_column_inner_fields', payload);
        },

        getInnerFields() {
            return this.field.inner_fields;
        },

        open_column_field_settings: function(field, index, column) {
            var self = this,
                payload = {
                    field_id: self.field.id,
                    column_field: field,
                    index: index,
                    column: column,
                };
            self.$store.commit('open_column_field_settings', payload);
        },

        clone_column_field: function(field, index, column) {
            var self = this,
                payload = {
                    field_id: self.field.id,
                    column_field_id: field.id,
                    index: index,
                    toColumn: column,
                    new_id: self.get_random_id()
                };

            // check if the field is allowed to duplicate
            if ( self.isSingleInstance( field.template ) ) {
                Swal.fire({
                    title: "Oops...",
                    text: "You already have this field in the form"
                });
                return;
            }

            self.$store.commit('clone_column_field_element', payload);
        },

        delete_column_field: function(index, fromColumn) {
            var self = this,
                payload = {
                    field_id: self.field.id,
                    index: index,
                    fromColumn: fromColumn
                };

            const icon_delete  = wpuf_admin_script.asset_url + '/images/delete-icon-rounded.svg';
            const delete_icon_html = '<img src="' + icon_delete + '" alt="delete">';

            (Swal.fire({
                title: self.i18n.delete_field_warn_title,
                html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' +  self.i18n.delete_field_warn_msg + '</span>',
                iconHtml: delete_icon_html,
                showCancelButton: true,
                confirmButtonText: self.i18n.yes_delete_it,
                cancelButtonText: self.i18n.no_cancel_it,
                cancelButtonColor: '#fff',
                confirmButtonColor: '#EF4444',
                reverseButtons: true
            })).then((result) => {
                if (result.isConfirmed) {
                    self.$store.commit('delete_column_field_element', payload);
                }
            });
        },

        resizeColumns(columnsNumber) {
            var self = this;

            (function () {
                var columnElement;
                var startOffset;
                var columnField = $(self.$el).parent();
                var total_width = parseInt($(columnField).width());

                Array.prototype.forEach.call(
                    $(self.$el).find(".wpuf-column-field-inner-columns .wpuf-column-inner-fields"),

                    function (column) {
                        column.style.position = 'relative';

                        var grip = document.createElement('div');
                        grip.innerHTML = "&nbsp;";
                        grip.style.top = 0;
                        grip.style.right = 0;
                        grip.style.bottom = 0;
                        grip.style.width = '5px';
                        grip.style.position = 'absolute';
                        grip.style.cursor = 'col-resize';
                        grip.addEventListener('mousedown', function (e) {
                            columnElement = column;
                            startOffset = column.offsetWidth - e.pageX;
                        });

                        column.appendChild(grip);
                    });

                $(self.$el).find(".wpuf-column-field-inner-columns .wpuf-column-inner-fields").mousemove(function( e ) {
                    if (columnElement) {
                    var currentColumnWidth = startOffset + e.pageX;

                    columnElement.style.width = (100*currentColumnWidth) / total_width + '%';
                    }
                });

                $(self.$el).find(".wpuf-column-field-inner-columns .wpuf-column-inner-fields").mouseup(function() {
                    let colOneWidth   = 0,
                        colTwoWidth   = 0,
                        colThreeWidth = 0;

                    if (parseInt(columnsNumber) === 3) {
                        colOneWidth = 100 / columnsNumber;
                        colTwoWidth = 100 / columnsNumber;
                        colThreeWidth = 100 / columnsNumber;
                    } else if (parseInt(columnsNumber) === 2) {
                        colOneWidth = 100 / columnsNumber;
                        colTwoWidth = 100 / columnsNumber;
                        colThreeWidth = 0;
                    } else {
                        colOneWidth = 100;
                        colTwoWidth = 0;
                        colThreeWidth = 0;
                    }

                    self.field.inner_columns_size['column-1'] = colOneWidth + '%';
                    self.field.inner_columns_size['column-2'] = colTwoWidth + '%';
                    self.field.inner_columns_size['column-3'] = colThreeWidth + '%';

                    columnElement = undefined;
                });
            })();
        }
    },

    watch: {
        innerColumns(new_value) {
            var columns = parseInt(new_value),
                columns_size = this.field.inner_columns_size;

            Object.keys(columns_size).forEach(function (column) {
                if (columns === 1) {
                    columns_size[column] = '100%';
                }

                if (columns === 2) {
                    columns_size[column] = '50%';
                }

                if (columns === 3) {
                    columns_size[column] = '33.33%';
                }
            });

            // if columns number reduce to 1 then move other column fields to the first column
            if ( columns === 1 ) {
                this.moveFieldsTo( "column-1" );
            }

            // if columns number reduce to 2 then move column-2 and column-3 fields to the column-2
            if ( columns === 2 ) {
                this.moveFieldsTo( "column-2" );
            }

            this.resizeColumns(columns);
        }
    }
});

/**
 * Field template: Hidden
 */
Vue.component('form-custom_hidden_field', {
    template: '#tmpl-wpuf-form-custom_hidden_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Custom HTML
 */
Vue.component('form-custom_html', {
    template: '#tmpl-wpuf-form-custom_html',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            raw_html: '<p>from data</p>'
        };
    }
});

/**
 * Field template: Dropdown/Select
 */
Vue.component('form-dropdown_field', {
    template: '#tmpl-wpuf-form-dropdown_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Email
 */
Vue.component('form-email_address', {
    template: '#tmpl-wpuf-form-email_address',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Featured Image
 */
Vue.component('form-featured_image', {
    template: '#tmpl-wpuf-form-featured_image',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#tmpl-wpuf-form-fields',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.form_fields).concat(wpuf_mixins.add_form_field),

    computed: {
        panel_sections: function () {
            return this.$store.state.panel_sections;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        form_fields: function () {
            return this.$store.state.form_fields;
        }
    },

    mounted: function () {
        // bind jquery ui draggable
        $(this.$el).find('.panel-form-field-buttons .button').draggable({
            connectToSortable: '#form-preview-stage, #form-preview-stage .wpuf-form, .wpuf-column-inner-fields .wpuf-column-fields-sortable-list, .wpuf-repeat-fields-sortable-list',
            helper: 'clone',
            revert: 'invalid',
            cancel: '.button-faded',
        }).disableSelection();
    },

    methods: {
        panel_toggle: function (index) {
            this.$store.commit('panel_toggle', index);
        },

        is_pro_feature: function (field) {
            return this.field_settings[field].pro_feature;
        },

        alert_pro_feature: function (field) {
            var title = this.field_settings[field].title;

            Swal.fire({
                title: '<i class="fa fa-lock"></i> ' + title + ' <br>' + this.i18n.is_a_pro_feature,
                text: this.i18n.pro_feature_msg,
                icon: '',
                showCancelButton: true,
                cancelButtonText: this.i18n.close,
                confirmButtonColor: '#059669',
                confirmButtonText: this.i18n.upgrade_to_pro
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.open(wpuf_form_builder.pro_link, '_blank');
                }

            }, function() {});
        },

        alert_invalidate_msg: function (field) {
            var validator = this.field_settings[field].validator;

            if (validator && validator.msg) {
                this.warn({
                    title: validator.msg_title || '',
                    html: validator.msg,
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#46b450',
                    confirmButtonText: this.i18n.ok
                });
            }
        },

        get_invalidate_btn_class: function (field) {
            return this.field_settings[field].validator.button_class;
        }
    }
});

/**
 * Sidebar form fields panel
 */
Vue.component('form-fields-v4-1', {
    template: '#tmpl-wpuf-form-fields-v4-1',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.form_fields).concat(wpuf_mixins.add_form_field),

    data: function () {
        return {
            searched_fields: '',
            is_pro_active: wpuf_form_builder.is_pro_active,
        };
    },

    computed: {
        panel_sections: function () {
            return this.$store.state.panel_sections;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },

        form_fields: function () {
            return this.$store.state.form_fields;
        },
    },

    mounted: function () {
        var self = this;

        // Bind jquery ui draggable. But first destroy any previous binding
        Vue.nextTick(function () {
            var buttons = $(self.$el).find('.panel-form-field-buttons .wpuf-field-button');

            buttons.each(function () {
                if ($(this).draggable('instance')) {
                    $(this).draggable('destroy');
                }
            });

            buttons.draggable({
                connectToSortable: '#form-preview-stage, #form-preview-stage .wpuf-form, .wpuf-column-inner-fields .wpuf-column-fields-sortable-list, .wpuf-repeat-fields-sortable-list',
                helper: 'clone',
                revert: 'invalid',
                cancel: '.button-faded',
            }).disableSelection();
        });
    },

    methods: {
        panel_toggle: function (index) {
            this.$store.commit('panel_toggle', index);
        },

        is_pro_feature: function (field) {
            return this.field_settings[field].pro_feature;
        },

        alert_pro_feature: function (field) {
            var title = this.field_settings[field].title;
            var iconHtml = '';

            if ( this.i18n.pro_field_message[field] ) {
                switch ( this.i18n.pro_field_message[field].asset_type ) {
                    case 'image':
                        iconHtml = `<img src="${this.i18n.pro_field_message[field].asset_url}" alt="${field}" loading="lazy" onload="this.closest('div').classList.add('wpuf-is-loaded')">`;
                        break;

                    case 'video':
                        iconHtml = `<iframe onload="this.closest('div').classList.add('wpuf-is-loaded')" class="wpuf-w-full" src="${this.i18n.pro_field_message[field].asset_url}" title="${field}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></iframe>`;
                        break;
                }

                var html = `<div class="wpuf-flex wpuf-text-left">
                                        <div class="wpuf-w-1/2">
                                            <img src="${wpuf_form_builder.lock_icon}" alt="">
                                            <h2 class="wpuf-text-black"><span class="wpuf-text-primary">${title} </span>${this.i18n.is_a_pro_feature}</h2>
                                            <p>${this.i18n.pro_feature_msg}</p>
                                        </div>
                                        <div class="wpuf-w-1/2">
                                            <div class="wpuf-icon-container wpuf-flex wpuf-justify-center wpuf-items-center">
                                                ${iconHtml}
                                                <div class="wpuf-shimmer"></div>
                                            </div>
                                        </div>
                                    </div>`;

                Swal.fire({
                    html: html,
                    showCloseButton: true,
                    customClass: {
                        confirmButton: "!wpuf-flex focus:!wpuf-shadow-none",
                        closeButton: "wpuf-absolute"
                    },
                    width: '50rem',
                    padding: '1.5rem',
                    confirmButtonColor: '#059669',
                    confirmButtonText: this.i18n.upgrade_to_pro
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.open(wpuf_form_builder.pro_link, '_blank');
                    }

                }, function() {});

            } else {
                Swal.fire({
                    html: this.i18n.pro_feature_msg,
                    showCloseButton: true,
                    customClass: {
                        confirmButton: "!wpuf-flex focus:!wpuf-shadow-none",
                        closeButton: "wpuf-absolute"
                    },
                    width: '40rem',
                    padding: '2rem 3rem',
                    title: '<span class="wpuf-text-primary">' + title + '</span> ' + this.i18n.is_a_pro_feature,
                    icon: '',
                    imageUrl: wpuf_form_builder.lock_icon,
                    confirmButtonColor: '#059669',
                    confirmButtonText: this.i18n.upgrade_to_pro
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.open(wpuf_form_builder.pro_link, '_blank');
                    }

                }, function() {});
            }
        },

        alert_invalidate_msg: function (field) {
            var validator = this.field_settings[field].validator;

            if (validator && validator.msg) {
                this.warn({
                    title: validator.msg_title || '',
                    color: validator.color || '#059669',
                    html: validator.msg,
                    showCancelButton: true,
                    imageUrl: validator.icon || '',
                    confirmButtonText: validator.cta || '',
                    cancelButtonText: this.i18n.ok,
                    showCloseButton: true,
                    width: '40rem',
                    padding: '2rem 3rem',
                    type: 'warning',
                    customClass: {
                        confirmButton: '!wpuf-bg-white !wpuf-text-gray-700 focus:!wpuf-shadow-none !wpuf-p-0 hover:!wpuf-bg-none',
                        closeButton: "wpuf-absolute wpuf-top-4 wpuf-right-4",
                        cancelButton: "!wpuf-bg-primary !wpuf-text-white"
                    },
                });
            }
        },

        get_invalidate_btn_class: function (field) {
            return this.field_settings[field].validator.button_class;
        },

        set_default_panel_sections: function () {
            this.$store.commit('set_default_panel_sections', this.panel_sections);
        },

        get_icon_url: function (field) {
            // return if icon is not set, undefined or empty
            if (typeof this.field_settings[field] === 'undefined' || typeof this.field_settings[field].icon === 'undefined' || this.field_settings[field].icon === '') {
                return '';
            }

            if (this.is_pro_active === '1' && this.field_settings[field].pro_feature) {
                return wpuf_form_builder.pro_asset_url + '/images/' + this.field_settings[field].icon + '.svg';
            } else {
                return wpuf_form_builder.asset_url + '/images/' + this.field_settings[field].icon + '.svg';
            }
        },
    },

    watch: {
        searched_fields: function ( searchValue ) {
            var self = this;

            this.set_default_panel_sections();

            // Bind jquery ui draggable. But first destroy any previous binding
            Vue.nextTick(function () {
                var buttons = $(self.$el).find('.panel-form-field-buttons .wpuf-field-button');

                buttons.each(function () {

                    if ($(this).draggable('instance')) {
                        $(this).draggable('destroy');
                    }
                });

                buttons.draggable({
                    connectToSortable: '#form-preview-stage, #form-preview-stage .wpuf-form, .wpuf-column-inner-fields .wpuf-column-fields-sortable-list',
                    helper: 'clone',
                    revert: 'invalid',
                    cancel: '.button-faded',
                }).disableSelection();
            });

            if (this.searched_fields === '') {
                return;
            }

            const matchedFields = Object.keys( self.field_settings ).filter( key =>
                self.field_settings[key].title.toLowerCase().includes( searchValue.toLowerCase() )
            );

            const updatedStructure = self.panel_sections.map(section => ({
                id: section.id,
                title: section.title,
                show: section.show,
                fields: section.fields.filter(field => matchedFields.includes(field))
            }));

            this.$store.commit('set_panel_sections', updatedStructure);
        }
    }
});

/**
 * Field template: Image Upload
 */
Vue.component('form-image_upload', {
    template: '#tmpl-wpuf-form-image_upload',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Multi-Select
 */
Vue.component('form-multiple_select', {
    template: '#tmpl-wpuf-form-multiple_select',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field Template: Post Content
 */
Vue.component('form-post_content', {
    template: '#tmpl-wpuf-form-post_content',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field Template: Post Excerpt
 */
Vue.component('form-post_excerpt', {
    template: '#tmpl-wpuf-form-post_excerpt',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: post_tags
 */
Vue.component('form-post_tags', {
    template: '#tmpl-wpuf-form-post_tags',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Post Title
 */
Vue.component('form-post_title', {
    template: '#tmpl-wpuf-form-post_title',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Radio
 */
Vue.component('form-radio_field', {
    template: '#tmpl-wpuf-form-radio_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Recaptcha
 */
Vue.component('form-recaptcha', {
    template: '#tmpl-wpuf-form-recaptcha',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        has_recaptcha_api_keys: function () {
            return (wpuf_form_builder.recaptcha_site && wpuf_form_builder.recaptcha_secret) ? true : false;
        },

        no_api_keys_msg: function () {
            return wpuf_form_builder.field_settings.recaptcha.validator.msg;
        }
    }
});

/**
 * Field template: Section Break
 */
Vue.component('form-section_break', {
    template: '#tmpl-wpuf-form-section_break',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: taxonomy
 */
Vue.component('form-taxonomy', {
    template: '#tmpl-wpuf-form-taxonomy',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        terms: function () {
            var i;

            for (i in wpuf_form_builder.wp_post_types) {
                var taxonomies = wpuf_form_builder.wp_post_types[i];

                if (taxonomies.hasOwnProperty(this.field.name)) {
                    var tax_field = taxonomies[this.field.name];

                    if (tax_field.terms) {
                        return tax_field.terms;
                    }
                }
            }

            return [];
        },

        should_show_text_input: function () {
            // Show text input for ajax type
            return this.field.type === 'ajax';
        },

        should_show_ajax_dropdown: function () {
            // Never show ajax dropdown - always use text input for ajax type
            return false;
        },

        sorted_terms: function () {
            var self  = this;
            var terms = $.extend(true, [], this.terms);

            // selection type and terms
            if (this.field.exclude_type && this.field.exclude) {
                var filter_ids = [];

                if ( this.field.exclude.length > 0 ) {
                    filter_ids = this.field.exclude.map(function (id) {
                        id = id.trim();
                        id = parseInt(id);
                        return id;
                    }).filter(function (id) {
                        return isFinite(id);
                    });
                }

                terms = terms.filter(function (term) {

                    switch(self.field.exclude_type) {
                        case 'exclude':
                            return _.indexOf(filter_ids, term.term_id) < 0;

                        case 'include':
                            return _.indexOf(filter_ids, term.term_id) >= 0;

                        case 'child_of':
                            return _.indexOf(filter_ids, parseInt(term.parent)) >= 0;
                    }
                });
            }

            // order
            terms = _.sortBy(terms, function (term) {
                return term[self.field.orderby];
            });

            if ('DESC' === this.field.order) {
                terms = terms.reverse();
            }

            var parent_terms = terms.filter(function (term) {
                return !term.parent;
            });

            parent_terms.map(function (parent) {
                parent.children = self.get_child_terms(parent.term_id, terms);
            });

            return parent_terms.length ? parent_terms : terms;
        }
    },

    methods: {
        get_child_terms: function (parent_id, terms) {
            var self = this;

            var child_terms = terms.filter(function (term) {
                return parseInt(term.parent) === parseInt(parent_id);
            });

            child_terms.map(function (child) {
                child.children = self.get_child_terms(child.term_id, terms);
            });

            return child_terms;
        },

        get_term_dropdown_options: function () {
            var self    = this,
                options = '';

            if ( this.field.type === 'select' ) {
                options = '<option value="">' + this.field.first + '</option>';
            }

            _.each(self.sorted_terms, function (term) {
                options += self.get_term_dropdown_options_children(term, 0);
            });

            return options;
        },

        get_term_dropdown_options_children: function (term, level) {
            var self   = this,
                option = '';

            var indent = '',
                i = 0;

            for (i = 0; i < level; i++) {
                indent += '&nbsp;&nbsp;';
            }

            option += '<option value="' + term.id + '">' + indent + term.name + '</option>';

            if (term.children.length) {
                _.each(term.children, function (child_term) {
                    option += self.get_term_dropdown_options_children(child_term, (level + 1));
                });
            }

            return option;
        },

        get_term_checklist: function () {
            var self      = this,
                checklist = '';

            checklist += '<ul class="wpuf-category-checklist">';

            _.each(this.sorted_terms, function (term) {
                checklist += self.get_term_checklist_li(term);
            });

            checklist += '</ul>';

            return checklist;
        },

        get_term_checklist_li: function (term) {
            var self = this,
                li   = '';

            li += '<li><label class="selectit"><input type="checkbox"> ' + term.name + '</label></li>';

            if (term.children.length) {
                li += '<ul class="children">';

                _.each(term.children, function (child_term) {
                    li += self.get_term_checklist_li(child_term);
                });

                li += '</ul>';
            }

            return li;
        },

        get_term_checklist_inline: function () {
            var self      = this,
                checklist = '';

            _.each(this.sorted_terms, function (term) {
                checklist += self.get_term_checklist_li_inline(term);
            });

            return checklist;
        },

        get_term_checklist_li_inline: function (term) {
            var self = this,
                li_inline   = '';

            li_inline += '<label class="wpuf-checkbox-inline"><input type="checkbox"> ' + term.name + '</label>';

            if (term.children.length) {
                _.each(term.children, function (child_term) {
                    li_inline += self.get_term_checklist_li_inline(child_term);
                });
            }

            return li_inline;
        }
    }
});

/**
 * Field template: Text
 */
Vue.component('form-text_field', {
    template: '#tmpl-wpuf-form-text_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

Vue.component('form-textarea_field', {
    template: '#tmpl-wpuf-form-textarea_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Website URL
 */
Vue.component('form-website_url', {
    template: '#tmpl-wpuf-form-website_url',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

Vue.component('help-text', {
    template: '#tmpl-wpuf-help-text',

    props: {
        text: {
            type: String,
            default: ''
        },

        placement: {
            type: String,
            default: 'top',
            validator: function (placement) {
                return ['top', 'right', 'bottom', 'left'].indexOf(placement) >= 0;
            }
        }
    },

    mounted: function () {
        $( this.$el ).tooltip({
            title: this.text,
            placement: 'auto top'
        });
    }
});

Vue.component('text-editor', {
    template: '#tmpl-wpuf-text-editor',

    props: ['rich', 'default_text'],

    computed: {
        site_url: function () {
            return wpuf_form_builder.site_url;
        },

        is_full: function () {
            return 'yes' === this.rich;
        }
    }
});

})(jQuery);
