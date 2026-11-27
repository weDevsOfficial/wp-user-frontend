import { TextInput } from '@wpuf/components';
import { CONTROL_SIZE } from '../controlSize';
import SettingLabel, { LongHelp } from './SettingLabel';
import CopyValue from './CopyValue';
import { Notice } from '@wpuf/components';

/**
 * Text setting (legacy text / email / url / password and text-like callbacks)
 * on the shared TextInput (4.6a). Shows the stored value, else the default;
 * stores the string as typed.
 */
export default function TextField( { field, name, value, onChange } ) {
    // Generated values the admin only copies (PayPal / Stripe webhook URLs):
    // the schema marks them `readonly`, `attr: disabled` or class `disabled`.
    const readOnly = !! field.readonly || 'disabled' === field.attr || /(^|\s)disabled(\s|$)/.test( field.class || '' );

    if ( readOnly ) {
        return (
            <>
                <SettingLabel field={ field } htmlFor={ name } />
                <CopyValue value={ value || field.default || '' } id={ name } />
                <LongHelp html={ field.long_help } />
            </>
        );
    }

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <TextInput
                id={ name }
                className={ CONTROL_SIZE }
                value={ value || field.default || '' }
                onChange={ ( next ) => onChange( name, next ) }
                placeholder={ field.placeholder || '' }
            />
            { field.notice && (
                <Notice tone="warning" className="mt-3">{ field.notice.text }</Notice>
            ) }
            <LongHelp html={ field.long_help } />
        </>
    );
}
