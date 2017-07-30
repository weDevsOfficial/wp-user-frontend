Vue.component('wpuf-integration', {
    template: '#tmpl-wpuf-integration',

    computed: {

        integrations: function() {
            return wpuf_form_builder.integrations;
        },

        hasIntegrations: function() {
            return Object.keys(this.integrations).length;
        },

        store: function() {
            return this.$store.state.integrations;
        },

        pro_link: function() {
            return wpuf_form_builder.pro_link;
        }
    },

    methods: {

        getIntegration: function(id) {
            return this.integrations[id];
        },

        getIntegrationSettings: function(id) {
            // find settings in store, otherwise take from default integration settings
            return this.store[id] || this.getIntegration(id).settings;
        },

        isActive: function(id) {
            if ( !this.isAvailable(id) ) {
                return false;
            }

            return this.getIntegrationSettings(id).enabled === true;
        },

        isAvailable: function(id) {
            return ( this.integrations[id] && this.integrations[id].pro ) ? false : true;
        },

        toggleState: function(id, target) {
            if ( ! this.isAvailable(id) ) {
                this.alert_pro_feature( id );
                return;
            }

            // toggle the enabled state
            var settings = this.getIntegrationSettings(id);

            settings.enabled = !this.isActive(id);

            this.$store.commit('updateIntegration', {
                index: id,
                value: settings
            });

            $(target).toggleClass('checked');
        },

        alert_pro_feature: function (id) {
            var title = this.getIntegration(id).title;

            swal({
                title: '<i class="fa fa-lock"></i> ' + title + ' <br>' + this.i18n.is_a_pro_feature,
                text: this.i18n.pro_feature_msg,
                type: '',
                showCancelButton: true,
                cancelButtonText: this.i18n.close,
                confirmButtonColor: '#46b450',
                confirmButtonText: this.i18n.upgrade_to_pro
            }).then(function (is_confirm) {
                if (is_confirm) {
                    window.open(wpuf_form_builder.pro_link, '_blank');
                }

            }, function() {});
        },

        showHide: function(target) {
            $(target).closest('.wpuf-integration').toggleClass('collapsed');
        },
    }
});
