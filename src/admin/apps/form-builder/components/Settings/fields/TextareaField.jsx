import { Textarea } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Textarea setting (develop type="textarea") on the shared Textarea (4.4e).
 */
export default function TextareaField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <Textarea
                id={ name }
                className="w-full"
                value={ value ?? '' }
                onChange={ ( next ) => onChange( name, next ) }
                rows={ field.rows || 6 }
                placeholder={ field.placeholder || '' }
            />
        </>
    );
}
