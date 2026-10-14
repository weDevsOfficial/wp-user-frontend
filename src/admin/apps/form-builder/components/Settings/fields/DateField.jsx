import { DateTime } from '@wpuf/components';
import { __ } from '@wordpress/i18n';
import { X } from 'lucide-react';
import SettingLabel from './SettingLabel';
import { fromPicker, toPicker } from '../../../utils/dateSetting';

/**
 * Date setting (develop type="date", e.g. form scheduling From / To): the
 * shared React date picker in place of the jQuery UI datepicker, with an
 * optional time (the schedule opens / closes at that time; without one it is
 * midnight, as before). A stored value the picker cannot read is shown as it
 * is and only replaced when a new date is picked; the clear button empties
 * it, as deleting the text did.
 */
export function DateInput( { id, value, onChange, className } ) {
    const stored = String( value ?? '' );
    const picker = toPicker( stored );

    return (
        <div className={ `flex items-center gap-2 ${ className || '' }` } data-wpuf-date-setting="">
            <DateTime
                id={ id }
                withTime
                value={ picker }
                placeholder={ stored && ! picker ? stored : __( 'Select date and time', 'wp-user-frontend' ) }
                onChange={ ( next ) => onChange( fromPicker( next ) ) }
                className="flex-1"
            />
            { stored && (
                <button
                    type="button"
                    className="inline-flex size-9 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-500 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                    aria-label={ __( 'Clear date', 'wp-user-frontend' ) }
                    title={ __( 'Clear date', 'wp-user-frontend' ) }
                    onClick={ () => onChange( '' ) }
                >
                    <X className="size-4" aria-hidden="true" />
                </button>
            ) }
        </div>
    );
}

export default function DateField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <DateInput id={ name } value={ value } onChange={ ( next ) => onChange( name, next ) } />
        </>
    );
}
