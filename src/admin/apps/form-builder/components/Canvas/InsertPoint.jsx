/**
 * Add a field *here*: a "+" that opens a searchable list of field types
 * anchored to it (FlyForms' builder `InsertPoint`). Picking a type does what
 * a palette click / drop does at this position: Pro preview alert, validation
 * alert, then the stage (useAddField: single instance, custom field tip) or a
 * column cell (usePaletteDrop: develop's column checks). In a column the list
 * leaves out the types a column refuses.
 *
 * Variants:
 * - `seam`: a 32px gap between two stage fields, the "+" centred in it with
 *   room above and below (owner: no overlap with the field borders), shown on
 *   hover / focus. Not a drag row (no `data-dnd-item`), so the drop indicator
 *   positions are unchanged.
 * - `cell`: a row under a column cell's fields, shown while the cell is hovered.
 * - `empty`: centred over an empty column cell, shown while the cell is hovered.
 *
 * @since WPUF_SINCE
 */
import { useMemo, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Popover, PopoverContent, PopoverTrigger } from '@wedevs/plugin-ui';
import { Plus, Search } from 'lucide-react';
import { STORE_NAME } from '../../store';
import { filterPanelSections } from '../../extensions/hooks';
import { getFieldValidators } from '../../extensions/registry';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { openProFieldAlert, openValidationAlert } from '../../common/BuilderDialogs';
import useAddField from '../../hooks/useAddField';
import usePaletteDrop from '../Dnd/usePaletteDrop';
import { canDrop } from '../../utils/dndTree';
import { builderData } from '../../utils/boot';

// Inline styles: the list renders in a body portal, outside the builder's
// scoped utility classes.
const S = {
    panel: { width: 288, maxHeight: 360, display: 'flex', flexDirection: 'column', fontFamily: '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif' },
    searchWrap: { position: 'relative', padding: 8, borderBottom: '1px solid #e5e7eb' },
    searchIcon: { position: 'absolute', left: 18, top: '50%', transform: 'translateY(-50%)', color: '#9ca3af', pointerEvents: 'none' },
    search: { width: '100%', height: 36, padding: '0 10px 0 32px', border: '1px solid #d1d5db', borderRadius: 6, fontSize: 14, color: '#111827', outline: 'none', boxSizing: 'border-box', background: '#fff', boxShadow: 'none' },
    list: { overflowY: 'auto', padding: 4 },
    group: { padding: '8px 8px 4px', fontSize: 11, fontWeight: 600, letterSpacing: '.04em', textTransform: 'uppercase', color: '#6b7280' },
    item: { display: 'flex', width: '100%', alignItems: 'center', gap: 8, padding: '7px 8px', border: 0, borderRadius: 6, background: 'transparent', color: '#374151', fontSize: 13, textAlign: 'left', cursor: 'pointer' },
    itemActive: { background: '#ecfdf5', color: '#065f46' },
    icon: { width: 16, height: 16, flexShrink: 0 },
    badge: { marginLeft: 'auto', height: 14 },
    empty: { padding: 16, fontSize: 13, color: '#6b7280', textAlign: 'center' },
};

/**
 * Icon URL of a palette field (as FieldItem).
 *
 * @param {Object}  config      Field settings.
 * @param {boolean} isProActive Pro active.
 *
 * @return {string} URL.
 */
const iconUrl = ( config, isProActive ) => {
    if ( ! config || ! config.icon ) {
        return '';
    }

    const data = builderData();

    return ( isProActive && config.pro_feature ? data.pro_asset_url : data.asset_url ) + '/images/' + config.icon + '.svg';
};

/**
 * Seam with the "+".
 *
 * @since WPUF_SINCE
 *
 * @param {Object} props
 * @param {number} props.index       Position a picked field lands at.
 * @param {string} [props.container] Drop list id of a column cell (stage when empty).
 * @param {string} [props.variant]   seam|cell|empty (default seam).
 *
 * @return {JSX.Element} Seam.
 */
