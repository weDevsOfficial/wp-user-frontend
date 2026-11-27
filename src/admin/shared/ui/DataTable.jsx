/**
 * List screens on plugin-ui DataViews (Dokan AdminDataViewTable pattern):
 * WPUF defaults around it (table layout, search, responsive, a per-screen
 * namespace for its wp.hooks filters). Needs the screen stylesheet built
 * with `dataviews: true` (tools/admin-css).
 */
import { DataViews } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';

/**
 * @param {Object}   props
 * @param {string}   props.screen         Screen key; namespace `wpuf-<screen>-table`
 *                                     (DataViews uses it as the element id
 *                                     and its filter prefix, so it must not
 *                                     equal a screen mount id).
 * @param {Array}    props.fields         DataViews fields.
 * @param {Array}    props.data           Rows (each with an `id`, or pass getItemId).
 * @param {Object}   props.view           DataViews view state.
 * @param {Function} props.onChangeView   ( view ) => void
 * @param {Object}   props.paginationInfo { totalItems, totalPages }
 */
export default function DataTable( { screen, searchLabel, ...props } ) {
    // Marked so DataViews keeps its styles inside a legacy host (design.md D25).
    return (
        <div data-wpuf-ui="">
            <DataViews
                namespace={ `wpuf-${ screen }-table` }
                defaultLayouts={ { table: {} } }
                search
                searchLabel={ searchLabel || __( 'Search', 'wp-user-frontend' ) }
                responsive
                { ...props }
            />
        </div>
    );
}
