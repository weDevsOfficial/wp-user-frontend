import { useSelect } from '@wordpress/data';
import { ColorPicker } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { STORE_NAME } from '../../../../stores/settings/constants';

/**
 * Color setting on the shared ColorPicker (4.6a): label left, picker right in
 * a 2/5 row. A field may preview a default that follows another setting (Pro
 * login colors follow the chosen layout): display only, nothing is stored
 * until a color is picked.
 */
export default function ColorPickerField( { field, name, sectionId, value, onChange } ) {
    const previewKey = useSelect(
        ( select ) => ( field.preview_by ? select( STORE_NAME ).getValue( sectionId, field.preview_by ) : null ),
        [ field.preview_by, sectionId ]
    );
    const preview = field.preview && 'object' === typeof field.preview
        ? field.preview[ previewKey || Object.keys( field.preview )[ 0 ] ] || ''
        : '';

    return (
        <div className="flex items-center justify-between w-2/5">
            <SettingLabel field={ field } htmlFor={ name } />
            <ColorPicker
                id={ name }
                className="ml-2"
                value={ value || preview || field.default || '#000000' }
                onChange={ ( next ) => onChange( name, next ) }
            />
        </div>
    );
}
