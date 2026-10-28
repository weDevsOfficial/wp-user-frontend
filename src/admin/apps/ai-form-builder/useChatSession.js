/**
 * Chat state of the success stage (develop's FormSuccessStage.vue logic):
 * sending a message (API or canned answer), pending changes with Accept /
 * Reject, checkpoints and restore, and the conversation context the API gets.
 *
 * @since WPUF_SINCE
 */
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { notify } from '@wpuf/components';

import { sendChat } from './api';
import { chatHistory, formHash, newSessionId, shouldShowButtons } from './lib/chat';
import { convertFieldsToPreview, formatAddedField, toWpufField } from './lib/fields';
import { isInformationalQuery, isPredefinedPrompt, shouldMakeAPICall } from './lib/intent';
import { predefinedResponse } from './lib/responses';

const STATUS_MS = 3000;

const clone = ( value ) => JSON.parse( JSON.stringify( value ) );

const freshConversation = () => ( {
    original_prompt: '',
    form_created: false,
    modifications_count: 0,
    context_history: [],
    is_predefined_template: false,
    template_modified: false,
    original_form_hash: null,
} );

const now = () => new Date().toISOString();

/**
 * @param {Object}   options
 * @param {string}   options.title           Form title (from the generation).
 * @param {Array}    options.initialFields   Generated fields.
 * @param {Array}    options.initialMessages First messages (prompt, "...", summary).
 * @param {Function} options.onFormUpdated   ( { wpuf_fields, form_title, form_description } ) on Accept.
 * @param {Function} options.onTitle         ( title ) when an accepted change renames the form.
 *
 * @return {Object} Chat state and actions.
 */
