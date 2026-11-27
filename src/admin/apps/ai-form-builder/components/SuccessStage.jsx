/**
 * Stage 3: chat on the left, live form preview on the right (resizable on
 * large screens), Regenerate and Edit with Builder (develop's
 * FormSuccessStage.vue).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Button, ConfirmDialog } from '@wpuf/components';
import { cn } from '@wedevs/plugin-ui';

import { assetUrl, config } from '../api';
import { normalizeRequired } from '../lib/fields';
import { findProFields, proFieldItems } from '../lib/proFields';
import useChatSession from '../useChatSession';
import ChatMessage from './ChatMessage';
import PreviewField from './PreviewField';
import { LogoBadge, PencilIcon, RegenerateIcon, SendIcon } from './icons';

const MIN_PANEL = 20;
const MAX_PANEL = 80;
const LARGE = 1024;
const PRICING_URL = 'https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&utm_medium=popup';

/**
 * Whether the viewport is at least 1024px wide (side-by-side panes).
 *
 * @return {boolean} Large.
 */
function useLargeScreen() {
    const [ large, setLarge ] = useState( () => window.innerWidth >= LARGE );

    useEffect( () => {
        const check = () => setLarge( window.innerWidth >= LARGE );

        window.addEventListener( 'resize', check );

        return () => window.removeEventListener( 'resize', check );
    }, [] );

    return large;
}

/**
 * Regenerate confirmation (the standard admin dialog).
 *
 * @param {Object}   props
 * @param {boolean}  props.open      Open.
 * @param {Function} props.onConfirm Leave & regenerate.
 * @param {Function} props.onCancel  Stay.
 */
function RegenerateDialog( { open, onConfirm, onCancel } ) {
    return (
        <ConfirmDialog
            open={ open }
            title={ __( 'Are you sure you want to leave and regenerate the form?', 'wp-user-frontend' ) }
            message={ __( "If you decide to leave and regenerate the form, please be aware that you will lost the information you've currently generated.", 'wp-user-frontend' ) }
            icon="warning"
            // Develop's order: "Leave & Regenerate" (outline) then "Cancel" (green, the safe default).
            cancelText={ __( 'Leave & Regenerate', 'wp-user-frontend' ) }
            confirmText={ __( 'Cancel', 'wp-user-frontend' ) }
            tone="primary"
            className="wpuf-ai-regenerate-dialog"
            onCancel={ onConfirm }
            onConfirm={ onCancel }
            onDismiss={ onCancel }
        />
    );
}

/**
 * "Pro feature detected" before Edit with Builder on a free site.
 *
 * @param {Object}   props
 * @param {Array}    props.items      Pro field items { key, label, icon }.
 * @param {Function} props.onContinue Continue without Pro.
 * @param {Function} props.onClose    Close.
 */
function ProFieldsDialog( { items, onContinue, onClose } ) {
    return (
        <ConfirmDialog
            open={ items.length > 0 }
            title={ __( 'Pro feature detected', 'wp-user-frontend' ) }
            message={ __( 'Your form includes fields that require WPUF Pro version:', 'wp-user-frontend' ) }
            icon="pro"
            confirmText={ __( 'Upgrade to Pro', 'wp-user-frontend' ) }
            cancelText={ __( 'Continue without Pro', 'wp-user-frontend' ) }
            tone="primary"
            className="wpuf-ai-pro-fields-dialog"
            onConfirm={ () => {
                window.open( PRICING_URL, '_blank' );
                onClose();
            } }
            onCancel={ onContinue }
            onDismiss={ onClose }
        >
            <div className="flex w-full flex-col gap-2">
                { items.map( ( item ) => (
                    <div key={ item.key } className="wpuf-pro-field-item flex h-11 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700">
                        { item.icon && <img src={ item.icon } alt="" className="size-5 shrink-0" onError={ ( event ) => ( event.currentTarget.style.display = 'none' ) } /> }
                        <span>{ item.label }</span>
                    </div>
                ) ) }
            </div>
        </ConfirmDialog>
    );
}

