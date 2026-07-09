import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function TaxonomyPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const type = field.type || 'select';

    if ( type === 'text' ) {
        return (
            <div className="wpuf-fields">
                <input
                    type="text"
                    placeholder={ field.placeholder || '' }
                    size={ field.size }
                    className={ builderClassNames( 'text' ) }
                    readOnly
                />
                <HelpText text={ field.help } />
            </div>
        );
    }

    if ( type === 'checkbox' ) {
        return (
            <div className="wpuf-fields">
                <div className="category-wrap">
                    <div className={ field.show_inline === 'yes' ? 'wpuf-flex wpuf-flex-wrap wpuf-gap-4' : 'wpuf-space-y-2' }>
                        <div className="wpuf-flex wpuf-items-center">
                            <input type="checkbox" className={ builderClassNames( 'checkbox' ) } readOnly />
                            <label>{ __( 'Category', 'wp-user-frontend' ) }</label>
                        </div>
                    </div>
                </div>
                <HelpText text={ field.help } />
            </div>
        );
    }

    if ( type === 'multiselect' ) {
        return (
            <div className="wpuf-fields">
                <div className="category-wrap">
                    <select
                        className={ `${ builderClassNames( 'select' ) } !wpuf-text-base` }
                        multiple
                        readOnly
                    >
                        <option>{ __( '— Select —', 'wp-user-frontend' ) }</option>
                    </select>
                </div>
                <HelpText text={ field.help } />
            </div>
        );
    }

    // select or ajax
    return (
        <div className="wpuf-fields">
            <select
                className={ `${ builderClassNames( 'select' ) } !wpuf-text-base` }
                readOnly
            >
                <option>{ __( '— Select —', 'wp-user-frontend' ) }</option>
            </select>
            <HelpText text={ field.help } />
        </div>
    );
}
