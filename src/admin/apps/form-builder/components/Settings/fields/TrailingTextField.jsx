import { NumberInput, TextInput } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Input with a trailing unit (develop type="trailing-text", e.g. "hours") on
 * the shared TextInput / NumberInput (4.4e).
 */
export default function TrailingTextField( { field, name, value, onChange } ) {
    const Input = 'number' === field.trailing_type ? NumberInput : TextInput;

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <div className="relative">
                <Input
                    id={ name }
                    className="w-full pe-24"
                    value={ value ?? '' }
                    onChange={ ( next ) => onChange( name, next ) }
                    { ...( 'number' === field.trailing_type ? { allowNegative: true } : {} ) }
                />
                { field.trailing_text && (
                    <span className="absolute top-0 end-0 h-full flex items-center bg-gray-50 rounded-e-md text-gray-700 border border-gray-300 text-sm px-3">
                        { field.trailing_text }
                    </span>
                ) }
            </div>
        </>
    );
}
