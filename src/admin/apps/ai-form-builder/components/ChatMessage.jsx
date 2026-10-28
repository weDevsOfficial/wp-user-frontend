/**
 * One chat bubble. Develop rendered every AI message with v-html (AI-returned
 * labels and error texts went into the page as markup); here a message is
 * data: plain text (line breaks and **bold** kept), or the generation summary
 * as a list.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { cn } from '@wedevs/plugin-ui';
import { Button } from '@wpuf/components';

import { LogoBadge, RestoreIcon, TypingDots } from './icons';

/**
 * Text with `**bold**` spans and line breaks, no markup from the text.
 *
 * @param {Object} props
 * @param {string} props.text Text.
 */
function RichText( { text } ) {
    return String( text || '' ).split( /(\*\*[^*]+\*\*)/g ).map( ( part, index ) => {
        if ( /^\*\*[^*]+\*\*$/.test( part ) ) {
            return <strong key={ index }>{ part.slice( 2, -2 ) }</strong>;
        }

        return <Fragment key={ index }>{ part }</Fragment>;
    } );
}

/**
 * The first summary: title, fields, description.
 *
 * @param {Object} props
 * @param {Object} props.summary { title, items, description }.
 */
function Summary( { summary } ) {
    return (
        <>
            { /* Develop's English sentence, kept so the history sent to the API is unchanged. */ }
            { `Perfect! I've created a "${ summary.title }" form for you with the following fields:` }
            <ul className="m-0 list-none p-0">
                { summary.items.map( ( item, index ) => (
                    <li key={ index }>
                        { item.label }{ item.required ? ' (Required)' : '' } - { item.description }
                    </li>
                ) ) }
            </ul>
            { summary.description }
        </>
    );
}

/**
 * @param {Object}   props
 * @param {Object}   props.message       Message.
 * @param {boolean}  props.statusVisible Status line shown.
 * @param {boolean}  props.applying      Accept / Reject running.
 * @param {Function} props.onAccept      Accept the pending change.
 * @param {Function} props.onReject      Reject the pending change.
 * @param {Function} props.onRestore     Restore this message's checkpoint.
 */
export default function ChatMessage( { message, statusVisible, applying, onAccept, onReject, onRestore } ) {
    if ( 'user' === message.type ) {
        return (
            <div className="wpuf-message-user flex justify-end">
                { /* Develop: the bubble wraps its text (no grow). */ }
                <div>
                    <div className="w-full whitespace-pre-line rounded-xl rounded-br border border-solid border-primary/30 bg-primary/5 px-3.5 py-2.5 text-sm font-normal leading-6 text-gray-800">
                        { message.content }
                    </div>
                </div>
            </div>
        );
    }

    let body;

    if ( message.isProcessing ) {
        body = <TypingDots />;
    } else if ( message.summary ) {
        body = <Summary summary={ message.summary } />;
    } else {
        body = <RichText text={ message.content } />;
    }

    return (
        <div className="wpuf-message-ai flex items-start gap-3" data-error={ message.isError || undefined }>
            { /* Develop's nested flex: the bubble wraps its text up to the panel width. */ }
            <div className="flex items-start gap-3">
            <LogoBadge className="size-8 shrink-0" />
            <div className="flex-1">
                <div className={ cn( 'wpuf-message-bubble-ai w-full rounded-xl rounded-bl border border-solid border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-700' ) }>
                    <div className="m-0 whitespace-pre-line text-sm leading-6 text-gray-700">{ body }</div>
                    { message.showButtons && (
                        <div className="wpuf-message-actions mt-4 flex gap-3">
                            <Button variant="secondary" size="sm" onClick={ onAccept } disabled={ applying } className="wpuf-btn-accept">
                                { __( 'Accept', 'wp-user-frontend' ) }
                            </Button>
                            <Button variant="secondary" size="sm" onClick={ onReject } disabled={ applying } className="wpuf-btn-reject">
                                { __( 'Reject', 'wp-user-frontend' ) }
                            </Button>
                        </div>
                    ) }
                    { message.acceptedStatus && <div className="wpuf-accepted-status mt-2 text-xs text-green-600">{ message.acceptedStatus }</div> }
                    { message.hasCheckpoint && message.checkpointSaved && (
                        <div className="wpuf-checkpoint-actions mt-2 flex items-center gap-2">
                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={ onRestore }
                                title={ __( 'Restore form to this checkpoint', 'wp-user-frontend' ) }
                                className="wpuf-btn-restore border-primary text-xs text-primary enabled:hover:bg-primary enabled:hover:text-white"
                            >
                                <RestoreIcon />
                                { __( 'Restore to this checkpoint', 'wp-user-frontend' ) }
                            </Button>
                        </div>
                    ) }
                </div>
                { message.status && statusVisible && (
                    <div className="wpuf-message-status mt-2 text-right text-[13px] font-normal italic text-primary">
                        { message.status }
                    </div>
                ) }
            </div>
            </div>
        </div>
    );
}
