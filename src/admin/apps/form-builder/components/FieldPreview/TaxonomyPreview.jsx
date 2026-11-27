import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../../store/constants';
import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';
import { builderData } from '../../utils/boot';

/**
 * Terms of the field's taxonomy from the localized post types, as develop's
 * form-taxonomy `terms` computed.
 *
 * @param {string} taxonomy Taxonomy name (the field name)
 * @return {Array}
 */
function getTerms( taxonomy ) {
    const postTypes = builderData().wp_post_types || {};

    for ( const postType of Object.keys( postTypes ) ) {
        const taxonomies = postTypes[ postType ] || {};

        if ( Object.prototype.hasOwnProperty.call( taxonomies, taxonomy ) && taxonomies[ taxonomy ].terms ) {
            return taxonomies[ taxonomy ].terms;
        }
    }

    return [];
}

/**
 * Filter (include / exclude / child_of), order and nest terms like develop's
 * `sorted_terms`.
 *
 * @param {Object} field Taxonomy field
 * @return {Array} Top level terms, each with `children`
 */
function sortTerms( field ) {
    let terms = getTerms( field.name ).map( ( term ) => ( { ...term } ) );

    if ( field.exclude_type && field.exclude ) {
        const exclude = Array.isArray( field.exclude ) ? field.exclude : String( field.exclude ).split( ',' );
        const ids = exclude.map( ( id ) => parseInt( String( id ).trim(), 10 ) ).filter( ( id ) => isFinite( id ) );

        terms = terms.filter( ( term ) => {
            switch ( field.exclude_type ) {
                case 'exclude':
                    return ! ids.includes( term.term_id );
                case 'include':
                    return ids.includes( term.term_id );
                case 'child_of':
                    return ids.includes( parseInt( term.parent, 10 ) );
                default:
                    return false;
            }
        } );
    }

    const orderby = field.orderby;
    terms = terms
        .map( ( term, index ) => ( { term, index } ) )
        .sort( ( a, b ) => {
            const x = a.term[ orderby ];
            const y = b.term[ orderby ];

            if ( x === y || x === undefined || y === undefined ) {
                return a.index - b.index;
            }

            return x < y ? -1 : 1;
        } )
        .map( ( item ) => item.term );

    if ( 'DESC' === field.order ) {
        terms.reverse();
    }

    const withChildren = ( parentId ) => terms
        .filter( ( term ) => parseInt( term.parent, 10 ) === parseInt( parentId, 10 ) )
        .map( ( term ) => ( { ...term, children: withChildren( term.term_id ) } ) );

    const parents = terms
        .filter( ( term ) => ! term.parent )
        .map( ( term ) => ( { ...term, children: withChildren( term.term_id ) } ) );

    return parents.length ? parents : terms.map( ( term ) => ( { ...term, children: [] } ) );
}

/**
 * Flatten nested terms into dropdown options, children indented two spaces
 * per level.
 *
 * @param {Array}  terms Nested terms
 * @param {number} level Depth
 * @return {Array} `<option>` elements
 */
function termOptions( terms, level = 0 ) {
    return terms.flatMap( ( term ) => [
        <option key={ term.term_id || term.id } value={ term.id }>
            { '  '.repeat( level ) + term.name }
        </option>,
        ...termOptions( term.children || [], level + 1 ),
    ] );
}

function TermChecklist( { terms } ) {
    return (
        <ul className="wpuf-category-checklist">
            { terms.map( ( term ) => (
                <TermChecklistItem key={ term.term_id || term.id } term={ term } />
            ) ) }
        </ul>
    );
}

function TermChecklistItem( { term } ) {
    return (
        <>
            <li><label className="selectit"><input type="checkbox" /> { term.name }</label></li>
            { term.children && term.children.length > 0 && (
                <ul className="children">
                    { term.children.map( ( child ) => (
                        <TermChecklistItem key={ child.term_id || child.id } term={ child } />
                    ) ) }
                </ul>
            ) }
        </>
    );
}

function flattenTerms( terms ) {
    return terms.flatMap( ( term ) => [ term, ...flattenTerms( term.children || [] ) ] );
}

/**
 * Taxonomy preview: the field's real terms, per field type, as develop's
 * form-taxonomy template.
 */
export default function TaxonomyPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const type = field.type;
    const terms = useMemo(
        () => sortTerms( field ),
        [ field.name, field.exclude_type, field.exclude, field.orderby, field.order ]
    );
    const selectClass = `${ builderClassNames( 'select' ) } text-base!`;
    // Develop renders no preview for a taxonomy without builder settings (e.g.
    // a custom taxonomy while Pro is off).
    const isAvailable = useSelect( ( select ) => !! select( STORE_NAME ).getFieldSettings()[ field.name ], [ field.name ] );

    if ( ! isAvailable ) {
        return null;
    }

    return (
        <div className="wpuf-fields">
            { 'select' === type && (
                <select className={ selectClass }>
                    <option value="">{ field.first }</option>
                    { termOptions( terms ) }
                </select>
            ) }

            { 'ajax' === type && (
                <div className="category-wrap">
                    <div>
                        <select className={ selectClass }>
                            <option className="text-base leading-none!">{ __( '— Select —', 'wp-user-frontend' ) }</option>
                            { terms.map( ( term ) => (
                                <option key={ term.term_id || term.id } value={ term.id }>{ term.name }</option>
                            ) ) }
                        </select>
                    </div>
                </div>
            ) }

            { 'multiselect' === type && (
                <div className="category-wrap">
                    <select className={ selectClass } multiple>
                        { termOptions( terms ) }
                    </select>
                </div>
            ) }

            { 'checkbox' === type && (
                <div className="category-wrap">
                    { 'yes' === field.show_inline ? (
                        <div className="category-wrap">
                            <div>
                                { flattenTerms( terms ).map( ( term ) => (
                                    <label key={ term.term_id || term.id } className="wpuf-checkbox-inline">
                                        <input type="checkbox" /> { term.name }
                                    </label>
                                ) ) }
                            </div>
                        </div>
                    ) : (
                        <div className="category-wrap">
                            <div><TermChecklist terms={ terms } /></div>
                        </div>
                    ) }
                </div>
            ) }

            { 'text' === type && (
                <input
                    type="text"
                    placeholder={ field.placeholder || '' }
                    size={ field.size }
                    className={ builderClassNames( 'text' ) }
                    defaultValue=""
                    autoComplete="off"
                    readOnly
                />
            ) }

            <HelpText text={ field.help } />
        </div>
    );
}
