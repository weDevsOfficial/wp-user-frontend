import { __ } from '@wordpress/i18n';

/**
 * Show a Pro feature upgrade modal using SweetAlert2.
 * Replaces the Vue form-field-option-pro-feature-alert component.
 *
 * @param {string} featureName  Name of the Pro feature
 */
export function showProFeatureAlert( featureName ) {
    const data = window.wpuf_form_builder || {};

    if ( typeof window.Swal === 'undefined' ) {
        return;
    }

    window.Swal.fire( {
        title: __( 'Premium Feature', 'wp-user-frontend' ),
        html: `<p>${ featureName } ${ __( 'is a premium feature. Please upgrade to Pro to use this feature.', 'wp-user-frontend' ) }</p>`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: __( 'Upgrade to Pro', 'wp-user-frontend' ),
        cancelButtonText: __( 'Cancel', 'wp-user-frontend' ),
        confirmButtonColor: '#059669',
        customClass: {
            confirmButton: '!wpuf-text-white',
        },
    } ).then( ( result ) => {
        if ( result.isConfirmed && data.pro_link ) {
            window.open( data.pro_link, '_blank' );
        }
    } );
}

/**
 * ProFeatureAlert component that renders an inline upgrade badge.
 *
 * @param {Object} props
 * @param {string} props.featureName
 */
export default function ProFeatureAlert( { featureName } ) {
    return (
        <span
            className="wpuf-pro-feature-badge"
            onClick={ () => showProFeatureAlert( featureName ) }
            role="button"
            tabIndex={ 0 }
            onKeyDown={ ( e ) => {
                if ( e.key === 'Enter' || e.key === ' ' ) {
                    showProFeatureAlert( featureName );
                }
            } }
        >
            <img
                src={
                    ( window.wpuf_form_builder?.lock_icon ) ||
                    ''
                }
                alt={ __( 'Pro feature', 'wp-user-frontend' ) }
                className="wpuf-pro-lock-icon"
            />
        </span>
    );
}
