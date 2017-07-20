Vue.component('wpuf-modal', {
    template: '#tmpl-wpuf-modal',
    props: {
        show: Boolean,
        onClose: Function
    },

    mounted: function () {
        var self = this;

        $('body').on( 'keydown', function(e) {
            if (self.show && e.keyCode === 27) {
                self.closeModal();
            }
        });
    },

    methods: {
        closeModal: function() {
            if ( typeof this.onClose !== 'undefined' ) {
                this.onClose();
            } else {
                this.$emit('hideModal');
            }
        }
    }
});