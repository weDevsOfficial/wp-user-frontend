import {createApp} from 'vue';
import AIFormBuilder from './components/AIFormBuilder.vue';
import '../css/ai-form-builder.css';

// Make the global variable available
window.wpufAIFormBuilder = window.wpufAIFormBuilder || {};

const app = createApp( AIFormBuilder );
app.mount( '#wpuf-ai-form-builder' );