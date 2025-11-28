/**
 * User Directory Free - Main Entry Point
 *
 * React-based admin for User Directory
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import { createRoot } from '@wordpress/element';
import App from './App';
import './styles/main.css';

// Wait for DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('wpuf-ud-free-app');
    
    if (container) {
        const root = createRoot(container);
        root.render(<App />);
    }
});
