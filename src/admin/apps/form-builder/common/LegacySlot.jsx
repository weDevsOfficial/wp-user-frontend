import { memo, useEffect, useRef } from '@wordpress/element';

/**
 * Output other plugins printed on the PHP builder hooks (settings rows, tabs),
 * collected and sanitized by `includes/Builder/HookBridge.php` (wp_kses with a
 * form-element allowlist) and delivered in `wpuf_form_builder.legacy_slots`.
 *
 * Inputs named `wpuf_settings[...]` inside a slot are saved with the form, as
 * develop's form post saved them, once the user edited that slot (an untouched
 * save adds no data). Their state lives in `registry` so edits
 * survive the settings panel unmounting (switching to the Form Editor tab).
 */
const registry = new Map();

/**
 * Enabled form controls of a slot (disabled ones are not posted, as in a form).
 *
 * @param {HTMLElement} node Slot container
 * @return {Array} Controls
 */
function controlsOf( node ) {
    return Array.from( node.querySelectorAll( 'input[name], select[name], textarea[name]' ) )
        .filter( ( control ) => ! control.matches( ':disabled' ) );
}

/**
 * Current state of every control, by position.
 *
 * @param {HTMLElement} node Slot container
 * @return {Array} State per control
 */
function readState( node ) {
    return controlsOf( node ).map( ( control ) => ( {
        name: control.name,
        type: control.type,
        value: control.value,
        checked: control.checked,
        selected: 'SELECT' === control.tagName ? Array.from( control.options ).filter( ( o ) => o.selected ).map( ( o ) => o.value ) : null,
    } ) );
}

/**
 * Put a saved state back into a freshly mounted slot.
 *
 * @param {HTMLElement} node  Slot container
 * @param {Array}       state Saved state
 */
function applyState( node, state ) {
    controlsOf( node ).forEach( ( control, index ) => {
        const saved = state[ index ];

        if ( ! saved || saved.name !== control.name ) {
            return;
        }

        if ( 'checkbox' === control.type || 'radio' === control.type ) {
            control.checked = saved.checked;
        } else if ( saved.selected ) {
            Array.from( control.options ).forEach( ( option ) => {
                option.selected = saved.selected.includes( option.value );
            } );
        } else {
            control.value = saved.value;
        }
    } );
}

/**
 * Name/value pairs a form post would send for a state.
 *
 * @param {Array} state Control state
 * @return {Array} [ name, value ] pairs
 */
function pairsOf( state ) {
    const pairs = [];

    state.forEach( ( control ) => {
        if ( ( 'checkbox' === control.type || 'radio' === control.type ) && ! control.checked ) {
            return;
        }

        if ( control.selected ) {
            control.selected.forEach( ( value ) => pairs.push( [ control.name, value ] ) );
            return;
        }

        if ( 'button' !== control.type && 'submit' !== control.type ) {
            pairs.push( [ control.name, control.value ] );
        }
    } );

    return pairs;
}

/**
 * What the slots add to a save: the `wpuf_settings` pairs of every slot that
 * was shown, and the top-level settings keys those slots own (a key whose
 * checkbox is unticked is owned but not posted, so the server removes it).
 *
 * @return {{ data: string, keys: string[] }} URL-encoded pairs and owned keys
 */
export function getLegacySettingsPayload() {
    const params = new URLSearchParams();
    const keys = new Set();

    registry.forEach( ( entry ) => {
        // A slot nobody edited sends nothing: an untouched save adds no data.
        if ( ! entry.dirty ) {
            return;
        }

        const state = entry.node && entry.node.isConnected ? readState( entry.node ) : entry.state;

        state.forEach( ( control ) => {
            const match = /^wpuf_settings\[([^\]]+)\]/.exec( control.name );

            if ( match ) {
                keys.add( match[ 1 ] );
            }
        } );

        pairsOf( state ).forEach( ( [ name, value ] ) => {
            if ( name.startsWith( 'wpuf_settings[' ) ) {
                params.append( name, value );
            }
        } );
    } );

    return { data: params.toString(), keys: Array.from( keys ) };
}

/**
 * The slots of the builder page (`wpuf_form_builder.legacy_slots`).
 *
 * @return {Object} { settings, tabs, unsupported }
 */
export function getLegacySlots() {
    const slots = ( window.wpuf_form_builder || {} ).legacy_slots || {};

    return {
        settings: slots.settings || {},
        tabs: slots.tabs || {},
        unsupported: !! slots.unsupported,
    };
}

/**
 * One slot. `id` must be stable across mounts (it keys the saved state).
 */
function LegacySlot( { id, html, className = '' } ) {
    const ref = useRef( null );

    useEffect( () => {
        const node = ref.current;

        if ( ! node || ! html ) {
            return;
        }

        const entry = registry.get( id );

        if ( entry ) {
            applyState( node, entry.state );
        }

        const dirty = !! ( entry && entry.dirty );
        registry.set( id, { node, state: readState( node ), dirty } );

        const remember = () => registry.set( id, { node, state: readState( node ), dirty: true } );
        node.addEventListener( 'input', remember );
        node.addEventListener( 'change', remember );

        return () => {
            node.removeEventListener( 'input', remember );
            node.removeEventListener( 'change', remember );
            const last = registry.get( id ) || {};
            registry.set( id, { node: null, state: readState( node ), dirty: !! last.dirty } );
        };
    }, [ id, html ] );

    if ( ! html ) {
        return null;
    }

    return (
        <div
            ref={ ref }
            className={ `wpuf-legacy-slot ${ className }`.trim() }
            data-wpuf-legacy-slot={ id }
            // Sanitized on the server (HookBridge::allowed_html), as develop printed it.
            dangerouslySetInnerHTML={ { __html: html } }
        />
    );
}

export default memo( LegacySlot );
