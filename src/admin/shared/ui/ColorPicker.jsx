/**
 * Color picker; value and onChange use the color string as stored (hex).
 *
 * FlyHR's colour field (flyhr LeavePolicyPrimaryFields): the browser's own
 * colour input on plugin-ui tokens, with the hex value beside it. plugin-ui
 * does not export its ColorPicker (pinned acc2771, also upstream main), so
 * the admin screens use the same control FlyHR uses.
 */
import { cn } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';

/** `#rgb` / `#rrggbb` as `#rrggbb` (what the colour input takes), else ''. */
const toHex = ( value ) => {
    const color = String( value || '' ).trim();

    if ( /^#[0-9a-f]{6}$/i.test( color ) ) {
        return color.toLowerCase();
    }

    if ( /^#[0-9a-f]{3}$/i.test( color ) ) {
        return ( '#' + color.slice( 1 ).split( '' ).map( ( c ) => c + c ).join( '' ) ).toLowerCase();
    }

    return '';
};

/**
 * @param {Object}   props
 * @param {string}   [props.value]    Color (`#rrggbb`).
 * @param {Function} [props.onChange] ( color: string ) => void
 * @param {boolean}  [props.disabled] Disabled.
 * @param {string}   [props.id]       Input id (the label's `for`).
 */
export default function ColorPicker( { value, onChange, disabled, className, id, ...props } ) {
    const hex = toHex( value );

    return (
        // data-wpuf-ui: the admin sheet applies plugin-ui token classes inside the
        // plugin-ui host only to marked parts.
        <span data-wpuf-ui="" className={ cn( 'inline-flex items-center gap-3', className ) }>
            <input
                id={ id }
                type="color"
                value={ hex || '#000000' }
                disabled={ disabled }
                aria-label={ props[ 'aria-label' ] || __( 'Choose color', 'wp-user-frontend' ) }
                onChange={ ( event ) => onChange?.( event.target.value ) }
                className="h-10 w-16 cursor-pointer rounded-md border border-border bg-background p-1 disabled:cursor-not-allowed disabled:opacity-50"
            />
            <span className="font-mono text-sm text-muted-foreground">{ hex || String( value || '' ) || __( 'None', 'wp-user-frontend' ) }</span>
        </span>
    );
}
