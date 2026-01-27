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
            activeTab: 'icon',
            icons: wpuf_form_builder.icons || []
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

        isImageValue: function() {
            if (!this.value) return false;
            return this.value.indexOf('http') === 0 || this.value.indexOf('//') === 0 || this.value.indexOf('/') === 0;
        },

        selectedIconDisplay: function() {
            if (this.value) {
                if (this.isImageValue) {
                    return 'Custom image';
                }
                var icon = this.icons.find(function(item) {
                    return item.class === this.value;
                }.bind(this));
                return icon ? icon.name : this.value;
            }
            return 'Select an icon or upload image';
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

    watch: {
        'editing_form_field.show_icon': function(newVal, oldVal) {
            // When show_icon changes from 'no' to 'yes' and field_icon is empty or 'fas fa-0'
            if (newVal === 'yes' && oldVal === 'no') {
                if (!this.editing_form_field.field_icon || this.editing_form_field.field_icon === 'fas fa-0') {
                    // Set a proper default icon based on field type
                    var defaultIcons = wpuf_form_builder.defaultIcons || {};

                    // Get the field type/template
                    var fieldType = this.editing_form_field.template || this.editing_form_field.input_type || 'text';

                    // Set the default icon based on field type
                    var defaultIcon = defaultIcons[fieldType] || 'fa-solid fa-circle';

                    this.$store.commit('update_editing_form_field', {
                        editing_field_id: this.editing_form_field.id,
                        field_name: 'field_icon',
                        value: defaultIcon
                    });
                }
            }
        },

        value: function(newVal) {
            // Auto-switch tab based on value type
            if (newVal) {
                this.activeTab = (newVal.indexOf('http') === 0 || newVal.indexOf('//') === 0 || newVal.indexOf('/') === 0) ? 'image' : 'icon';
            }
        }
    },

    methods: {

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

        switchTab: function(tab) {
            this.activeTab = tab;
        },

        openMediaUploader: function(e) {
            if (e) e.stopPropagation();

            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            var self = this;
            var frame = wp.media({
                title: 'Select Icon Image',
                button: { text: 'Use as Icon' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = (attachment.sizes && attachment.sizes.thumbnail)
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;
                self.value = url;
                self.showIconPicker = false;
            });

            frame.open();
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
