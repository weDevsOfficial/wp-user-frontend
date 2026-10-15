/**
 * SettingsNav — left vertical tab navigation + search, driven by the IA map.
 */
import { __ } from '@wordpress/i18n';
import { SearchInput } from '@wpuf/components';
import NavIcon from './nav-icons';

export default function SettingsNav( { ia, activeTab, onSelect, search, onSearch } ) {
    return (
        <div className="sticky top-10 w-60 shrink-0 rounded-[10px] border border-gray-200 bg-white p-2 shadow-sm">
            <SearchInput
                value={ search }
                onChange={ onSearch }
                placeholder={ __( 'Search settings…', 'wp-user-frontend' ) }
                aria-label={ __( 'Search settings', 'wp-user-frontend' ) }
                className="mb-2 w-full"
            />
            <nav className="[&>:not([hidden])~:not([hidden])]:mt-0.5 [&>:not([hidden])~:not([hidden])]:mb-0">
                { ia.map( ( tab ) => (
                    <button
                        type="button"
                        key={ tab.id }
                        aria-current={ activeTab === tab.id ? 'page' : undefined }
                        onClick={ () => onSelect( tab.id ) }
                        className={ `focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary/40 flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium ${
                            activeTab === tab.id
                                ? 'bg-primary! text-white!'
                                : 'text-gray-700 hover:bg-gray-100! hover:text-gray-900!'
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
