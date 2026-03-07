import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import { STORE_NAME } from '../store';

/**
 * Hook for managing integrations.
 * Replaces the Vue integration.js mixin.
 *
 * @param {string} integrationId The integration identifier
 * @return {Object}
 */
export default function useIntegrations( integrationId ) {
    const { integrations, storeIntegrations } = useSelect(
        ( select ) => ( {
            integrations: window.wpuf_form_builder?.integrations || {},
            storeIntegrations: select( STORE_NAME ).getIntegrations(),
        } ),
        []
    );

    const { updateIntegration } = useDispatch( STORE_NAME );

    const getIntegration = useCallback(
        ( id ) => integrations[ id ] || null,
        [ integrations ]
    );

    const settings = storeIntegrations[ integrationId ] ||
        ( integrations[ integrationId ] && integrations[ integrationId ].settings ) ||
        {};

    const updateSettings = useCallback(
        ( value ) => {
            updateIntegration( integrationId, value );
        },
        [ integrationId, updateIntegration ]
    );

    const insertValue = useCallback(
        ( type, field, prop ) => {
            const value = field !== undefined
                ? `{${ type }:${ field }}`
                : `{${ type }}`;

            updateIntegration( integrationId, {
                ...( storeIntegrations[ integrationId ] || {} ),
                [ prop ]: ( storeIntegrations[ integrationId ]?.[ prop ] || '' ) + value,
            } );
        },
        [ integrationId, storeIntegrations, updateIntegration ]
    );

    return {
        integrations,
        settings,
        getIntegration,
        updateSettings,
        insertValue,
    };
}