export default function InsertPoint( { index, container = '', variant = 'seam' } ) {
    const { panelSections, fieldSettings, isProActive } = useSelect( ( select ) => {
        const store = select( STORE_NAME );

        return {
            panelSections: store.getPanelSections(),
            fieldSettings: store.getFieldSettings(),
            isProActive: store.getIsProActive(),
        };
    }, [] );
    const addField = useAddField();
    const dropField = usePaletteDrop();
    const [ open, setOpen ] = useState( false );
    const [ term, setTerm ] = useState( '' );
    const [ hover, setHover ] = useState( '' );

    const sections = useMemo( () => {
        const needle = term.trim().toLowerCase();

        return filterPanelSections( panelSections )
            .map( ( section ) => ( {
                ...section,
                fields: section.fields.filter( ( template ) => {
                    const config = fieldSettings[ template ];

                    if ( ! config || ( container && ! canDrop( { kind: 'palette', template }, container ) ) ) {
                        return false;
                    }

                    return ! needle || String( config.title || template ).toLowerCase().includes( needle );
                } ),
            } ) )
            .filter( ( section ) => section.fields.length );
    }, [ panelSections, fieldSettings, term, container ] );

    const pick = ( template ) => {
        const config = fieldSettings[ template ] || {};
        const data = builderData();

        setOpen( false );
        setTerm( '' );

        if ( ! isProActive && config.pro_feature ) {
            openProFieldAlert( config.title || template, ( ( data.i18n || {} ).pro_field_message || {} )[ template ] );
            return;
        }

        if ( isFailedToValidate( template, { [ template ]: config }, getFieldValidators() ) ) {
            if ( config.validator && config.validator.msg ) {
                openValidationAlert( config.validator );
            }
            return;
        }

        if ( container ) {
            dropField( template, { container, index } );
        } else {
            addField( template, index );
        }
    };

    const shown = open ? 'opacity-100! border-primary text-primary' : `opacity-0 ${ 'seam' === variant ? 'group-hover/seam:opacity-100!' : 'group-hover/cell:opacity-100!' }`;
    const label = container ? __( 'Add a field to this column', 'wp-user-frontend' ) : __( 'Add a field here', 'wp-user-frontend' );
    const wrapClass = {
        seam: `wpuf-insert-seam group/seam relative m-0! flex h-8 list-none items-center justify-center p-0! ${ open ? 'is-open' : '' }`,
        cell: 'wpuf-insert-cell relative flex h-8 items-center justify-center',
        empty: 'wpuf-insert-cell pointer-events-none absolute inset-0 z-10 flex items-center justify-center',
    }[ variant ];
    const Wrap = 'seam' === variant ? 'li' : 'div';

    return (
        <Wrap className={ wrapClass } data-insert-index={ index }>
            { 'seam' === variant ? (
                <>
                    { /* Hover target the whole way across the gap and the hairline it
                       lights, so the "+" is not a 24px spot to hunt for. */ }
                    <span aria-hidden="true" className="absolute inset-0 z-10" />
                    <span aria-hidden="true" className={ `pointer-events-none absolute inset-x-0 top-1/2 z-10 h-px ${ open ? 'bg-primary/40' : 'bg-transparent group-hover/seam:bg-primary/40' }` } />
                </>
            ) : (
                <span aria-hidden="true" className={ `pointer-events-none absolute inset-x-1 top-1/2 h-px ${ open ? 'bg-primary/40' : 'bg-transparent group-hover/cell:bg-primary/40' }` } />
            ) }
            <Popover open={ open } onOpenChange={ ( next ) => { setOpen( next ); if ( ! next ) { setTerm( '' ); } } }>
                <PopoverTrigger
                    aria-label={ label }
                    className={ `${ 'seam' === variant ? 'absolute z-20' : 'pointer-events-auto relative' } flex size-6 cursor-pointer items-center justify-center rounded-full border border-solid border-gray-300 bg-white p-0 text-gray-500 shadow-sm transition-opacity hover:border-primary hover:text-primary focus-visible:opacity-100! ${ shown }` }
                >
                    <Plus size={ 14 } aria-hidden="true" />
                </PopoverTrigger>
                <PopoverContent align="center" className="p-0">
                    <div style={ S.panel }>
                        <div style={ S.searchWrap }>
                            <Search size={ 14 } style={ S.searchIcon } aria-hidden="true" />
                            { /* eslint-disable-next-line jsx-a11y/no-autofocus */ }
                            <input
                                type="search"
                                autoFocus
                                value={ term }
                                onChange={ ( e ) => setTerm( e.target.value ) }
                                onKeyDown={ ( e ) => {
                                    if ( 'Enter' === e.key && sections.length ) {
                                        e.preventDefault();
                                        pick( sections[ 0 ].fields[ 0 ] );
                                    }
                                } }
                                placeholder={ __( 'Search fields', 'wp-user-frontend' ) }
                                aria-label={ __( 'Search fields', 'wp-user-frontend' ) }
                                style={ S.search }
                            />
                        </div>
                        <div style={ S.list } role="listbox" aria-label={ __( 'Field types', 'wp-user-frontend' ) }>
                            { sections.length ? sections.map( ( section ) => (
                                <div key={ section.id }>
                                    <div style={ S.group }>{ section.title }</div>
                                    { section.fields.map( ( template ) => {
                                        const config = fieldSettings[ template ] || {};
                                        const locked = ! isProActive && config.pro_feature;
                                        const url = iconUrl( config, isProActive );

                                        return (
                                            <button
                                                key={ template }
                                                type="button"
                                                role="option"
                                                aria-selected={ hover === template }
                                                data-insert-field={ template }
                                                onMouseEnter={ () => setHover( template ) }
                                                onMouseLeave={ () => setHover( '' ) }
                                                onClick={ () => pick( template ) }
                                                style={ { ...S.item, ...( hover === template ? S.itemActive : {} ), opacity: locked ? 0.6 : 1 } }
                                            >
                                                { url ? <img src={ url } alt="" style={ S.icon } /> : <span style={ S.icon } /> }
                                                <span>{ config.title || template }</span>
                                                { locked ? <img src={ `${ builderData().asset_url || '' }/images/pro-badge.svg` } alt="" style={ S.badge } /> : null }
                                            </button>
                                        );
                                    } ) }
                                </div>
                            ) ) : (
                                <div style={ S.empty }>{ __( 'No fields found', 'wp-user-frontend' ) }</div>
                            ) }
                        </div>
                    </div>
                </PopoverContent>
            </Popover>
        </Wrap>
    );
}
