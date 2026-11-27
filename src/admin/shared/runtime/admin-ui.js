/**
 * `admin-ui` entry (handle wpuf-admin-ui, depends on wpuf-admin-runtime):
 * @wedevs/plugin-ui loaded once for free and Pro (`import ... from
 * '@wedevs/plugin-ui'` resolves to window.wpuf.ui), the providers and, from
 * task 3.3, the shared/ui wrappers (window.wpuf.components). Toasts:
 * `import { toast } from '@wedevs/plugin-ui'` (one sonner instance).
 */
import * as ui from '@wedevs/plugin-ui';

import * as wrappers from '../ui';
import ErrorBoundary from '../providers/ErrorBoundary';
import ScreenSlots from '../providers/ScreenSlots';
import WpufProviders from '../providers/WpufProviders';
import publish from './publish';

publish( {
    ui,
    components: { ...wrappers, WpufProviders, ErrorBoundary, ScreenSlots },
} );
