/**
 * One drag-and-drop system for the form builder (design.md D16): a single
 * `@dnd-kit` DndContext around the palette and the canvas.
 *
 * - Sources: palette buttons and canvas fields (move handle) are dnd-kit
 *   draggables (mouse: 5px to start, so a click still adds; touch: hold
 *   200ms; keyboard: Space on a focused palette button or move handle).
 * - Targets: every canvas list (stage, column cells, repeat lists) is a
 *   container (DropList). The pointer is hit-tested against them, the
 *   deepest one under it wins; a canvas field that a list refuses falls back
 *   to the enclosing list (e.g. a column field dragged over a cell lands
 *   next to that column field). Palette fields keep develop's refusal (red
 *   placeholder, "Oops..." on drop).
 * - Feedback: develop's dashed placeholder at the insertion point, the
 *   source dimmed, a drag preview that turns red over a refusing list,
 *   auto-scroll at the canvas edges, screen reader messages.
 * - Keyboard: arrows step through every allowed position in page order,
 *   Space / Enter drops, Escape cancels.
 *
 * @since WPUF_SINCE
 */
import { useCallback, useMemo, useRef, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    MouseSensor,
    TouchSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import { STORE_NAME } from '../../store';
import { canDrop, isNoopMove, locateField } from '../../utils/dndTree';
import { DropTargetContext, createDropTarget, useTargetState } from './dropTarget';
import usePaletteDrop from './usePaletteDrop';

// Auto-scroll: distance from the canvas edge (px) and top speed (px per frame).
const EDGE = 72;
const MAX_SPEED = 18;

const containerOf = ( node ) => ( node && node.closest ? node.closest( '[data-dnd-container]' ) : null );

// A list's rows: its direct children, or those of its `data-dnd-rows` child
// (the stage: the whole stage area takes drops, the rows are in its list).
const rowsOf = ( list ) => {
    const host = list.querySelector( ':scope > [data-dnd-rows]' ) || list;

    return Array.from( host.children ).filter( ( row ) => row.hasAttribute( 'data-dnd-item' ) );
};

/**
 * Insertion index in a list for a vertical position: before the first visible
 * row whose middle is below it, else the end.
 *
 * @param {HTMLElement} list List element.
 * @param {number}      y    Client Y.
 *
 * @return {number} Index.
 */
function indexAt( list, y ) {
    const rows = rowsOf( list ).filter( ( row ) => null !== row.offsetParent );

    for ( const row of rows ) {
        const box = row.getBoundingClientRect();

        if ( y < box.top + ( box.height / 2 ) ) {
            return parseInt( row.dataset.index, 10 );
        }
    }

    return parseInt( list.dataset.dndLength, 10 ) || 0;
}

/**
 * Drop target under a point.
 *
 * @param {Object} item Dragged item.
 * @param {number} x    Client X.
 * @param {number} y    Client Y.
 *
 * @return {Object|null} `{ container, index, blocked }`
 */
function hitTest( item, x, y ) {
    let list = null;

    for ( const node of document.elementsFromPoint( x, y ) ) {
        list = containerOf( node );

        if ( list ) {
            break;
        }
    }

    while ( list ) {
        const container = list.dataset.dndContainer;

        if ( canDrop( item, container ) ) {
            return { container, index: indexAt( list, y ), blocked: false };
        }

        if ( 'palette' === item.kind ) {
            return { container, index: indexAt( list, y ), blocked: true };
        }

        list = containerOf( list.parentElement );
    }

    return null;
}

/**
 * Every allowed position, in page order, for keyboard dragging.
 *
 * @param {Object} item Dragged item.
 *
 * @return {Array} `[ { container, index } ]`
 */
function keyboardSlots( item ) {
    const slots = [];

    document.querySelectorAll( '[data-dnd-container]' ).forEach( ( list ) => {
        const container = list.dataset.dndContainer;

        if ( ! canDrop( item, container ) || ! list.offsetParent ) {
            return;
        }

        const length = parseInt( list.dataset.dndLength, 10 ) || 0;

        for ( let index = 0; index <= length; index++ ) {
            slots.push( { container, index } );
        }
    } );

    return slots;
}

/**
 * Where a keyboard slot is on screen (for the preview and scrolling).
 *
 * @param {Object} slot `{ container, index }`
 *
 * @return {Object|null} `{ x, y, node }`
 */
function slotPoint( slot ) {
    const list = document.querySelector( `[data-dnd-container="${ slot.container }"]` );

    if ( ! list ) {
        return null;
    }

    const rows = rowsOf( list );
    const row = rows.find( ( node ) => parseInt( node.dataset.index, 10 ) === slot.index );
    const node = row || rows[ rows.length - 1 ] || list;
    const box = node.getBoundingClientRect();

    return { x: box.left + 16, y: row || ! rows.length ? box.top + 4 : box.bottom - 4, node };
}

/**
 * The scrollable canvas column (the stage's nearest scrolling ancestor).
 *
 * @return {HTMLElement|null} Scroller.
 */
function canvasScroller() {
    let node = document.querySelector( '[data-dnd-container="top"]' );

    while ( node && node !== document.body ) {
        const overflow = getComputedStyle( node ).overflowY;

        if ( ( 'auto' === overflow || 'scroll' === overflow ) && node.scrollHeight > node.clientHeight ) {
            return node;
        }

        node = node.parentElement;
    }

    return null;
}

const labelOf = ( item ) => item.label || item.template || '';

/**
 * Drag preview: a palette button or a compact field card (develop's helper
 * clone), red while the target refuses the field.
 *
 * @param {Object} props
 * @param {Object} props.item Dragged item.
 */
function DragPreview( { item } ) {
    const state = useTargetState();
    const tone = 'blocked' === state ? 'border-red-400 cursor-not-allowed' : 'border-primary cursor-grabbing';

    if ( 'palette' === item.kind ) {
        return (
            <div className={ `wpuf-field-button flex items-center w-[220px] rounded-lg border border-solid bg-white shadow-lg px-3 py-4 ${ tone }` }>
                <p className="text-[13px] font-normal text-gray-700 m-0">{ labelOf( item ) }</p>
            </div>
        );
    }

    return (
        <div className={ `max-w-[360px] rounded-lg border border-solid bg-white shadow-lg px-4 py-3 ${ tone }` }>
            <p className="m-0 text-sm font-medium text-gray-700 truncate">{ labelOf( item ) }</p>
        </div>
    );
}

export default function BuilderDnd( { children } ) {
    const formFields = useSelect( ( select ) => select( STORE_NAME ).getFormFields(), [] );
    const { moveFieldTo } = useDispatch( STORE_NAME );
    const dropPalette = usePaletteDrop();

    const target = useMemo( createDropTarget, [] );
    const [ active, setActive ] = useState( null );
    const [ message, setMessage ] = useState( '' );

    const fieldsRef = useRef( formFields );
    fieldsRef.current = formFields;

    const drag = useRef( {
        item: null,
        keyboard: false,
        pointer: null,
        frame: 0,
        scroller: null,
        slots: [],
        slot: -1,
        onMove: null,
    } );

    const runHitTest = useCallback( () => {
        const state = drag.current;

        state.frame = 0;

        if ( ! state.item || ! state.pointer ) {
            return;
        }

        target.set( hitTest( state.item, state.pointer.x, state.pointer.y ) );
    }, [ target ] );

    // Scroll the canvas while the pointer rests near its top or bottom edge.
    const autoScroll = useCallback( () => {
        const state = drag.current;

        if ( ! state.item || state.keyboard ) {
            return;
        }

        const scroller = state.scroller;
        const point = state.pointer;

        if ( scroller && point ) {
            const box = scroller.getBoundingClientRect();
            const inside = point.x >= box.left && point.x <= box.right;
            let speed = 0;

            if ( inside && point.y < box.top + EDGE ) {
                speed = -MAX_SPEED * ( 1 - Math.max( 0, point.y - box.top ) / EDGE );
            } else if ( inside && point.y > box.bottom - EDGE ) {
                speed = MAX_SPEED * ( 1 - Math.max( 0, box.bottom - point.y ) / EDGE );
            }

            if ( speed ) {
                scroller.scrollTop += Math.round( speed );
                runHitTest();
            }
        }

        state.scrollFrame = window.requestAnimationFrame( autoScroll );
    }, [ runHitTest ] );

    const stop = useCallback( () => {
        const state = drag.current;

        if ( state.onMove ) {
            window.removeEventListener( 'pointermove', state.onMove );
            window.removeEventListener( 'touchmove', state.onMove );
            window.removeEventListener( 'scroll', state.onScroll, true );
        }

        window.cancelAnimationFrame( state.frame );
        window.cancelAnimationFrame( state.scrollFrame );
        document.body.classList.remove( 'wpuf-builder-dragging' );

        drag.current = { item: null, keyboard: false, pointer: null, frame: 0, scroller: null, slots: [], slot: -1, onMove: null };
        target.set( null );
        setActive( null );
    }, [ target ] );

    // Keyboard: arrows go to the previous / next allowed position.
    const coordinateGetter = useCallback( ( event, { currentCoordinates } ) => {
        const state = drag.current;
        const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[ event.code ];

        if ( ! step || ! state.slots.length ) {
            return undefined;
        }

        event.preventDefault();
        state.slot = Math.max( 0, Math.min( state.slots.length - 1, state.slot + step ) );

        const slot = state.slots[ state.slot ];
        const list = slot.container;

        target.set( { container: list, index: slot.index, blocked: false } );

        const length = state.slots.filter( ( item ) => item.container === list ).length;
        /* translators: 1: position, 2: number of positions */
        setMessage( sprintf( __( 'Position %1$d of %2$d.', 'wp-user-frontend' ), slot.index + 1, length ) );

        const point = slotPoint( slot );

        if ( ! point ) {
            return currentCoordinates;
        }

        point.node.scrollIntoView( { block: 'nearest' } );

        const moved = slotPoint( slot ) || point;

        return { x: moved.x, y: moved.y };
    }, [ target ] );

    const sensors = useSensors(
        useSensor( MouseSensor, { activationConstraint: { distance: 5 } } ),
        useSensor( TouchSensor, { activationConstraint: { delay: 200, tolerance: 6 } } ),
        useSensor( KeyboardSensor, {
            // Enter on a palette button keeps adding the field at the end (click).
            keyboardCodes: { start: [ 'Space' ], cancel: [ 'Escape' ], end: [ 'Space', 'Enter' ] },
            coordinateGetter,
        } )
    );

    const handleDragStart = useCallback( ( { active: source, activatorEvent } ) => {
        const data = source.data.current || {};
        const item = { ...data };

        if ( 'field' === item.kind ) {
            const place = locateField( fieldsRef.current, item.fieldId );

            if ( ! place ) {
                return;
            }

            item.container = place.container;
            item.index = place.index;
        }

        const keyboard = typeof window.KeyboardEvent !== 'undefined' && activatorEvent instanceof window.KeyboardEvent;
        const state = drag.current;

        state.item = item;
        state.keyboard = keyboard;
        state.scroller = canvasScroller();
        document.body.classList.add( 'wpuf-builder-dragging' );
        setActive( item );

        if ( keyboard ) {
            state.slots = keyboardSlots( item );

            const current = 'field' === item.kind
                ? state.slots.findIndex( ( slot ) => slot.container === item.container && slot.index === item.index )
                : -1;

            state.slot = current;

            if ( -1 !== current ) {
                target.set( { container: item.container, index: item.index, blocked: false } );
            }
        } else {
            const touch = activatorEvent && activatorEvent.touches && activatorEvent.touches[ 0 ];
            const origin = touch || activatorEvent;

            state.pointer = origin ? { x: origin.clientX, y: origin.clientY } : null;
            // Touch drags: the browser stops pointer events once the drag owns
            // the gesture, so touch moves are read too.
            state.onMove = ( event ) => {
                const point = event.touches ? event.touches[ 0 ] : event;

                if ( ! point ) {
                    return;
                }

                state.pointer = { x: point.clientX, y: point.clientY };

                if ( ! state.frame ) {
                    state.frame = window.requestAnimationFrame( runHitTest );
                }
            };

            window.addEventListener( 'pointermove', state.onMove );
            window.addEventListener( 'touchmove', state.onMove, { passive: true } );
            // Wheel or auto-scroll moves the lists under a resting pointer.
            state.onScroll = () => {
                if ( ! state.frame ) {
                    state.frame = window.requestAnimationFrame( runHitTest );
                }
            };
            window.addEventListener( 'scroll', state.onScroll, { capture: true, passive: true } );
            state.scrollFrame = window.requestAnimationFrame( autoScroll );
        }

        /* translators: %s: field name */
        setMessage( sprintf( __( 'Picked up %s. Use the arrow keys to choose a position, Space to drop, Escape to cancel.', 'wp-user-frontend' ), labelOf( item ) ) );
    }, [ target, runHitTest, autoScroll ] );

    const handleDragEnd = useCallback( () => {
        const item = drag.current.item;
        const place = target.get();

        stop();

        if ( ! item || ! place ) {
            setMessage( __( 'Moving cancelled.', 'wp-user-frontend' ) );
            return;
        }

        if ( 'palette' === item.kind ) {
            dropPalette( item.template, place );
        } else if ( ! place.blocked ) {
            const from = { container: item.container, index: item.index };
            const to = { container: place.container, index: place.index };

            if ( ! isNoopMove( from, to ) ) {
                moveFieldTo( from, to );
            }
        }

        /* translators: %s: field name */
        setMessage( sprintf( __( '%s dropped.', 'wp-user-frontend' ), labelOf( item ) ) );
    }, [ target, stop, dropPalette, moveFieldTo ] );

    const handleDragCancel = useCallback( () => {
        stop();
        setMessage( __( 'Moving cancelled.', 'wp-user-frontend' ) );
    }, [ stop ] );

    // dnd-kit's own messages would name internal ids; ours live in the region below.
    const accessibility = useMemo( () => ( {
        announcements: {
            onDragStart: () => undefined,
            onDragMove: () => undefined,
            onDragOver: () => undefined,
            onDragEnd: () => undefined,
            onDragCancel: () => undefined,
        },
        screenReaderInstructions: {
            draggable: __( 'To pick up a field, press Space. Use the arrow keys to choose a position, Space or Enter to drop, Escape to cancel.', 'wp-user-frontend' ),
        },
    } ), [] );

    return (
        <DropTargetContext.Provider value={ target }>
            <DndContext
                sensors={ sensors }
                autoScroll={ false }
                accessibility={ accessibility }
                onDragStart={ handleDragStart }
                onDragEnd={ handleDragEnd }
                onDragCancel={ handleDragCancel }
            >
                { children }
                <DragOverlay dropAnimation={ null } style={ { pointerEvents: 'none' } } zIndex={ 100000 }>
                    { active ? <DragPreview item={ active } /> : null }
                </DragOverlay>
            </DndContext>
            <div className="sr-only" role="status" aria-live="assertive">{ message }</div>
        </DropTargetContext.Provider>
    );
}
