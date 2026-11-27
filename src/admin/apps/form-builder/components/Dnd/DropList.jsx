/**
 * Pieces every canvas list uses for drag and drop (design.md D16): the list's
 * container attributes (read by the hit test in BuilderDnd) and the insertion
 * indicator.
 *
 * The indicator is a 3px line drawn over the rows (absolutely positioned, no
 * layout shift), so the content under the pointer never moves while a field
 * is dragged; develop's 50px placeholder box pushed the rows down and made the
 * drop point jump. Primary green when the list takes the field, red when it
 * refuses the dragged palette field.
 *
 * Rows of a list are its direct children (or those of its `data-dnd-rows`
 * child) with `data-dnd-item` and `data-index` (the field's index in that
 * list); rows and lists are `position: relative` for the indicator.
 *
 * @since WPUF_SINCE
 */

/**
 * @param {string} container Container id (utils/dndTree.js).
 * @param {number} length    Number of fields in the list.
 *
 * @return {Object} Attributes for the list element.
 */
export const dropListProps = ( container, length ) => ( {
    'data-dnd-container': container,
    'data-dnd-length': length,
} );

/**
 * Where a row shows the indicator.
 *
 * @param {Object|null} placeholder `{ index, blocked }` (usePlaceholder)
 * @param {number}      index       Row index.
 * @param {number}      length      List length.
 *
 * @return {Object|null} `{ edge: 'top'|'bottom', blocked }`
 */
export function indicatorFor( placeholder, index, length ) {
    if ( ! placeholder ) {
        return null;
    }

    if ( placeholder.index === index ) {
        return { edge: 'top', blocked: placeholder.blocked };
    }

    if ( index === length - 1 && placeholder.index >= length ) {
        return { edge: 'bottom', blocked: placeholder.blocked };
    }

    return null;
}

/**
 * @param {Object}  props
 * @param {string}  [props.edge]    top|bottom of the row (or list).
 * @param {boolean} [props.blocked] The list refuses the dragged field.
 */
export function Indicator( { edge = 'top', blocked = false } ) {
    return (
        <span
            aria-hidden="true"
            className={ `wpuf-dnd-indicator pointer-events-none absolute left-1 right-1 z-20 h-[3px] rounded-full ${ 'bottom' === edge ? 'bottom-0' : 'top-0' } ${ blocked ? 'bg-red-500' : 'bg-primary' }` }
        />
    );
}
