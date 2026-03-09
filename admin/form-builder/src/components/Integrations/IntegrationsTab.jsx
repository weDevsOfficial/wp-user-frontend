import { useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../store';

/**
 * Integrations tab component.
 *
 * Renders the list of integrations from store.
 * Pro extensions add integration types via `applyFilters('wpuf.formBuilder.integrations')`.
 * Free version shows a placeholder indicating Pro is needed.
 */
export default function IntegrationsTab() {
    const isProActive = useSelect( ( select ) => select( STORE_NAME ).getIsProActive(), [] );
    const integrations = useSelect( ( select ) => select( STORE_NAME ).getIntegrations(), [] );
    const { updateIntegration } = useDispatch( STORE_NAME );

    const integrationsList = useMemo( () => {
        return applyFilters( 'wpuf.formBuilder.integrations', integrations );
    }, [ integrations ] );

    if ( ! isProActive ) {
        return (
            <div className="wpuf-text-center wpuf-py-16 wpuf-px-8">
                <svg className="wpuf-w-16 wpuf-h-16 wpuf-text-gray-300 wpuf-mx-auto wpuf-mb-4" fill="none" viewBox="0 0 24 24" strokeWidth="1" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <h3 className="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-700 wpuf-mb-2">
                    { __( 'Integrations', 'wp-user-frontend' ) }
                </h3>
                <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-max-w-md wpuf-mx-auto">
                    { __( 'Integrations are available in WP User Frontend Pro. Upgrade to connect your forms with third-party services.', 'wp-user-frontend' ) }
                </p>
            </div>
        );
    }

    // Pro integrations are rendered via wp.hooks — Pro registers components
    // that handle their own UI through the filtered integrationsList
    const hasIntegrations = integrationsList && Object.keys( integrationsList ).length > 0;

    if ( ! hasIntegrations ) {
        return (
            <div className="wpuf-text-center wpuf-py-12 wpuf-text-gray-500">
                <p>{ __( 'No integrations configured for this form.', 'wp-user-frontend' ) }</p>
            </div>
        );
    }

    return (
        <div className="wpuf-integrations-tab wpuf-p-6">
            { Object.entries( integrationsList ).map( ( [ key, integration ] ) => (
                <div key={ key } className="wpuf-integration-item wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-4 wpuf-mb-4">
                    <div className="wpuf-flex wpuf-items-center wpuf-justify-between">
                        <div>
                            <h4 className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-900 wpuf-m-0">
                                { integration.title || key }
                            </h4>
                            { integration.description && (
                                <p className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-1 wpuf-mb-0">
                                    { integration.description }
                                </p>
                            ) }
                        </div>
                        <span className={ `wpuf-text-xs wpuf-px-2 wpuf-py-1 wpuf-rounded-full ${ integration.enabled ? 'wpuf-bg-green-100 wpuf-text-green-700' : 'wpuf-bg-gray-100 wpuf-text-gray-600' }` }>
                            { integration.enabled
                                ? __( 'Active', 'wp-user-frontend' )
                                : __( 'Inactive', 'wp-user-frontend' )
                            }
                        </span>
                    </div>
                </div>
            ) ) }
        </div>
    );
}
