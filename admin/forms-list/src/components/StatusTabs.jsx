/**
 * StatusTabs component — renders tab navigation for form statuses.
 *
 * @since WPUF_SINCE
 */
const StatusTabs = ( { postCounts, currentTab, onTabChange } ) => {
    return (
        <div className="wpuf-flex wpuf-mt-9">
            { Object.entries( postCounts ).map( ( [ key, value ] ) => {
                const tabKey = key === 'all' ? 'any' : key;
                const isActive = currentTab === tabKey || ( key === 'all' && currentTab === 'any' );

                return (
                    <span
                        key={ key }
                        onClick={ () => onTabChange( tabKey ) }
                        className={
                            'wpuf-flex hover:wpuf-border-primary hover:wpuf-text-primary wpuf-whitespace-nowrap wpuf-py-4 wpuf-px-1 wpuf-border-b-2 wpuf-font-medium wpuf-text-sm wpuf-mr-8 focus:wpuf-outline-none focus:wpuf-shadow-none wpuf-transition-all hover:wpuf-cursor-pointer ' +
                            ( isActive
                                ? 'wpuf-border-primary wpuf-text-primary'
                                : 'wpuf-border-transparent wpuf-text-gray-500' )
                        }
                    >
                        { value.label }
                        <span className="wpuf-bg-gray-100 wpuf-text-gray-900 wpuf-ml-3 wpuf-rounded-full wpuf-py-0.5 wpuf-px-2.5 wpuf-text-xs wpuf-font-medium md:wpuf-inline-block">
                            { value.count }
                        </span>
                    </span>
                );
            } ) }
        </div>
    );
};

export default StatusTabs;
