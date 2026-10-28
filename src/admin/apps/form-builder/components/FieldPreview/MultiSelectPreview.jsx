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
                className={ `${ builderClassNames( 'multi_label' ) } block w-full min-w-full rounded-md py-1.5 text-gray-900 shadow-xs placeholder:text-gray-400 sm:text-sm sm:leading-6 border border-gray-300!` }
                multiple
                value={ selected }
                readOnly
            >
                { field.first && <option value="">{ field.first }</option> }
                { hasOptions && Object.entries( options ).map( ( [ val, label ] ) => (
                    <option key={ val } value={ label }>{ label }</option>
                ) ) }
            </select>
            <HelpText text={ field.help } className="wpuf-help" />
        </div>
    );
}
