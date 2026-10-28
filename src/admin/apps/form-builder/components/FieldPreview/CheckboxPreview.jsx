import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function CheckboxPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const options = field.options || {};
    const hasOptions = Object.keys( options ).length > 0;
    const isInline = field.inline === 'yes';
    const selected = Array.isArray( field.selected ) ? field.selected : [ field.selected ];

    return (
        <div className="wpuf-fields">
            <div className={ isInline ? 'flex' : '[&>:not([hidden])~:not([hidden])]:mt-2 [&>:not([hidden])~:not([hidden])]:mb-0' }>
                { hasOptions && Object.entries( options ).map( ( [ val, label ] ) => (
                    isInline ? (
                        <div key={ val } className="relative flex items-center mr-4">
                            <input
                                type="checkbox"
                                value={ val }
                                checked={ selected.includes( val ) }
                                className={ `${ builderClassNames( 'checkbox' ) } mt-[.5px]! rounded-sm border-gray-300 text-indigo-600` }
                                readOnly
                            />
                            <label>{ label }</label>
                        </div>
                    ) : (
                        // Develop wraps the stacked input and label in one more flex row.
                        <div key={ val } className="relative flex items-center">
                            <div className="flex items-center">
                                <input
                                    type="checkbox"
                                    value={ val }
                                    checked={ selected.includes( val ) }
                                    className={ builderClassNames( 'checkbox' ) }
                                    readOnly
                                />
                                <label>{ label }</label>
                            </div>
                        </div>
                    )
                ) ) }
            </div>
            <HelpText text={ field.help } />
        </div>
    );
}
