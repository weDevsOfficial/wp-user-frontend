/**
 * Under a setting that only works when another setting (often in another
 * section) is set: what is missing and a button to that section. Display
 * only; schema key `requires` = { section, fields, check: on | filled,
 * message, action }.
 *
 * @since WPUF_SINCE
 */
import { useSelect } from '@wordpress/data';
import { Button } from '@wpuf/components';
import { TriangleAlert } from 'lucide-react';
import { STORE_NAME } from '../../../../stores/settings/constants';
import { openSettingsSection } from '../../navigate';

const isOn = ( value ) => [ 'on', 'yes', '1', 1, true ].includes( value );
const isFilled = ( value ) => '' !== String( value ?? '' ).trim();

/**
 * @param {Object} props
 * @param {Object} props.requires The field's `requires`.
 */
export default function SettingRequirement( { requires } ) {
    const values = useSelect( ( select ) => select( STORE_NAME ).getSectionValues( requires.section ), [ requires.section ] );
    const fields = Array.isArray( requires.fields ) ? requires.fields : [];
    const test = 'filled' === requires.check ? isFilled : isOn;

    if ( ! fields.length || fields.every( ( name ) => test( values[ name ] ) ) ) {
        return null;
    }

    return (
        <div data-wpuf-ui="" role="status" className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-md border border-solid border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
            <TriangleAlert size={ 14 } className="shrink-0" aria-hidden="true" />
            <span className="min-w-0 flex-1">{ requires.message }</span>
            { requires.action && (
                <Button size="xs" variant="secondary" onClick={ () => openSettingsSection( requires.section ) }>
                    { requires.action }
                </Button>
            ) }
        </div>
    );
}
