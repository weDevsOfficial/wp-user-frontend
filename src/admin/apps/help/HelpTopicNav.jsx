/**
 * The topics list beside the content card.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { FileText, House, LayoutDashboard, Lock, LockOpen, ShoppingCart, SquarePen, UserPlus } from 'lucide-react';

// The classic page's dashicons, as the app's icon pack (lucide, like FlyHR).
const ICONS = {
    'dashicons-admin-home': House,
    'dashicons-media-text': FileText,
    'dashicons-dashboard': LayoutDashboard,
    'dashicons-admin-users': UserPlus,
    'dashicons-lock': Lock,
    'dashicons-edit': SquarePen,
    'dashicons-cart': ShoppingCart,
    'dashicons-unlock': LockOpen,
};

/**
 * @param {Object}   props
 * @param {Object[]} props.topics  { id, label, icon }.
 * @param {string}   props.current Open topic id.
 * @param {Function} props.onOpen  ( id ) => void
 */
export default function HelpTopicNav( { topics, current, onOpen } ) {
    return (
        <nav aria-label={ __( 'Help topics', 'wp-user-frontend' ) } className="self-start rounded-[10px] border border-solid border-gray-200 bg-white p-2 shadow-sm">
            <ul className="m-0 list-none p-0">
                { topics.map( ( topic ) => {
                    const active = topic.id === current;
                    const Icon = ICONS[ topic.icon ] || FileText;

                    return (
                        <li key={ topic.id } className="m-0">
                            <button
                                type="button"
                                onClick={ () => onOpen( topic.id ) }
                                aria-current={ active ? 'page' : undefined }
                                data-topic={ topic.id }
                                className={ `wpuf-help-topic flex w-full cursor-pointer items-center gap-3 rounded-md border-0 px-3 py-2.5 text-start text-sm font-medium transition-colors ${ active ? 'bg-primary text-white' : 'bg-transparent text-gray-700 hover:bg-gray-100 hover:text-gray-900' }` }
                            >
                                <Icon size={ 18 } aria-hidden="true" className={ active ? 'text-white' : 'text-gray-400' } />
                                { topic.label }
                            </button>
                        </li>
                    );
                } ) }
            </ul>
        </nav>
    );
}