/**
 * @param {Object}   props
 * @param {string}   props.title           Form title.
 * @param {Array}    props.initialFields   Generated fields.
 * @param {Array}    props.initialMessages First chat messages.
 * @param {boolean}  props.saving          The form is being created.
 * @param {Function} props.onFormUpdated   Accepted change.
 * @param {Function} props.onTitle         Accepted rename.
 * @param {Function} props.onRegenerate    Back to the input stage.
 * @param {Function} props.onEditInBuilder ( fields ) => create the form and open the builder.
 */
export default function SuccessStage( { title, initialFields, initialMessages, saving, onFormUpdated, onTitle, onRegenerate, onEditInBuilder } ) {
    const chat = useChatSession( { title, initialFields, initialMessages, onFormUpdated, onTitle } );
    const [ input, setInput ] = useState( '' );
    const [ chatWidth, setChatWidth ] = useState( 30 );
    const [ resizing, setResizing ] = useState( false );
    const [ regenerateOpen, setRegenerateOpen ] = useState( false );
    const [ proItems, setProItems ] = useState( [] );
    const large = useLargeScreen();
    const container = useRef( null );
    const scroller = useRef( null );
    const locked = chat.updating || chat.hasPendingButtons;

    useEffect( () => {
        if ( scroller.current ) {
            scroller.current.scrollTop = scroller.current.scrollHeight;
        }
    }, [ chat.messages ] );

    const send = () => {
        if ( ! input.trim() || locked ) {
            return;
        }

        const text = input;

        setInput( '' );
        chat.send( text );
    };

    const editInBuilder = () => {
        if ( ! config().isProActive ) {
            const pro = findProFields( chat.fields );

            if ( pro.length ) {
                setProItems( proFieldItems( pro, assetUrl() ) );
                return;
            }
        }

        onEditInBuilder( normalizeRequired( chat.fields ) );
    };

    const startResize = () => {
        if ( ! large || ! container.current ) {
            return;
        }

        const rect = container.current.getBoundingClientRect();
        const move = ( event ) => {
            const percent = ( ( event.clientX - rect.left ) / rect.width ) * 100;

            setChatWidth( Math.max( MIN_PANEL, Math.min( MAX_PANEL, percent ) ) );
        };
        const stop = () => {
            setResizing( false );
            document.removeEventListener( 'mousemove', move );
            document.removeEventListener( 'mouseup', stop );
            document.body.style.userSelect = '';
            document.body.style.cursor = '';
        };

        setResizing( true );
        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'col-resize';
        document.addEventListener( 'mousemove', move );
        document.addEventListener( 'mouseup', stop );
    };

    let placeholder = __( 'Type your message here...', 'wp-user-frontend' );

    if ( chat.hasPendingButtons ) {
        placeholder = __( 'Please accept or reject the changes above', 'wp-user-frontend' );
    } else if ( chat.updating ) {
        placeholder = __( 'Please wait while form is being generated...', 'wp-user-frontend' );
    }

    return (
        <div className="wpuf-ai-form-wrapper relative min-h-screen w-full bg-[#f0f0f1] pb-20 font-sans md:pb-16 lg:pb-12">
            <div className="flex min-h-[calc(100vh-5rem)] flex-col">
                <div className="flex h-full flex-col rounded-lg">
                    <div className="flex items-center justify-between px-6 pb-3 pt-6">
                        <div className="flex items-center gap-3">
                            <LogoBadge />
                            <div>
                                <h1 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ __( 'AI Form Builder', 'wp-user-frontend' ) }</h1>
                                <p className="m-0 mt-0.5 text-sm text-gray-500">{ __( 'Generate forms instantly with AI assistance', 'wp-user-frontend' ) }</p>
                            </div>
                        </div>
                        <div className="flex gap-3">
                            <Button variant="secondary" size="lg" onClick={ () => setRegenerateOpen( true ) } className="wpuf-btn-regenerate">
                                { __( 'Regenerate', 'wp-user-frontend' ) }
                                <RegenerateIcon />
                            </Button>
                            <Button size="lg" onClick={ editInBuilder } busy={ saving } className="wpuf-btn-edit-builder">
                                { __( 'Edit with Builder', 'wp-user-frontend' ) }
                                { ! saving && <PencilIcon /> }
                            </Button>
                        </div>
                    </div>

                    <div ref={ container } className="wpuf-resizable-container relative flex flex-col gap-5 p-2 sm:p-5 lg:flex-row lg:gap-0">
                        <div
                            className="wpuf-chat-box flex h-[calc(100vh-14rem)] min-w-0 shrink-0 flex-col overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-gray-50 px-5 pt-5 shadow-sm sm:h-[calc(100vh-10rem)] lg:rounded-r-none"
                            style={ { width: large ? `${ chatWidth }%` : '100%' } }
                        >
                            <div ref={ scroller } className="wpuf-chat-scrollable max-h-[calc(100vh-300px)] flex-1 overflow-y-auto" aria-live="polite">
                                <div className="flex flex-col gap-4">
                                    { chat.messages.map( ( message, index ) => (
                                        <ChatMessage
                                            key={ index }
                                            message={ message }
                                            statusVisible={ chat.isStatusVisible( index ) }
                                            onAccept={ chat.accept }
                                            onReject={ chat.reject }
                                            onRestore={ () => chat.restore( index ) }
                                        />
                                    ) ) }
                                </div>
                            </div>
                            <div className="shrink-0 pb-3 pt-4">
                                <div className="relative">
                                    <textarea
                                        value={ input }
                                        onChange={ ( event ) => setInput( event.target.value ) }
                                        onKeyDown={ ( event ) => {
                                            if ( 'Enter' === event.key && ! event.shiftKey ) {
                                                event.preventDefault();
                                                send();
                                            }
                                        } }
                                        placeholder={ placeholder }
                                        aria-label={ __( 'Message', 'wp-user-frontend' ) }
                                        disabled={ locked }
                                        className={ cn(
                                            'wpuf-chat-input max-h-[200px] min-h-[88px] w-full resize-none rounded-md border border-solid border-gray-300 bg-white px-3 py-2.5 pr-14 text-sm shadow-none outline-none focus:border-primary focus:shadow-[0_10px_15px_-3px_rgba(16,185,129,0.1),0_4px_6px_-2px_rgba(16,185,129,0.05)] focus:outline-none',
                                            locked && 'cursor-not-allowed opacity-50'
                                        ) }
                                    />
                                    <button
                                        type="button"
                                        onClick={ send }
                                        disabled={ locked }
                                        aria-label={ __( 'Send', 'wp-user-frontend' ) }
                                        className={ cn( 'wpuf-send-button absolute bottom-2.5 right-2.5 flex size-9 cursor-pointer items-center justify-center rounded-full border-0 bg-primary text-white transition-colors hover:bg-primaryHover', locked && 'cursor-not-allowed opacity-50' ) }
                                    >
                                        <SendIcon />
                                    </button>
                                </div>
                            </div>
                        </div>

                        { large && (
                            // Drag to resize (develop); the arrow keys do the same from the keyboard.
                            <button
                                type="button"
                                aria-label={ __( 'Resize panels', 'wp-user-frontend' ) }
                                onMouseDown={ startResize }
                                onKeyDown={ ( event ) => {
                                    const step = { ArrowLeft: -2, ArrowRight: 2 }[ event.key ];

                                    if ( step ) {
                                        event.preventDefault();
                                        setChatWidth( ( width ) => Math.max( MIN_PANEL, Math.min( MAX_PANEL, width + step ) ) );
                                    }
                                } }
                                className={ cn( 'wpuf-resize-handle relative z-20 flex w-2 shrink-0 cursor-col-resize items-center justify-center self-stretch border-0 bg-[#eaffea] p-0 transition-all hover:w-3 hover:bg-[#069668]', resizing && 'w-3 bg-emerald-400' ) }
                            >
                                <span className="pointer-events-none flex flex-col gap-1">
                                    <span className="block size-1 rounded-full bg-green-800" />
                                    <span className="block size-1 rounded-full bg-green-800" />
                                    <span className="block size-1 rounded-full bg-green-800" />
                                </span>
                            </button>
                        ) }

                        <div
                            className={ cn( 'wpuf-form-preview relative flex h-[calc(100vh-12rem)] min-w-0 shrink-0 flex-col gap-6 rounded-[10px] border border-solid border-gray-200 bg-white p-4 shadow-sm sm:h-[calc(100vh-10rem)] sm:p-6 lg:rounded-l-none lg:border-l-0 lg:p-8', chat.updating && 'wpuf-form-updating' ) }
                            style={ { width: large ? `${ 100 - chatWidth }%` : '100%' } }
                        >
                            { chat.updating && (
                                <div className="absolute inset-0 z-10 flex flex-col items-center justify-center rounded-lg bg-white/80 backdrop-blur-[1px]">
                                    <img src={ `${ assetUrl() }/images/ai-star.gif` } alt={ __( 'Processing', 'wp-user-frontend' ) } className="size-24" />
                                </div>
                            ) }
                            { ( chat.localTitle || chat.fields.length > 0 ) && (
                                <div className={ cn( 'shrink-0 pb-8', chat.updating && 'blur-[2px]' ) }>
                                    { chat.localTitle && <h3 className="wpuf-form-title m-0 mb-1 text-center text-xl font-semibold leading-7 text-gray-900">{ chat.localTitle }</h3> }
                                    { chat.fields.length > 0 && (
                                        <p className="m-0 text-center text-sm font-normal text-gray-500">{ chat.description || __( 'Please complete all information below', 'wp-user-frontend' ) }</p>
                                    ) }
                                </div>
                            ) }
                            <div className={ cn( 'wpuf-form-scrollable mb-4 flex-1 overflow-y-auto', chat.updating && 'blur-[2px]' ) }>
                                { 0 === chat.fields.length && ! chat.updating && (
                                    <div className="flex h-full min-h-[300px] flex-col items-center justify-center">
                                        <p className="m-0 mb-2 text-center text-lg text-gray-500">
                                            { chat.waiting ? __( 'Your form is being generated', 'wp-user-frontend' ) : __( 'No form fields yet', 'wp-user-frontend' ) }
                                        </p>
                                        <p className="m-0 text-center text-base leading-6 text-gray-400">
                                            { chat.waiting ? __( 'Please wait...', 'wp-user-frontend' ) : __( 'Use the chat to create your form', 'wp-user-frontend' ) }
                                        </p>
                                    </div>
                                ) }
                                { chat.fields.length > 0 && (
                                    <div className="wpuf-form-fields flex flex-col gap-4">
                                        { chat.fields.map( ( field, index ) => <PreviewField key={ field.id || index } field={ field } /> ) }
                                    </div>
                                ) }
                            </div>
                            <div className="-mx-4 shrink-0 border-t border-gray-200 px-4 pt-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                                <Button size="lg" onClick={ editInBuilder } busy={ saving } className="wpuf-btn-edit-full w-full justify-center">
                                    { __( 'Edit with Builder', 'wp-user-frontend' ) }
                                    { ! saving && <PencilIcon /> }
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <RegenerateDialog
                open={ regenerateOpen }
                onCancel={ () => setRegenerateOpen( false ) }
                onConfirm={ () => {
                    setRegenerateOpen( false );
                    onRegenerate();
                } }
            />
            <ProFieldsDialog
                items={ proItems }
                onClose={ () => setProItems( [] ) }
                onContinue={ () => {
                    setProItems( [] );
                    // Develop's "Continue without Pro" sends the fields as they are.
                    onEditInBuilder( chat.fields );
                } }
            />
        </div>
    );
}
