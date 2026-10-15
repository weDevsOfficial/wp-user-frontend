import { useMemo } from '@wordpress/element';
import { MultiSelect } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';
import { builderData } from '../../../utils/boot';

/**
 * Multi-select input for field settings.
 * Replaces Vue field-multiselect component.
 *
 * Supports dynamic taxonomy term options when field is a taxonomy type.
 * On the shared MultiSelect wrapper (4.4c; develop used selectize): the
 * stored value is the list of option values (falsy -> []).
 */
export default function MultiSelectInput( { optionField, field, value, onChange } ) {
    // Dynamic options for taxonomy exclude fields
    const dynamicOptions = useMemo( () => {
        if (
            optionField.name === 'exclude' &&
            field &&
            field.input_type === 'taxonomy' &&
            field.name
        ) {
            const wpPostTypes = builderData().wp_post_types;

            if ( wpPostTypes ) {
                for ( const postType in wpPostTypes ) {
                    const taxonomies = wpPostTypes[ postType ];

                    if ( taxonomies && taxonomies.hasOwnProperty( field.name ) ) {
                        const taxField = taxonomies[ field.name ];

                        if ( taxField && taxField.terms && taxField.terms.length > 0 ) {
                            const opts = {};
                            taxField.terms.forEach( ( term ) => {
                                if ( term && term.term_id && term.name ) {
                                    opts[ term.term_id ] = term.name;
                                }
                            } );
                            return opts;
                        }
                    }
                }
            }
        }

        return optionField.options || {};
    }, [ optionField.name, optionField.options, field ] );

    return (
        <div className="panel-field-opt panel-field-opt-select">
            <div className="flex">
                { optionField.title && (
                    <label className="mb-0!">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>

            <MultiSelect
                className="term-list-selector w-full mt-1.5"
                options={ dynamicOptions }
                value={ Array.isArray( value ) ? value : [] }
                onChange={ ( next ) => onChange( next ) }
            />
        </div>
    );
}
