;(function($) {
'use strict';

/**
 * Mixin for form fields like
 * field-text_field, field_textarea etc
 */
wpuf_mixins.form_field_mixin = {
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
    }
};

/**
 * Global mixin
 */
Vue.mixin({
    computed: {
        i18n: function () {
            return this.$store.state.i18n;
        }
    }
});

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
    }
};

Vue.component('builder-stage', {
    template: '#tmpl-wpuf-builder-stage',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.builder_stage),

    computed: {
        form_fields: function () {
            return this.$store.state.form_fields;
        }
    },

    methods: {
        open_field_settings: function(field_id) {
            this.$store.commit('open_field_settings', field_id);
        },

        clone_field: function(field_id) {
            console.log(field_id);
        },

        delete_field: function(field_id) {
            console.log(field_id);
        },
    }
});

/**
 * Sidebar field options panel
 */
Vue.component('field-options', {
    template: '#tmpl-wpuf-field-options',

    data: function() {
        return {
            show_basic_settings: true,
            show_advanced_settings: false
        };
    },

    computed: {
        editing_field_id: function () {
            return parseInt(this.$store.state.editing_field_id);
        },

        editing_form_field: function () {
            var self = this;
            return _.find(this.$store.state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(self.editing_field_id);
            });
        },

        settings: function() {
            var settings = this.$store.state.field_settings[this.editing_form_field.template].settings;

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

        form_field_type_title: function() {
            return this.$store.state.field_settings[this.editing_form_field.template].title;
        }
    }
});

Vue.component('field-radio', {
    template: '#tmpl-wpuf-field-radio',

    mixins: [
        wpuf_mixins.option_field_mixin
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
    },

    created: function () {
        if ('yes' === this.editing_form_field.is_meta) {
            wpuf_form_builder.event_hub.$on('field-text-focusout', this.met_key_autocomplete);
        }
    },

    methods: {
        on_focusout: function (e) {
            wpuf_form_builder.event_hub.$emit('field-text-focusout', e, this);
        },

        met_key_autocomplete: function (e, label_vm) {
            if ('label' === label_vm.option_field.name && !this.value.trim()) {
                this.value = label_vm.value.replace(/\W/g, '_').toLowerCase();
            }
        }
    }
});

Vue.component('field-text', {
    template: '#tmpl-wpuf-field-text',

    mixins: [
        wpuf_mixins.option_field_mixin
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
    },

    methods: {
        on_focusout: function (e) {
            wpuf_form_builder.event_hub.$emit('field-text-focusout', e, this);
        }
    }
});

Vue.component('field-text-meta', {
    template: '#tmpl-wpuf-field-text-meta',

    mixins: [
        wpuf_mixins.option_field_mixin
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
    },

    created: function () {
        if ('yes' === this.editing_form_field.is_meta) {
            wpuf_form_builder.event_hub.$on('field-text-focusout', this.met_key_autocomplete);
        }
    },

    methods: {
        on_focusout: function (e) {
            wpuf_form_builder.event_hub.$emit('field-text-focusout', e, this);
        },

        met_key_autocomplete: function (e, label_vm) {
            if ('label' === label_vm.option_field.name && !this.value.trim()) {
                this.value = label_vm.value.replace(/\W/g, '_').toLowerCase();
            }
        }
    }
});

/**
 * Sidebar form fields panel
 */
Vue.component('form-fields', {
    template: '#tmpl-wpuf-form-fields',

    computed: {
        panel_sections: function () {
            return this.$store.state.panel_sections;
        },

        field_settings: function () {
            return this.$store.state.field_settings;
        },
    },

    methods: {
        panel_toggle: function (index) {
            this.$store.commit('panel_toggle', index);
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
    ],

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
    },
});

Vue.component('form-textarea_field', {
    template: '#tmpl-wpuf-form-textarea_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    props: {
        field: {
            type: Object,
            default: {}
        }
    }
});

Vue.component('help-text', {
    template: '#tmpl-wpuf-help-text',

    props: {
        text: {
            type: String,
            default: ''
        }
    },

    mounted: function () {

    }
});

/**
 * Only proceed if current page is a form builder page
 */
if (!$('#wpuf-form-builder').length) {
    return;
}

/**
 * Vuex Store data
 */
var wpuf_form_builder_store = new Vuex.Store({
    state: {
        i18n: wpuf_form_builder.i18n,
        post: wpuf_form_builder.post,
        form_fields: wpuf_form_builder.form_fields,
        panel_sections: wpuf_form_builder.panel_sections,
        field_settings: wpuf_form_builder.field_settings,
        current_panel: 'form-fields',
        editing_field_id: 0, // editing form field id
    },

    mutations: {
        // set the current panel
        set_current_panel: function (state, panel) {
            if ('field-options' !== state.current_panel &&
                'field-options' === panel &&
                state.form_fields.length
            ) {
                state.editing_field_id = state.form_fields[0].id;
            }

            state.current_panel = panel;
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

            if (field.length) {
                state.editing_field_id = field[0].id;
                state.current_panel = 'field-options';
            }
        },

        // update editing field value
        update_editing_form_field: function (state, payload) {
            var editing_field = _.find(state.form_fields, function (item) {
                return parseInt(item.id) === parseInt(payload.editing_field_id);
            });

            editing_field[payload.field_name] = payload.value;
        }
    }
});

/**
 * The main form builder vue instance
 */
new Vue({
    el: '#wpuf-form-builder',

    mixins: wpuf_form_builder_mixins(wpuf_mixins.root_mixins),

    store: wpuf_form_builder_store,

    computed: {
        current_panel: function () {
            return this.$store.state.current_panel;
        },

        post: function () {
            return this.$store.state.post;
        },
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
        this.bind_tab_on_click($('#wpuf-form-builder > .nav-tab-wrapper > a'), '#wpuf-form-builder');

        // secondary settings tabs and their contents
        var settings_tabs = $('#wpuf-form-builder-settings .nav-tab'),
            settings_tab_contents = $('#wpuf-form-builder-settings .tab-contents .group');

        settings_tabs.first().addClass('nav-tab-active');
        settings_tab_contents.first().addClass('active');

        this.bind_tab_on_click(settings_tabs, '#wpuf-form-builder-settings');
    },

    methods: {
        // tabs and their contents
        bind_tab_on_click: function (tabs, scope) {
            tabs.on('click', function (e) {
                e.preventDefault();

                var button = $(this),
                    tab_contents = $(scope + ' > .tab-contents'),
                    group_id = button.attr('href');

                button.addClass('nav-tab-active').siblings('.nav-tab-active').removeClass('nav-tab-active');

                tab_contents.children().removeClass('active');
                $(group_id).addClass('active');
            });
        },

        // set current sidebar panel
        set_current_panel: function (panel) {
            this.$store.commit('set_current_panel', panel);
        },

        // save form builder data
        save_form_builder: function () {
            console.log('form submitted!!!');
        }
    }
});

})(jQuery);
