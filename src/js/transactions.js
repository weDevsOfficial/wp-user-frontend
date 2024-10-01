import {createApp} from '../../assets/vendor/vue-3/vue.esm-browser';
import {createPinia} from 'pinia';

import Transactions from './components/Transactions.vue';

import '../css/admin/transactions.css';

window.wpufTransactions = wpufTransactions;

const pinia = createPinia();
const app = createApp( Transactions );

app.use( pinia );

app.mount( '#wpuf-transactions-page' );
