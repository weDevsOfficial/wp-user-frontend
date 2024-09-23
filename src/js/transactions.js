import {createApp} from '../../assets/vendor/vue-3/vue.esm-browser';
import {createPinia} from 'pinia';

import Transactions from './components/Transactions.vue';

import '../../assets/css/admin/transactions.css';

// window.wpufSubscriptions = wpufSubscriptions;

const pinia = createPinia();
const app = createApp( Transactions );

app.use( pinia );

app.mount( '#wpuf-transactions-page' );
