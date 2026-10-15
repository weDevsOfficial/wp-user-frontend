/**
 * TemplatePicker: the "Add New" form template picker of the forms lists
 * (post + registration), full screen on the shared gray page.
 *
 * Replaces the PHP + jQuery modal (includes/Admin/template-parts/modal-v4.2.php):
 * same templates, links, search and categories, from
 * `formTemplates()` (Admin\Forms\Template_Picker). A category's
 * count is the number of cards it shows (Blank and AI Forms included; the PHP
 * modal left them out of the category count). Each card shows
 * the top of a screenshot of the created form; hovering or focusing a card
 * opens a preview that scrolls through the whole screenshot.
 *
 * @since WPUF_SINCE
 */
import { useCallback, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Loader2 } from 'lucide-react';
import { Button, ProBadge, TextInput, notify } from '@wpuf/components';
import { request, restPath } from '@wpuf/api';
import { inApp, openRoute } from '../../../app/client';
import { formTemplates } from '../boot';

const PREVIEW_WIDTH = 460;
const PREVIEW_GAP = 12;
const OPEN_DELAY = 300;
const CLOSE_DELAY = 150;
const SCROLL_STEP = 80;
// Auto scroll of the preview: pixels per second, and the waits at both ends.
const AUTO_SPEED = 110;
const AUTO_START_WAIT = 600;
const AUTO_END_WAIT = 1400;

const ACTION_CLASS = 'inline-flex h-10 items-center justify-center rounded-md border border-solid border-transparent bg-primary px-5 text-sm font-medium shadow-sm text-white no-underline cursor-pointer hover:bg-[#10b981] hover:text-white focus:text-white focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-white';

const PlusIcon = () => (
    <svg className="size-8 text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
);

