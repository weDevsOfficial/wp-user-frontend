import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function RadioPreview( { field } ) {
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
            <div className={ isInline ? 'wpuf-space-y-6 sm:wpuf-flex sm:wpuf-items-center sm:wpuf-space-x-10 sm:wpuf-space-y-0' : 'wpuf-space-y-2' }>
                { Object.entries( options ).map( ( [ val, label ] ) => (
                    <div key={ val } className="wpuf-flex wpuf-items-center">
                        <input
                            type="radio"
                            value={ val }
                            checked={ selected.includes( val ) }
                            id={ `radio-${ field.name }-${ val }` }
                            className={ builderClassNames( 'radio' ) }
                            readOnly
                        />
                        <label htmlFor={ `radio-${ field.name }-${ val }` }>{ label }</label>
                    </div>
                ) ) }
            </div>
            <HelpText text={ field.help } />
        </div>
    );
}
