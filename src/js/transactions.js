import {createApp} from 'vue';
import {createPinia} from 'pinia';

import Transactions from './components/Transactions.vue';
import '../css/admin/transactions.css';

if (process.env.NODE_ENV === 'development') {
    app.config.devtools = true;
}

window.wpufTransactions = wpufTransactions;

const pinia = createPinia();
const app = createApp( Transactions );

app.use( pinia );

app.mount( '#wpuf-transactions-page' );
