import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function CheckboxPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const options = field.options || {};
    const hasOptions = Object.keys( options ).length > 0;
    const isInline = field.inline === 'yes';
    const selected = Array.isArray( field.selected ) ? field.selected : [ field.selected ];

    if ( ! hasOptions ) {
        return <div className="wpuf-fields"><HelpText text={ field.help } /></div>;
    }

    return (
        <div className="wpuf-fields">
            <div className={ isInline ? 'wpuf-flex' : 'wpuf-space-y-2' }>
                { Object.entries( options ).map( ( [ val, label ] ) => (
                    <div key={ val } className={ `wpuf-relative wpuf-flex wpuf-items-center ${ isInline ? 'wpuf-mr-4' : '' }` }>
                        <input
                            type="checkbox"
                            value={ val }
                            checked={ selected.includes( val ) }
                            className={ builderClassNames( 'checkbox' ) }
                            readOnly
                        />
                        <label>{ label }</label>
                    </div>
                ) ) }
            </div>
            <HelpText text={ field.help } />
        </div>
    );
}
