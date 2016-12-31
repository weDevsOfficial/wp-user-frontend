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
