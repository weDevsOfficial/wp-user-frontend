/**
 * The current drop target of a builder drag (design.md D16), kept outside React
 * state so a drag re-renders only the list whose placeholder moves.
 *
 * Target: `{ container, index, blocked }` or null. `blocked` marks a palette
 * field over a list that refuses it (develop showed its refusal on drop).
 *
 * @since WPUF_SINCE
 */
import { createContext, useContext, useSyncExternalStore } from '@wordpress/element';

/**
 * @return {Object} `{ get, set, subscribe }`
 */
export function createDropTarget() {
    let target = null;
    const listeners = new Set();

    return {
        get: () => target,
        set( next ) {
            const same = ( ! next && ! target ) || ( next && target
                && next.container === target.container
                && next.index === target.index
                && next.blocked === target.blocked );

            if ( same ) {
                return;
            }

            target = next;
            listeners.forEach( ( listener ) => listener() );
        },
        subscribe( listener ) {
            listeners.add( listener );

            return () => listeners.delete( listener );
        },
    };
}

export const DropTargetContext = createContext( createDropTarget() );

/**
 * The placeholder of one list: `{ index, blocked }` while a drag targets it, else null.
 *
 * @param {string} container Container id (utils/dndTree.js).
 *
 * @return {Object|null} Placeholder.
 */
export function usePlaceholder( container ) {
    const store = useContext( DropTargetContext );
    // A primitive snapshot, so unrelated target changes do not re-render this list.
    const snapshot = useSyncExternalStore( store.subscribe, () => {
        const target = store.get();

        return target && target.container === container ? target.index + ( target.blocked ? ':x' : '' ) : '';
    } );

    if ( '' === snapshot ) {
        return null;
    }

    return { index: parseInt( snapshot, 10 ), blocked: String( snapshot ).endsWith( ':x' ) };
}

/**
 * Whether the current target is refused (for the drag preview).
 *
 * @return {string} 'none' (no target), 'blocked' or 'ok'.
 */
export function useTargetState() {
    const store = useContext( DropTargetContext );

    return useSyncExternalStore( store.subscribe, () => {
        const target = store.get();

        if ( ! target ) {
            return 'none';
        }

        return target.blocked ? 'blocked' : 'ok';
    } );
}
