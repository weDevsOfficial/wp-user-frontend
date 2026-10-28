import { sprintf, __ } from '@wordpress/i18n';
import { MultiSelect } from '@wpuf/components';
import { builderData } from '../../../utils/boot';

/**
 * Default terms per hierarchical taxonomy of the selected post type
 * (develop: `wpuf_form_setting_post` AJAX rows that replace the static
 * Default Category row, form-builder.js populate_default_categories), from
 * `wpuf_form_builder.wp_post_types` (4.4e). Each row stores the picked term
 * ids under `default_<taxonomy>`; none picked leaves the key out, as develop's
 * multi-select posted nothing.
 *
 * @param {Object}   props
 * @param {Object}   props.settings Resolved settings (post_type, default_<taxonomy>).
 * @param {Function} props.onChange ( name, value ) => void
 */
export default function TaxonomyDefaults( { settings, onChange } ) {
    const postTypes = builderData().wp_post_types || {};
    const taxonomies = Object.entries( postTypes[ settings.post_type || 'post' ] || {} ).filter( ( [ , tax ] ) => tax && tax.hierarchical );

    return taxonomies.map( ( [ taxonomy, tax ] ) => {
        const key = `default_${ taxonomy }`;
        const stored = settings[ key ];
        const options = ( tax.terms || [] ).map( ( term ) => ( { value: String( term.term_id ), label: term.name } ) );

        return (
            <div key={ taxonomy } className="mt-6 wpuf-input-container taxonomy-container" data-taxonomy={ taxonomy }>
                <div className="flex items-center">
                    <label htmlFor={ `${ key }_select` } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                        { /* translators: %s: taxonomy label, e.g. Categories */ }
                        { sprintf( __( 'Default %s', 'wp-user-frontend' ), tax.title ) }
                    </label>
                </div>
                <MultiSelect
                    id={ `${ key }_select` }
                    className="w-full mt-2"
                    options={ options }
                    value={ ( Array.isArray( stored ) ? stored : ( stored ? [ stored ] : [] ) ).map( String ) }
                    onChange={ ( next ) => onChange( key, next.length ? next : undefined ) }
                />
            </div>
        );
    } );
}
