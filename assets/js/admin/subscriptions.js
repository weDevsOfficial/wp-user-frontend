import { createApp, ref } from '../../vendor/vue-3/vue.esm-browser.js'

createApp({
    setup() {
        const message = ref('Hello vue!')
        return {
            message
        }
    }
}).mount('#subscription-page')
