/**
 * The frontend runtime bundle: the kit (with its icons) on window.wpuf.frontend,
 * plus the base sheet. Enqueued as `wpuf-frontend-runtime` by the apps.
 *
 * @since WPUF_SINCE
 */
import * as kit from './index';
import './kit.css';

window.wpuf = window.wpuf || {};
window.wpuf.frontend = { ...( window.wpuf.frontend || {} ), ...kit };
