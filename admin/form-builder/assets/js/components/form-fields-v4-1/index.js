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