const SparkleIcon = () => (
    <svg className="size-10" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="wpuf-template-ai" x1="18" y1="2" x2="6" y2="18" gradientUnits="userSpaceOnUse">
                <stop stopColor="#9333EA" />
                <stop offset="1" stopColor="#2563EB" />
            </linearGradient>
        </defs>
        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="url(#wpuf-template-ai)" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="url(#wpuf-template-ai)" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="url(#wpuf-template-ai)" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const CloseIcon = () => (
    <svg className="size-5" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
);

/**
 * Where the preview sits: beside the card, on the side with room.
 *
 * @param {DOMRect} rect Card rectangle.
 * @return {Object} Fixed position and max height.
 */
function previewPlace( rect ) {
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const maxHeight = Math.min( viewportHeight - 32, 720 );
    const fitsEnd = rect.right + PREVIEW_GAP + PREVIEW_WIDTH <= viewportWidth - 16;
    const left = fitsEnd ? rect.right + PREVIEW_GAP : Math.max( 16, rect.left - PREVIEW_GAP - PREVIEW_WIDTH );
    const top = Math.max( 16, Math.min( rect.top, viewportHeight - maxHeight - 16 ) );

    return { left, top, maxHeight };
}

/**
 * The hover / focus preview: the whole screenshot, scrolling by itself from
 * top to bottom and around again. The wheel, a drag or the arrow keys hand the
 * scroll to the user; reduced motion keeps it still.
 *
 * @param {Object}   props
 * @param {Object}   props.preview   `{ item, place }`.
 * @param {Object}   props.scrollRef Ref of the scroll area (arrow keys of the card).
 * @param {Object}   props.manualRef Ref flag: the user scrolls.
 * @param {Function} props.onEnter   Pointer on the preview.
 * @param {Function} props.onLeave   Pointer left the preview.
 */
function TemplatePreview( { preview, scrollRef, manualRef, onEnter, onLeave } ) {
    const [ shown, setShown ] = useState( false );
    const [ loaded, setLoaded ] = useState( false );

    // Fade in.
    useEffect( () => {
        const frame = window.requestAnimationFrame( () => setShown( true ) );

        return () => window.cancelAnimationFrame( frame );
    }, [] );

    useEffect( () => {
        const area = scrollRef.current;
        const still = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

        manualRef.current = false;

        if ( ! loaded || ! area || still ) {
            return undefined;
        }

        let frame = 0;
        let last = 0;
        let position = 0;
        let waitUntil = window.performance.now() + AUTO_START_WAIT;

        const tick = ( now ) => {
            if ( manualRef.current ) {
                return;
            }

            const end = area.scrollHeight - area.clientHeight;

            if ( end > 0 && now >= waitUntil ) {
                position += ( AUTO_SPEED * ( now - ( last || now ) ) ) / 1000;

                if ( position >= end ) {
                    // Bottom: wait, then start again from the top.
                    area.scrollTop = end;
                    position = 0;
                    waitUntil = now + AUTO_END_WAIT * 2;
                    window.setTimeout( () => {
                        if ( ! manualRef.current && scrollRef.current === area ) {
                            area.scrollTo( { top: 0, behavior: 'smooth' } );
                        }
                    }, AUTO_END_WAIT );
                } else {
                    area.scrollTop = position;
                }
            }

            last = now;
            frame = window.requestAnimationFrame( tick );
        };

        frame = window.requestAnimationFrame( tick );

        return () => window.cancelAnimationFrame( frame );
    }, [ loaded, scrollRef, manualRef ] );

    const takeOver = () => {
        manualRef.current = true;
    };

    return (
        // eslint-disable-next-line jsx-a11y/no-static-element-interactions -- keeps the hover preview open while the pointer is on it
        <div
            className={ `wpuf-template-preview fixed z-[100001] flex flex-col overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white shadow-xl transition duration-200 ease-out motion-reduce:transition-none ${ shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2' }` }
            style={ { left: preview.place.left, top: preview.place.top, width: PREVIEW_WIDTH, maxHeight: preview.place.maxHeight } }
            onMouseEnter={ onEnter }
            onMouseLeave={ onLeave }
            onWheel={ takeOver }
            onPointerDown={ takeOver }
            onTouchStart={ takeOver }
        >
            <div className="flex shrink-0 items-center justify-between gap-4 border-0 border-b border-solid border-gray-200 px-4 py-3">
                <span className="text-sm font-semibold leading-5 text-gray-800">{ preview.item.title }</span>
                <span className="shrink-0 text-xs leading-5 text-gray-500">{ __( 'Scroll to see the full form', 'wp-user-frontend' ) }</span>
            </div>
            <div ref={ scrollRef } className="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3">
                <img
                    src={ preview.item.image }
                    alt={ sprintf(
                        /* translators: %s: template name */
                        __( 'Preview of the %s form', 'wp-user-frontend' ),
                        preview.item.title
                    ) }
                    onLoad={ () => setLoaded( true ) }
                    className="block h-auto w-full max-w-none"
                />
            </div>
        </div>
    );
}

/**
 * One template card.
 *
 * @param {Object}   props
 * @param {Object}   props.item      Card data.
 * @param {Function} props.onPreview Open / close the preview ( item|null, element ).
 * @param {Function} props.onKeyDown Arrow keys scroll the preview.
 * @param {boolean}  props.previewed The preview of this card is open.
 * @param {Function} props.onCreate  Create from this card in the app ( item, event ).
 * @param {string}   props.creating  Key of the card being created ('' when none).
 */
function TemplateCard( { item, onPreview, onKeyDown, previewed, onCreate, creating } ) {
    const ref = useRef( null );
    const hasPreview = !! item.image;
    const hintId = `wpuf-template-hint-${ item.key }`;
    const dimmed = ! item.enabled && ! item.action;
    const busy = creating === item.key;

    let action = null;

    if ( item.onClick ) {
        action = <button type="button" className={ ACTION_CLASS } onClick={ item.onClick }>{ item.actionLabel }</button>;
    } else if ( item.url ) {
        action = (
            <a
                href={ item.url }
                className={ ACTION_CLASS }
                aria-describedby={ hasPreview ? hintId : undefined }
                onClick={ item.template ? ( event ) => onCreate( item, event ) : undefined }
                aria-disabled={ creating ? 'true' : undefined }
                { ...( item.is_pro ? { target: '_blank', rel: 'noopener noreferrer' } : {} ) }
            >
                { item.actionLabel }
            </a>
        );
    }

    return (
        <div
            ref={ ref }
            data-template={ item.key }
            className={ `wpuf-template-card group relative overflow-hidden rounded-[10px] border border-solid bg-white shadow-sm transition-colors focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/30 hover:border-primary ${ previewed ? 'border-primary' : 'border-gray-200' }` }
            onMouseEnter={ () => hasPreview && onPreview( item, ref.current, true ) }
            onMouseLeave={ () => hasPreview && onPreview( null ) }
            onFocus={ () => hasPreview && onPreview( item, ref.current ) }
            onBlur={ () => hasPreview && onPreview( null ) }
            onKeyDown={ hasPreview ? onKeyDown : undefined }
        >
            <div className="relative aspect-[5/5] overflow-hidden border-0 border-b border-solid border-gray-200 bg-white">
                { item.image && (
                    <img
                        src={ item.image }
                        alt=""
                        loading="lazy"
                        className={ `block size-full max-w-none bg-white object-cover object-top p-2 ${ dimmed || item.is_pro ? 'opacity-50' : '' }` }
                    />
                ) }
                { ! item.image && (
                    <div className="flex size-full flex-col items-center justify-center gap-3 bg-white">
                        { item.icon || <span className="px-3 text-center text-sm font-semibold text-gray-700">{ item.title }</span> }
                        { item.iconLabel && <span className="text-sm text-gray-600">{ item.iconLabel }</span> }
                    </div>
                ) }

                { item.is_pro && (
                    <span className="absolute end-2 top-2 z-20"><ProBadge utm="wpuf-form-templates" link={ false } /></span>
                ) }
                { dimmed && ! item.is_pro && (
                    <span className="absolute start-2 top-2 z-20 rounded-full border border-solid border-gray-200 bg-white px-2 py-0.5 text-xs font-medium text-gray-600">{ __( 'Not installed', 'wp-user-frontend' ) }</span>
                ) }

                { busy && (
                    <div className="wpuf-template-creating wpuf-fade-in absolute inset-0 z-30 flex flex-col items-center justify-center gap-3 bg-white/90 backdrop-blur-[2px] text-sm font-medium text-gray-700" role="status">
                        <span className="flex size-12 items-center justify-center rounded-full bg-primary/10">
                            <Loader2 className="size-6 animate-spin text-primary" aria-hidden="true" />
                        </span>
                        { __( 'Creating form…', 'wp-user-frontend' ) }
                        <span className="wpuf-progress-indeterminate h-1 w-24 rounded-full bg-primary/15" aria-hidden="true" />
                    </div>
                ) }
                <div className={ `absolute inset-0 z-10 flex flex-col items-center justify-center gap-2 bg-white/80 p-4 text-center opacity-0 backdrop-blur-[1px] transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 ${ creating ? 'pointer-events-none' : '' } ${ busy ? 'invisible' : '' }` }>
                    { action }
                    { ! action && (
                        <>
                            <span className="text-sm font-semibold text-gray-900">{ __( 'This integration is not installed.', 'wp-user-frontend' ) }</span>
                            { item.description && <span className="text-xs text-gray-600">{ item.description }</span> }
                        </>
                    ) }
                </div>
            </div>

            <div className="flex min-h-12 items-center px-4 py-2.5">
                <span className="text-sm font-semibold leading-5 text-gray-900">{ item.title }</span>
            </div>

            { hasPreview && (
                <span id={ hintId } className="screen-reader-text">{ __( 'Use the up and down arrow keys to scroll the preview.', 'wp-user-frontend' ) }</span>
            ) }
        </div>
    );
}

/**
 * @param {Object}        props
 * @param {boolean}       props.open    Shown.
 * @param {Function}      props.onClose Close the picker.
 * @param {Object}        props.data    `formTemplates()`.
 * @param {Function|null} props.onAI    Start the AI builder (null hides the card).
 */
export default function TemplatePicker( { open, onClose, data, onAI } ) {
    const [ search, setSearch ] = useState( '' );
    const [ category, setCategory ] = useState( 'all' );
    const [ preview, setPreview ] = useState( null );
    const dialogRef = useRef( null );
    const previewRef = useRef( null );
    const timer = useRef( null );
    const manualScroll = useRef( false );
    const returnFocus = useRef( null );
    const [ creating, setCreating ] = useState( '' );

    // Create from a template card through the admin forms REST route and open
    // the builder in the app; the card's link (develop's page request) stays
    // for a new tab and as the fallback.
    const createFromTemplate = useCallback( async ( item, event ) => {
        if ( ! inApp() || event.metaKey || event.ctrlKey || event.shiftKey || 0 !== event.button ) {
            return;
        }

        event.preventDefault();

        if ( creating ) {
            return;
        }

        const profile = 'profile' === data.form_type;

        setCreating( item.key );

        try {
            const created = await request( restPath( 'wpuf/v1', '/admin/forms' ), {
                method: 'POST',
                data: { type: profile ? 'wpuf_profile' : 'wpuf_forms', template: item.template },
            } );
            const id = created?.data?.id;

            if ( ! id ) {
                throw new Error( 'no id' );
            }

            openRoute( `/${ profile ? 'registration-forms' : 'post-forms' }/${ id }/edit`, item.url );
        } catch ( error ) {
            setCreating( '' );
            notify( error?.message || __( 'The form could not be created.', 'wp-user-frontend' ), 'error' );
        }
    }, [ creating, data.form_type ] );

    const items = useMemo( () => {
        const list = [
            {
                key: 'blank',
                title: __( 'Blank Form', 'wp-user-frontend' ),
                category: data.default_category,
                enabled: true,
                url: data.blank_form_url,
                actionLabel: __( 'Create Form', 'wp-user-frontend' ),
                action: true,
                icon: <PlusIcon />,
            },
        ];

        if ( onAI ) {
            list.push( {
                key: 'ai_form',
                title: __( 'AI Forms', 'wp-user-frontend' ),
                category: data.default_category,
                enabled: true,
                onClick: onAI,
                actionLabel: __( 'Create with AI', 'wp-user-frontend' ),
                action: true,
                icon: <SparkleIcon />,
            } );
        }

        ( data.templates || [] ).forEach( ( template ) => {
            let actionLabel = '';

            if ( template.is_pro ) {
                actionLabel = __( 'Upgrade to PRO', 'wp-user-frontend' );
            } else if ( template.enabled ) {
                actionLabel = __( 'Create Form', 'wp-user-frontend' );
            }

            list.push( {
                ...template,
                actionLabel,
                action: !! ( template.url && actionLabel ),
                template: template.enabled && ! template.is_pro ? template.key : '',
            } );
        } );

        return list;
    }, [ data, onAI ] );

    const counts = useMemo( () => {
        const result = { all: items.length };

        items.forEach( ( item ) => {
            result[ item.category ] = ( result[ item.category ] || 0 ) + 1;
        } );

        return result;
    }, [ items ] );

    const term = search.trim().toLowerCase();
    const visible = items.filter( ( item ) => ( term
        ? item.title.toLowerCase().includes( term )
        : 'all' === category || item.category === category ) );

    // Hover opens after a short wait and closes late enough to reach the preview.
    const showPreview = useCallback( ( item, element, delayed ) => {
        window.clearTimeout( timer.current );

        if ( ! item ) {
            timer.current = window.setTimeout( () => setPreview( null ), CLOSE_DELAY );
            return;
        }

        const openNow = () => setPreview( { item, place: previewPlace( element.getBoundingClientRect() ) } );

        if ( delayed ) {
            timer.current = window.setTimeout( openNow, OPEN_DELAY );
        } else {
            openNow();
        }
    }, [] );

    const scrollPreview = useCallback( ( event ) => {
        const steps = { ArrowDown: SCROLL_STEP, ArrowUp: -SCROLL_STEP, PageDown: SCROLL_STEP * 4, PageUp: -SCROLL_STEP * 4 };

        if ( steps[ event.key ] && previewRef.current ) {
            event.preventDefault();
            manualScroll.current = true;
            previewRef.current.scrollTop += steps[ event.key ];
        }
    }, [] );

    const close = useCallback( () => {
        window.clearTimeout( timer.current );
        setPreview( null );
        onClose();
    }, [ onClose ] );

    // Open: lock the page scroll, focus the search; close: focus goes back.
    useEffect( () => {
        if ( ! open ) {
            return undefined;
        }

        // Each opening starts with an empty search, as develop's modal did (a
        // search left from the last visit showed "No templates found").
        setSearch( '' );

        returnFocus.current = document.activeElement;
        const overflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        dialogRef.current?.querySelector( 'input' )?.focus();

        return () => {
            document.body.style.overflow = overflow;
            window.clearTimeout( timer.current );
            returnFocus.current?.focus?.();
        };
    }, [ open ] );

    if ( ! open ) {
        return null;
    }

    // Escape closes; Tab stays inside the picker.
    const onKeyDown = ( event ) => {
        if ( 'Escape' === event.key ) {
            event.stopPropagation();
            close();
            return;
        }

        if ( 'Tab' !== event.key ) {
            return;
        }

        const focusable = dialogRef.current.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled])' );
        const first = focusable[ 0 ];
        const last = focusable[ focusable.length - 1 ];

        if ( event.shiftKey && document.activeElement === first ) {
            event.preventDefault();
            last.focus();
        } else if ( ! event.shiftKey && document.activeElement === last ) {
            event.preventDefault();
            first.focus();
        }
    };

    const categories = [ { slug: 'all', label: __( 'All Templates', 'wp-user-frontend' ) }, ...( data.categories || [] ) ];

    return (
        // eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions -- Escape and the Tab loop of the dialog
        <div
            ref={ dialogRef }
            role="dialog"
            aria-modal="true"
            aria-labelledby="wpuf-template-picker-title"
            aria-describedby="wpuf-template-picker-description"
            className="wpuf-template-picker fixed inset-0 z-[100000] flex flex-col bg-[#f0f0f1]"
            onKeyDown={ onKeyDown }
        >
            <div className="shrink-0 border-0 border-b border-solid border-gray-200 bg-white">
                <div className="mx-auto flex max-w-[1400px] items-center justify-between gap-6 px-8 py-4">
                    <div>
                        <h2 id="wpuf-template-picker-title" className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ data.title }</h2>
                        <p id="wpuf-template-picker-description" className="m-0 mt-1 p-0 text-sm text-gray-500">
                            { __( 'Select from a pre-defined template to get started quickly, or start from a blank form to build your own from scratch', 'wp-user-frontend' ) }
                        </p>
                    </div>
                    <Button variant="icon" onClick={ close } aria-label={ __( 'Close', 'wp-user-frontend' ) } className="wpuf-template-picker-close shrink-0 rounded-full border border-solid border-gray-200 bg-white">
                        <CloseIcon />
                    </Button>
                </div>
            </div>

            <div className="min-h-0 flex-1 overflow-y-auto" onScroll={ () => preview && setPreview( null ) }>
                <div className="mx-auto flex max-w-[1400px] items-start gap-6 px-8 py-6">
                    <div className="sticky top-6 w-60 shrink-0 rounded-[10px] border border-solid border-gray-200 bg-white p-2 shadow-sm">
                        <div className="relative mb-2">
                            <svg className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                            </svg>
                            <TextInput
                                value={ search }
                                onChange={ ( value ) => {
                                    setSearch( value );
                                    setCategory( 'all' );
                                } }
                                placeholder={ __( 'Search Templates', 'wp-user-frontend' ) }
                                aria-label={ __( 'Search Templates', 'wp-user-frontend' ) }
                                className="h-9 w-full ps-9"
                            />
                        </div>

                        <ul className="m-0 flex list-none flex-col gap-0.5 p-0" aria-label={ __( 'Template categories', 'wp-user-frontend' ) }>
                            { categories.map( ( item ) => {
                                const active = ! term && category === item.slug;

                                return (
                                    <li key={ item.slug } className="m-0">
                                        <button
                                            type="button"
                                            aria-pressed={ active }
                                            data-category={ item.slug }
                                            className={ `flex w-full cursor-pointer items-center justify-between rounded-md border-0 px-3 py-2.5 text-start text-sm font-medium focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-primary/30 ${ active ? 'bg-primary text-white' : 'bg-transparent text-gray-700 hover:bg-gray-100 hover:text-gray-900' }` }
                                            onClick={ () => {
                                                setSearch( '' );
                                                setCategory( item.slug );
                                            } }
                                        >
                                            <span>{ item.label }</span>
                                            <span className={ `rounded-full px-2 py-0.5 text-xs ${ active ? 'bg-white/20 font-semibold text-white' : 'text-gray-500' }` }>
                                                { counts[ item.slug ] || 0 }
                                            </span>
                                        </button>
                                    </li>
                                );
                            } ) }
                        </ul>
                    </div>

                    <div className="min-w-0 flex-1">
                        { visible.length > 0 && (
                            <div className="grid grid-cols-[repeat(auto-fill,minmax(200px,1fr))] gap-4" data-template-grid="">
                                { visible.map( ( item ) => (
                                    <TemplateCard
                                        key={ item.key }
                                        item={ item }
                                        onPreview={ showPreview }
                                        onKeyDown={ scrollPreview }
                                        previewed={ preview?.item.key === item.key }
                                        onCreate={ createFromTemplate }
                                        creating={ creating }
                                    />
                                ) ) }
                            </div>
                        ) }
                        { 0 === visible.length && (
                            <p className="m-0 rounded-[10px] border border-solid border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-500" role="status">
                                { sprintf(
                                    /* translators: %s: search term */
                                    __( 'No templates found for "%s".', 'wp-user-frontend' ),
                                    search.trim()
                                ) }
                            </p>
                        ) }
                    </div>
                </div>
            </div>

            { preview && (
                <TemplatePreview
                    key={ preview.item.key }
                    preview={ preview }
                    scrollRef={ previewRef }
                    manualRef={ manualScroll }
                    onEnter={ () => window.clearTimeout( timer.current ) }
                    onLeave={ () => showPreview( null ) }
                />
            ) }
        </div>
    );
}
