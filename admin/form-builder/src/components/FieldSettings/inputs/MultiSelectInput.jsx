import { useMemo, useEffect, useRef } from '@wordpress/element';
import SettingHelpText from './SettingHelpText';

/**
 * Multi-select input for field settings.
 * Replaces Vue field-multiselect component.
 *
 * Supports dynamic taxonomy term options when field is a taxonomy type.
 * Uses native <select multiple> with selectize-compatible class.
 */
export default function MultiSelectInput( { optionField, field, value, onChange } ) {
    const selectRef = useRef( null );

    // Dynamic options for taxonomy exclude fields
    const dynamicOptions = useMemo( () => {
        if (
            optionField.name === 'exclude' &&
            field &&
            field.input_type === 'taxonomy' &&
            field.name
        ) {
            const wpPostTypes = window.wpuf_form_builder?.wp_post_types;

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

    // Initialize selectize if jQuery + selectize are available
    useEffect( () => {
        const $select = window.jQuery && selectRef.current
            ? window.jQuery( selectRef.current )
            : null;

        if ( ! $select || ! $select.selectize ) {
            return;
        }

        // Destroy existing instance
        if ( $select[ 0 ] && $select[ 0 ].selectize ) {
            $select[ 0 ].selectize.destroy();
        }

        $select.selectize( {} ).on( 'change', function () {
            const newVal = $select.val();
            onChange( newVal || [] );
        } );

        return () => {
            if ( $select[ 0 ] && $select[ 0 ].selectize ) {
                $select[ 0 ].selectize.destroy();
            }
        };
    }, [ dynamicOptions, onChange ] );

    const handleNativeChange = ( e ) => {
        const selected = Array.from( e.target.selectedOptions ).map( ( opt ) => opt.value );
        onChange( selected );
    };

    return (
        <div className="panel-field-opt panel-field-opt-select">
            <div className="wpuf-flex">
                { optionField.title && (
                    <label className="!wpuf-mb-0">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>

            <select
                ref={ selectRef }
                className="term-list-selector wpuf-w-full wpuf-mt-2 wpuf-border-primary wpuf-z-30"
                value={ Array.isArray( value ) ? value : [] }
                onChange={ handleNativeChange }
                multiple
            >
                { Object.entries( dynamicOptions ).map( ( [ key, label ] ) => (
                    <option
                        key={ key }
                        value={ key }
                        className="checked:wpuf-bg-primary"
                    >
                        { label }
                    </option>
                ) ) }
            </select>
        </div>
    );
}
