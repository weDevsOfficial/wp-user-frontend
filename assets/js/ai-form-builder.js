import {createApp} from 'vue';
import AIFormBuilder from './components/AIFormBuilder.vue';
import '../css/ai-form-builder.css';

// Make the global variable available
window.wpufAIFormBuilder = window.wpufAIFormBuilder || {};

// Check if the container exists before mounting
const container = document.getElementById('wpuf-ai-form-builder');
if (container) {
    const app = createApp( AIFormBuilder );
    app.mount( '#wpuf-ai-form-builder' );
}