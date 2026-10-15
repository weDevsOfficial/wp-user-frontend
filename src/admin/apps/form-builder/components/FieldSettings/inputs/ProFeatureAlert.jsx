import { __ } from '@wordpress/i18n';
import { builderData } from '../../../utils/boot';

/**
 * A Pro-only option shown as a teaser row with the Pro badge (e.g. Conditional
 * Logic while Pro is off). Replaces Vue field-option-pro-feature-alert.
 */
export default function ProFeatureAlert( { optionField } ) {
    const data = builderData();

    return (
        <div className="panel-field-opt panel-field-opt-pro-feature flex items-center text-sm text-gray-700 font-medium">
            <label>{ optionField.title } </label>
            <br />
            <label
                className="wpuf-pro-text-alert ml-2 wpuf-tooltip-top"
                data-tip={ __( 'Available in PRO version', 'wp-user-frontend' ) }
            >
                <a href={ data.pro_link || '' } target="_blank" rel="noopener noreferrer">
                    <img src={ `${ data.asset_url || '' }/images/pro-badge.svg` } alt={ __( 'pro icon', 'wp-user-frontend' ) } />
                </a>
            </label>
        </div>
    );
}
