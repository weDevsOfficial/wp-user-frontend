import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function MultiSelectPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const options = field.options || {};
    const hasOptions = Object.keys( options ).length > 0;

    const selected = Array.isArray( field.selected ) ? field.selected : ( field.selected ? [ field.selected ] : [] );

    return (
        <div className="wpuf-fields">
            <select
                className={ `${ builderClassNames( 'multi_label' ) } wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300` }
                multiple
                value={ selected }
                readOnly
            >
                { field.first && <option value="">{ field.first }</option> }
                { hasOptions && Object.entries( options ).map( ( [ val, label ] ) => (
                    <option key={ val } value={ label }>{ label }</option>
                ) ) }
            </select>
            <HelpText text={ field.help } />
        </div>
    );
}
