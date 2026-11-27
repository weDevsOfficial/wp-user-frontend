import { TextInput } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { Notice } from '@wpuf/components';

/**
 * Text setting (develop type="text") on the shared TextInput (4.4e). Shows the
 * value SettingsSection resolved (stored, else the field's default).
 */
export default function TextField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <TextInput
                id={ name }
                className="w-full"
                value={ value ?? '' }
                onChange={ ( next ) => onChange( name, next ) }
                placeholder={ field.placeholder || '' }
            />
            { field.notice && (
                <Notice tone="warning" className="mt-3">{ field.notice.text }</Notice>
            ) }
        </>
    );
}
