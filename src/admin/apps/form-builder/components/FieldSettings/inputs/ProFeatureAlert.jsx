import { __ } from '@wordpress/i18n';
import { ProBadge, Tooltip } from '@wpuf/components';
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
            <span className="wpuf-pro-text-alert ml-2">
                <Tooltip content={ __( 'Available in PRO version', 'wp-user-frontend' ) }>
                    <span className="inline-flex"><ProBadge upgradeUrl={ data.pro_link || undefined } utm="wpuf-form-builder" /></span>
                </Tooltip>
            </span>
        </div>
    );
}
