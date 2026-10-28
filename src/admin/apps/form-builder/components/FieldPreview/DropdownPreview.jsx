import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function DropdownPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const options = field.options || {};
    const hasOptions = Object.keys( options ).length > 0;

    return (
        <div className="wpuf-fields">
            <select className={ builderClassNames( 'dropdown' ) } value={ field.selected || '' } readOnly>
                { field.first && <option value="">{ field.first }</option> }
                { hasOptions && Object.entries( options ).map( ( [ val, label ] ) => (
                    <option key={ val } value={ label }>
                        { label }
                    </option>
                ) ) }
            </select>
            <HelpText text={ field.help } />
        </div>
    );
}
