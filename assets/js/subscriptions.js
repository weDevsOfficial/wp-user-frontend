import {createApp} from 'vue';
import {createPinia} from 'pinia';

import Subscriptions from './components/Subscriptions.vue';

import '../css/admin/subscriptions.css';

window.wpufSubscriptions = wpufSubscriptions;

const pinia = createPinia();
const app = createApp( Subscriptions );

app.use( pinia );

app.mount( '#wpuf-subscription-page' );
