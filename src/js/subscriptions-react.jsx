import { createRoot } from '@wordpress/element';
import Subscriptions from './components-react/Subscriptions';
import './stores-react/subscription';
import './stores-react/component';
import './stores-react/notice';
import './stores-react/quickEdit';
import './stores-react/fieldDependency';

// Import styles
import '../../assets/css/admin/subscriptions.css';

const container = document.getElementById('wpuf-subscription-page');

if (container) {
    const root = createRoot(container);
    root.render(<Subscriptions />);
}
