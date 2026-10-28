/**
 * Loading placeholders on plugin-ui Skeleton / Spinner.
 * - Skeleton: `lines` bars (or one block with className sizing);
 * - Loading: the centered primary spinner the subscriptions screen shows
 *   while it loads (48px, develop's green-500), as plugin-ui's Spinner;
 * - SkeletonBar: one bar sized by its classes (inline placeholders);
 * - TableSkeleton: a list table's frame (40px header row, 56px rows, the
 *   table's borders) with bars, so nothing jumps when the rows arrive.
 */
import { Skeleton as PuiSkeleton, Spinner, cn } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';

/**
 * @param {Object} props
 * @param {number} [props.lines] Number of bars (default 1).
 */
export default function Skeleton( { lines = 1, className } ) {
    return (
        <div data-wpuf-ui="" className="grid gap-3" aria-busy="true" aria-live="polite">
            { Array.from( { length: lines } ).map( ( _, index ) => (
                <PuiSkeleton key={ index } className={ cn( 'h-4 rounded-md bg-gray-200', index === lines - 1 && lines > 1 && 'w-2/3', className ) } />
            ) ) }
        </div>
    );
}

/**
 * @param {Object} props
 * @param {string} [props.label]  Screen reader text.
 * @param {string} [props.height] Area height class (default full viewport like develop).
 */
export function Loading( { label, height = 'h-svh', className } ) {
    return (
        <div data-wpuf-ui="" className={ cn( 'flex items-center justify-center', height, className ) } role="status">
            <Spinner className="size-12 text-green-500" />
            <span className="sr-only">{ label || __( 'Loading…', 'wp-user-frontend' ) }</span>
        </div>
    );
}

/**
 * @param {Object} props
 * @param {string} [props.className] Size / shape classes (e.g. `h-4 w-40`).
 *
 * @return {JSX.Element} Bar.
 */
export function SkeletonBar( { className } ) {
    return <PuiSkeleton className={ cn( 'h-3.5 rounded-md bg-gray-200', className ) } />;
}

const ROW = 'flex items-center gap-4 px-4 border-0 border-b border-solid border-gray-200';
const FORM_COLUMNS = [ 'h-3.5 flex-1', 'hidden h-3.5 w-32 sm:block', 'hidden h-3.5 w-24 md:block', 'h-5 w-16 rounded-full' ];

/**
 * @param {Object}   props
 * @param {number}   [props.rows=6]       Body rows.
 * @param {boolean}  [props.checkbox=true] A select column first.
 * @param {string[]} [props.columns]       One class list per bar of a row.
 *
 * @return {JSX.Element} Table placeholder.
 */
export function TableSkeleton( { rows = 6, checkbox = true, columns = FORM_COLUMNS } ) {
    const box = checkbox ? <SkeletonBar className="size-4 rounded-[4px]" /> : null;

    return (
        <div data-wpuf-ui="" role="status" aria-busy="true" aria-live="polite">
            <span className="sr-only">{ __( 'Loading…', 'wp-user-frontend' ) }</span>
            <div className={ ROW + ' h-10' }>
                { box }
                <SkeletonBar className="h-3 w-24" />
            </div>
            { Array.from( { length: rows } ).map( ( _, index ) => (
                <div key={ index } className={ ROW + ' h-14 last:border-b-0' }>
                    { box }
                    { columns.map( ( column, at ) => <SkeletonBar key={ at } className={ column } /> ) }
                </div>
            ) ) }
        </div>
    );
}
