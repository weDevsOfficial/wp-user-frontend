/**
 * A progress bar (plugin-ui Progress with its track and indicator), and the
 * inline spinner the admin screens show next to a label.
 *
 * @since WPUF_SINCE
 */
import { Progress as PuiProgress, ProgressIndicator, ProgressTrack, Spinner as PuiSpinner, cn } from '@wedevs/plugin-ui';

/**
 * @param {Object} props
 * @param {number} props.value         0-100.
 * @param {string} props.label         Accessible name.
 * @param {string} [props.size='sm']   sm|md|lg
 * @param {string} [props.className]   Extra classes (layout only).
 *
 * @return {JSX.Element} Progress bar.
 */
export default function Progress( { value, label, size = 'sm', className } ) {
    return (
        <PuiProgress value={ Math.max( 0, Math.min( 100, Number( value ) || 0 ) ) } size={ size } rounded aria-label={ label } className={ className }>
            <ProgressTrack>
                <ProgressIndicator />
            </ProgressTrack>
        </PuiProgress>
    );
}

const SPIN = { xs: 'size-3.5', sm: 'size-4', md: 'size-5', lg: 'size-8' };

/**
 * @param {Object} props
 * @param {string} [props.size='sm']  xs|sm|md|lg
 * @param {string} [props.className]  Extra classes (colour, layout).
 *
 * @return {JSX.Element} Spinner.
 */
export function Spinner( { size = 'sm', className } ) {
    return <PuiSpinner aria-hidden="true" className={ cn( SPIN[ size ] || SPIN.sm, className ) } />;
}
