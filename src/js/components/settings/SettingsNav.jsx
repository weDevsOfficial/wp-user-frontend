/**
 * SettingsNav — left vertical tab navigation + search, driven by the IA map.
 */
import { __ } from '@wordpress/i18n';
import NavIcon from './nav-icons';

export default function SettingsNav( { ia, activeTab, onSelect, search, onSearch } ) {
    return (
        <div className="wpuf-w-[280px] wpuf-shrink-0">
            <div className="wpuf-relative wpuf-mb-4">
                <input
                    type="text"
                    value={ search }
                    placeholder={ __( 'Search settings…', 'wp-user-frontend' ) }
                    onChange={ ( e ) => onSearch( e.target.value ) }
                    className="wpuf-block wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 placeholder:wpuf-text-gray-400 wpuf-max-w-full focus:wpuf-ring-transparent"
                    style={ {
                        width: '100%',
                        height: '42px',
                        borderRadius: '6px',
                        borderWidth: '1px',
                        paddingTop: '9px',
                        paddingRight: '38px',
                        paddingBottom: '9px',
                        paddingLeft: '13px',
                        backgroundColor: '#FFFFFF',
                        borderColor: '#CBD5E1',
                        borderStyle: 'solid',
                        opacity: 1,
                        boxSizing: 'border-box',
                        fontSize: '16px',
                    } }
                />
                { search ? (
                    <button
                        type="button"
                        onClick={ () => onSearch( '' ) }
                        aria-label={ __( 'Clear search', 'wp-user-frontend' ) }
                        className="wpuf-absolute wpuf-right-2 wpuf-top-1/2 wpuf--translate-y-1/2 wpuf-flex wpuf-h-6 wpuf-w-6 wpuf-items-center wpuf-justify-center wpuf-rounded-full wpuf-text-gray-400 hover:wpuf-bg-gray-100 hover:wpuf-text-gray-600"
                    >
                        <svg className="wpuf-h-4 wpuf-w-4" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                ) : (
                    <span className="wpuf-pointer-events-none wpuf-absolute wpuf-right-3 wpuf-top-1/2 wpuf--translate-y-1/2 wpuf-text-gray-400">
                        <svg className="wpuf-h-5 wpuf-w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.8" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                ) }
            </div>
            <nav className="wpuf-space-y-1">
                { ia.map( ( tab ) => (
                    <button
                        type="button"
                        key={ tab.id }
                        onClick={ () => onSelect( tab.id ) }
                        className={ `wpuf-flex wpuf-w-full wpuf-items-center wpuf-rounded-md wpuf-px-3 wpuf-py-2.5 wpuf-text-left wpuf-text-sm wpuf-font-medium ${
                            activeTab === tab.id
                                ? 'wpuf-bg-primary wpuf-text-white'
                                : 'wpuf-text-gray-700 hover:wpuf-bg-gray-100'
                        }` }
                    >
                        <NavIcon tabId={ tab.id } />
                        { tab.title }
                    </button>
                ) ) }
            </nav>
        </div>
    );
}