export default function useChatSession( { title, initialFields, initialMessages, onFormUpdated, onTitle } ) {
    const [ messages, setMessages ] = useState( () => [ ...( initialMessages || [] ) ] );
    const [ fields, setFields ] = useState( () => [ ...( initialFields || [] ) ] );
    const [ description, setDescription ] = useState( '' );
    const [ localTitle, setLocalTitle ] = useState( title );
    const [ updating, setUpdating ] = useState( false );
    const [ waiting, setWaiting ] = useState( false );
    const [ visibleStatuses, setVisibleStatuses ] = useState( () => new Set() );

    const settings = useRef( {} );
    const pending = useRef( null );
    const previous = useRef( null );
    const checkpoints = useRef( new Map() );
    const conversation = useRef( freshConversation() );
    const sessionId = useRef( newSessionId( 'wpuf_chat_session' ) );
    const timers = useRef( new Map() );
    const latest = useRef( { messages, fields, description, title } );

    latest.current = { messages, fields, description, title };

    const showStatus = useCallback( ( index ) => {
        setVisibleStatuses( ( current ) => new Set( current ).add( index ) );
        clearTimeout( timers.current.get( index ) );
        timers.current.set( index, setTimeout( () => {
            setVisibleStatuses( ( current ) => {
                const next = new Set( current );

                next.delete( index );

                return next;
            } );
        }, STATUS_MS ) );
    }, [] );

    const startConversation = ( prompt ) => {
        if ( ! prompt ) {
            return;
        }

        Object.assign( conversation.current, {
            original_prompt: prompt,
            is_predefined_template: isPredefinedPrompt( prompt ),
            original_form_hash: formHash( latest.current.title, latest.current.fields ),
            form_created: true,
        } );
    };

    useEffect( () => {
        const firstUser = ( initialMessages || [] ).find( ( message ) => 'user' === message.type );

        if ( firstUser ) {
            startConversation( firstUser.content );
        }

        ( initialMessages || [] ).forEach( ( message, index ) => message.status && showStatus( index ) );

        if ( title && ! conversation.current.form_created ) {
            startConversation( title );
        }

        const pendingTimers = timers.current;

        return () => pendingTimers.forEach( clearTimeout );
        // Mount only, like develop's mounted().
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [] );

    useEffect( () => setLocalTitle( title ), [ title ] );

    const hasPendingButtons = messages.some( ( message ) => true === message.showButtons );

    const remember = ( userMessage, aiMessage ) => {
        const state = conversation.current;

        state.context_history.push( {
            timestamp: now(),
            user_message: userMessage,
            ai_response: aiMessage,
            form_state: {
                title: latest.current.title,
                fields_count: latest.current.fields.length,
                field_types: latest.current.fields.map( ( field ) => field.type ),
            },
        } );
        state.context_history = state.context_history.slice( -10 );
        state.modifications_count++;
    };

    const context = ( list ) => ( {
        session_id: sessionId.current,
        conversation_state: conversation.current,
        current_form: {
            form_title: latest.current.title,
            form_description: latest.current.description,
            wpuf_fields: latest.current.fields.map( ( field, index ) => toWpufField( field, index, {}, 'Field' ) ),
            settings: settings.current || {},
        },
        chat_history: chatHistory( list ),
    } );

    const send = async ( text ) => {
        const userMessage = text.trim();

        if ( ! userMessage || updating ) {
            return;
        }

        if ( ! conversation.current.form_created && 0 === latest.current.messages.length ) {
            startConversation( userMessage );
        }

        const callApi = shouldMakeAPICall( userMessage );
        const withUser = [ ...latest.current.messages, { type: 'user', content: userMessage, timestamp: now() } ];

        if ( callApi ) {
            if ( latest.current.fields.length > 0 ) {
                setUpdating( true );
            } else {
                setWaiting( true );
            }
        }

        setMessages( [ ...withUser, { type: 'ai', content: '', isProcessing: true, showButtons: false, timestamp: now() } ] );

        const stopLoading = () => {
            setUpdating( false );
            setWaiting( false );
        };

        let response;

        try {
            response = callApi
                ? await sendChat( { message: userMessage, sessionId: sessionId.current, conversationContext: context( withUser ) } )
                : { success: true, message: predefinedResponse( userMessage ), action: 'info', form_data: null };
        } catch ( error ) {
            stopLoading();
            setMessages( [ ...withUser, {
                type: 'ai',
                content: error.message || __( 'Sorry, there was an error processing your request. Please try again.', 'wp-user-frontend' ),
                showButtons: false,
                isError: true,
                timestamp: now(),
            } ] );
            return;
        }

        if ( ! response.success ) {
            stopLoading();
            setMessages( [ ...withUser, {
                type: 'ai',
                content: response.message || __( 'Sorry, I could not process your request. Please try again.', 'wp-user-frontend' ),
                showButtons: false,
                isError: true,
                timestamp: now(),
            } ] );
            return;
        }

        const formData = response.form_data || response.data;
        const hasChanges = formData && ( formData.wpuf_fields || 'add_field' === formData.modification_type );
        let fallback = '';

        if ( isInformationalQuery( userMessage ) && ! hasChanges ) {
            fallback = response.message ? '' : __( "I can provide information about the form fields. Please be specific about what you'd like to know.", 'wp-user-frontend' );
        } else if ( hasChanges ) {
            const next = formData.wpuf_fields ? convertFieldsToPreview( formData.wpuf_fields ) : latest.current.fields;

            fallback = JSON.stringify( latest.current.fields ) !== JSON.stringify( next )
                ? __( 'Form has been updated successfully.', 'wp-user-frontend' )
                : __( 'The form already has those fields configured.', 'wp-user-frontend' );
        } else {
            fallback = response.message || __( 'I can help you with form-related tasks. Try asking me to add, remove, or modify form fields.', 'wp-user-frontend' );
        }

        const content = response.message || fallback;
        const aiMessage = {
            type: 'ai',
            content,
            showButtons: shouldShowButtons( response, content, withUser.length ),
            hasCheckpoint: false,
            checkpointSaved: false,
            response_data: response,
            timestamp: now(),
        };

        remember( userMessage, aiMessage );

        if ( formData && formData.wpuf_fields ) {
            const next = convertFieldsToPreview( formData.wpuf_fields );

            previous.current = clone( latest.current.fields );
            pending.current = {
                type: 'form_update',
                fields: next,
                formTitle: formData.form_title || latest.current.title,
                formDescription: formData.form_description || latest.current.description,
                previousDescription: latest.current.description,
                originalResponse: formData,
            };
            setFields( next );

            if ( formData.form_description ) {
                setDescription( formData.form_description );
            }
        } else if ( response.data && 'add_field' === response.data.modification_type && response.data.changes?.field ) {
            const added = formatAddedField( response.data.changes.field, latest.current.fields.length );

            previous.current = clone( latest.current.fields );
            pending.current = { type: 'add_field', field: added, originalResponse: response.data };
            setFields( [ ...latest.current.fields, added ] );
        }

        stopLoading();
        setMessages( [ ...withUser, aiMessage ] );
    };

    const hideButtons = ( list ) => {
        const next = [ ...list ];

        for ( let index = next.length - 1; index >= 0; index-- ) {
            if ( next[ index ].showButtons ) {
                next[ index ] = { ...next[ index ], showButtons: false };
                break;
            }
        }

        return next;
    };

    const reject = () => {
        if ( pending.current && previous.current ) {
            setFields( clone( previous.current ) );

            if ( 'form_update' === pending.current.type && undefined !== pending.current.previousDescription ) {
                setDescription( pending.current.previousDescription );
            }

            pending.current = null;
            previous.current = null;
        }

        setMessages( ( list ) => hideButtons( list ) );
    };

    const accept = () => {
        const change = pending.current;
        const current = latest.current;

        if ( change ) {
            if ( 'form_update' === change.type ) {
                if ( change.formTitle && change.formTitle !== current.title ) {
                    onTitle?.( change.formTitle );
                }

                onFormUpdated?.( {
                    wpuf_fields: change.originalResponse?.wpuf_fields || current.fields,
                    form_title: change.formTitle || current.title,
                    form_description: change.formDescription || current.description,
                } );
            } else {
                onFormUpdated?.( { wpuf_fields: current.fields, form_title: current.title, form_description: current.description } );
            }
        }

        const list = [ ...current.messages ];

        for ( let index = list.length - 1; index >= 0; index-- ) {
            if ( list[ index ].showButtons ) {
                checkpoints.current.set( index, {
                    formFields: clone( change?.originalResponse?.wpuf_fields || current.fields ),
                    formTitle: localTitle,
                    formDescription: current.description,
                    formSettings: clone( settings.current ),
                    timestamp: Date.now(),
                } );
                list[ index ] = {
                    ...list[ index ],
                    showButtons: false,
                    hasCheckpoint: true,
                    checkpointSaved: true,
                    acceptedStatus: __( '✓ Changes accepted & checkpoint saved', 'wp-user-frontend' ),
                };
                pending.current = null;
                previous.current = null;
                break;
            }
        }

        setMessages( list );
    };

    const restore = ( index ) => {
        const checkpoint = checkpoints.current.get( index );

        if ( ! checkpoint ) {
            setMessages( ( list ) => [ ...list, {
                type: 'ai',
                content: __( 'Sorry, no checkpoint was found for this state.', 'wp-user-frontend' ),
                showButtons: false,
                isError: true,
                timestamp: now(),
            } ] );
            return;
        }

        setUpdating( true );
        setTimeout( () => {
            setFields( clone( checkpoint.formFields ) );
            setDescription( checkpoint.formDescription );
            settings.current = clone( checkpoint.formSettings );

            if ( checkpoint.formTitle ) {
                setLocalTitle( checkpoint.formTitle );
            }

            setUpdating( false );
            setWaiting( false );
            notify( __( 'Form has been restored to the checkpoint.', 'wp-user-frontend' ), 'success' );
        }, 300 );
    };

    return {
        messages,
        fields,
        description,
        localTitle,
        updating,
        waiting,
        hasPendingButtons,
        isStatusVisible: ( index ) => visibleStatuses.has( index ),
        send,
        accept,
        reject,
        restore,
    };
}
