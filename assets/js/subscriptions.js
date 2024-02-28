import {createApp} from 'vue';
import Subscriptions from './components/Subscriptions.vue';

import '../css/admin/subscriptions.css';

window.wpufSubscriptions = wpufSubscriptions;

createApp( Subscriptions ).mount( '#subscription-page' );
