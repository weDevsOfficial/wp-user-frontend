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
            <div className={ isInline ? '[&>:not([hidden])~:not([hidden])]:mt-6 [&>:not([hidden])~:not([hidden])]:mb-0 sm:flex sm:items-center sm:[&>:not([hidden])~:not([hidden])]:ml-10 sm:[&>:not([hidden])~:not([hidden])]:mr-0 sm:[&>:not([hidden])~:not([hidden])]:mt-0 sm:[&>:not([hidden])~:not([hidden])]:mb-0' : '[&>:not([hidden])~:not([hidden])]:mt-2 [&>:not([hidden])~:not([hidden])]:mb-0' }>
                { Object.entries( options ).map( ( [ val, label ] ) => (
                    <div key={ val } className="flex items-center">
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
