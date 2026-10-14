import { Textarea } from '@wpuf/components';
import SettingLabel, { LongHelp } from './SettingLabel';

/**
 * Textarea setting on the shared Textarea (4.6a). Four 20px lines by default:
 * the height of the former six tight (line-height 1) rows.
 */
export default function TextareaField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <Textarea
                id={ name }
                className="w-full"
                value={ value !== undefined && value !== null ? value : ( field.default || field.value || '' ) }
                onChange={ ( next ) => onChange( name, next ) }
                rows={ field.rows || 4 }
                placeholder={ field.placeholder || '' }
            />
            <LongHelp html={ field.long_help } />
        </>
    );
}
