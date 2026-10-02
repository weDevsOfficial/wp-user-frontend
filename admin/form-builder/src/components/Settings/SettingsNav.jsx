import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../store';

/**
 * Settings sidebar navigation — mirrors the Vue template in post-form-settings.php.
 *
 * Structure: top-level groups (headers) with always-visible sub-items.
 * No collapse/expand — all sub-items rendered flat, exactly like Vue.
 */
const BADGE_MENUS = [ 'post_expiration' ];

export default function SettingsNav( { activeTab, onTabChange } ) {
    const data = window.wpuf_form_builder || {};
    const isProActive = useSelect( ( select ) => select( STORE_NAME ).getIsProActive(), [] );

    const settingsTitles = useMemo( () => {
        const titles = data.settings_titles || {};
        return applyFilters( 'wpuf.formBuilder.settingsTabs', titles );
    }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <div className="wpuf-w-1/4 wpuf-min-h-screen wpuf-border-r wpuf-p-8">
            { Object.entries( settingsTitles ).map( ( [ topKey, topItem ] ) => {
                const subItems = topItem.sub_items || {};
                const hasSubItems = Object.keys( subItems ).length > 0;

                // Modules header is clickable when it has no sub-items (Vue behavior)
                const isClickableHeader = ! hasSubItems;
                const isHeaderActive = isClickableHeader && activeTab === topKey;

                return (
                    <div key={ topKey }>
                        { /* Top-level section header */ }
                        <div className="wpuf-mb-4 wpuf-flex wpuf-justify-between wpuf-items-center">
                            <h2
                                id={ isClickableHeader ? `${ topKey }-menu` : undefined }
                                onClick={ isClickableHeader ? () => onTabChange( topKey ) : undefined }
                                className={ `wpuf-group/sidebar-item wpuf-text-base wpuf-m-0 wpuf-flex wpuf-items-center wpuf-w-full wpuf-py-2 wpuf-px-3 wpuf--ml-3 wpuf-rounded-lg wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out ${ isClickableHeader ? 'hover:wpuf-bg-primary hover:wpuf-cursor-pointer hover:wpuf-text-white' : '' } ${ isHeaderActive ? 'wpuf-bg-primary active_settings_tab wpuf-text-white' : 'wpuf-text-gray-600' }` }
                            >
                                { topItem.icon && (
                                    <span
                                        className={ `${ isHeaderActive ? '[&_.custom-stroke]:wpuf-stroke-white' : '[&_.custom-stroke]:wpuf-stroke-gray-500' } ${ isClickableHeader ? 'group-hover/sidebar-item:[&_.custom-stroke]:wpuf-stroke-white' : '' }` }
                                        dangerouslySetInnerHTML={ { __html: topItem.icon } }
                                    />
                                ) }
                                <span className="wpuf-ml-2">{ topItem.label }</span>
                            </h2>
                        </div>

                        { /* Sub-items list */ }
                        { hasSubItems && (
                            <div className="wpuf-mb-4">
                                <ul className="wpuf-sidebar-menu wpuf-list-none wpuf-space-y-2">
                                    { Object.entries( subItems ).map( ( [ subKey, subItem ] ) => {
                                        const isActive = activeTab === subKey;

                                        return (
                                            <li
                                                key={ subKey }
                                                onClick={ () => onTabChange( subKey ) }
                                                className={ `wpuf-group/sidebar-item wpuf-mx-2 wpuf-py-2 wpuf-px-3 hover:wpuf-bg-primary hover:wpuf-cursor-pointer wpuf-rounded-lg wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-items-center wpuf-flex wpuf-justify-between ${ isActive ? 'wpuf-bg-primary active_settings_tab' : '' }` }
                                                data-settings={ subKey }
                                            >
                                                <a
                                                    className={ `wpuf-ml-2 wpuf-text-sm group-hover/sidebar-item:wpuf-text-white wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out focus:wpuf-shadow-none focus:wpuf-outline-none wpuf-flex wpuf-items-center ${ isActive ? 'wpuf-text-white' : 'wpuf-text-gray-600' }` }
                                                >
                                                    { subItem.icon && (
                                                        <span
                                                            className={ `[&>svg]:wpuf-w-5 [&>svg]:wpuf-h-5 ${ isActive ? '[&_.custom-stroke]:wpuf-stroke-white' : '[&_.custom-stroke]:wpuf-stroke-gray-500 group-hover/sidebar-item:[&_.custom-stroke]:wpuf-stroke-white' }` }
                                                            dangerouslySetInnerHTML={ { __html: subItem.icon } }
                                                        />
                                                    ) }
                                                    <span className="wpuf-ml-2">{ subItem.label }</span>
                                                </a>
                                                { ! isProActive && BADGE_MENUS.includes( subKey ) && (
                                                    <span>
                                                        <img
                                                            src={ `${ data.asset_url || '' }/images/pro-badge.svg` }
                                                            alt="pro icon"
                                                        />
                                                    </span>
                                                ) }
                                            </li>
                                        );
                                    } ) }
                                </ul>
                            </div>
                        ) }
                    </div>
                );
            } ) }
        </div>
    );
}
