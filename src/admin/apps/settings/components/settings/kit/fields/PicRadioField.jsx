import { useCallback } from '@wordpress/element';
import { PicRadio } from '@wpuf/components';

/**
 * Picture radio field (type="pic-radio", the login form layout picker): the
 * shared compact card picker.
 */
export default function PicRadioField( { field, name, value, onChange } ) {
    const select = useCallback( ( key ) => onChange( name, key ), [ name, onChange ] );

    return <PicRadio id={ name } label={ field.label } options={ field.options || {} } value={ value } onChange={ select } />;
}
