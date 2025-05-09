import {createApp} from 'vue';

import '../css/forms-list.css';
import FormsList from './components/FormsList.vue';

const app = createApp( FormsList );

app.mount( '#wpuf-post-forms-list-table-view' );
