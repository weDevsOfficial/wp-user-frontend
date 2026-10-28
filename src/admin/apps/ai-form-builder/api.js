/**
 * Requests of the AI form builder: the endpoints and nonce come from
 * `wpufAIFormBuilder` (filterable through `wpuf_ai_form_builder_localize_data`,
 * like the Vue app); request bodies are the Vue app's.
 *
 * @since WPUF_SINCE
 */
import { parseJsonBody } from '@wpuf/api';

import { generationErrorMessage } from './lib/chat';

/**
 * The localized data.
 *
 * @return {Object} wpufAIFormBuilder.
 */
export const config = () => window.wpufAIFormBuilder || {};

/**
 * Plugin asset URL (images).
 *
 * @return {string} URL.
 */
export const assetUrl = () => config().assetUrl || config().pluginUrl || ( window.wpuf_frontend || {} ).asset_url || '';

/**
 * Fetch JSON with the REST nonce.
 *
 * @param {string} url    URL.
 * @param {Object} [init] fetch init.
 *
 * @return {Promise<Response>} Response.
 */
function send( url, init = {} ) {
    return window.fetch( url, {
        ...init,
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config().nonce || '', ...( init.headers || {} ) },
    } );
}

/**
 * JSON body of a response; stray output in front of it (another plugin's
 * notice) is skipped.
 *
 * @param {Response} response Response.
 *
 * @return {Promise<Object>} Body.
 */
const jsonBody = async ( response ) => parseJsonBody( await response.text() );

/**
 * Body of a failed response, or {}.
 *
 * @param {Response} response Response.
 *
 * @return {Promise<Object>} Body.
 */
const errorBody = ( response ) => jsonBody( response ).catch( () => ( {} ) );

/**
 * Integrations available for a form type ([] on failure, as develop).
 *
 * @param {string} formType Form type.
 *
 * @return {Promise<Array>} Integrations.
 */
export async function fetchIntegrations( formType ) {
    const url = config().endpoints?.integrations;

    if ( ! url ) {
        return [];
    }

    const response = await send( url + '?form_type=' + formType, { method: 'GET' } );

    if ( ! response.ok ) {
        return [];
    }

    const result = await jsonBody( response );

    return result.success && result.integrations ? result.integrations : [];
}

/**
 * First generation: `{ prompt, session_id, form_type, provider, integration? }`.
 * Throws with develop's readable message on an HTTP error.
 *
 * @param {Object} args             Arguments.
 * @param {string} args.prompt      Prompt.
 * @param {string} args.sessionId   Session id.
 * @param {string} args.integration Integration ('' for none).
 *
 * @return {Promise<Object>} Response body.
 */
export async function generateForm( { prompt, sessionId, integration } ) {
    const url = config().endpoints?.generate;

    if ( ! url ) {
        throw new Error( 'WPUF AI Form Builder: Generate endpoint not configured' );
    }

    const body = { prompt, session_id: sessionId, form_type: config().formType, provider: config().provider };

    if ( integration ) {
        body.integration = integration;
    }

    const response = await send( url, { method: 'POST', body: JSON.stringify( body ) } );

    if ( ! response.ok ) {
        throw new Error( generationErrorMessage( await errorBody( response ), `HTTP ${ response.status }: ${ response.statusText }` ) );
    }

    return jsonBody( response );
}

/**
 * Chat change request on the generate endpoint, with the conversation
 * context (develop's callChatAPI body).
 *
 * @param {Object} args                     Arguments.
 * @param {string} args.message             User message.
 * @param {string} args.sessionId           Chat session id.
 * @param {Object} args.conversationContext Context.
 *
 * @return {Promise<Object>} Response body.
 */
export async function sendChat( { message, sessionId, conversationContext } ) {
    const url = config().endpoints?.generate;

    if ( ! url ) {
        throw new Error( 'WPUF AI Form Builder: Generate endpoint not configured' );
    }

    const language = config().selectedLanguage || window.wpufSelectedLanguage || 'English';
    const response = await send( url, {
        method: 'POST',
        body: JSON.stringify( {
            prompt: message,
            session_id: sessionId,
            conversation_context: conversationContext,
            form_type: config().formType,
            provider: config().provider,
            language,
        } ),
    } );

    if ( ! response.ok ) {
        const data = await errorBody( response );

        throw new Error( data.message || response.statusText );
    }

    return jsonBody( response );
}

/**
 * Create the WPUF form (develop's editInBuilder body).
 *
 * @param {Object} formData { form_title, form_description, wpuf_fields, form_settings }.
 *
 * @return {Promise<Object>} { success, form_id, edit_url, list_url, ... }.
 */
export async function createForm( formData ) {
    const url = config().endpoints?.createForm;

    if ( ! url ) {
        throw new Error( 'WPUF AI Form Builder: Create form endpoint not configured' );
    }

    const response = await send( url, {
        method: 'POST',
        body: JSON.stringify( { form_data: formData, form_type: config().formType || 'post' } ),
    } );

    if ( ! response.ok ) {
        const data = await errorBody( response );

        throw new Error( `HTTP ${ response.status }: ${ data.message || response.statusText }` );
    }

    const result = await jsonBody( response );

    if ( ! result.success || ! result.form_id ) {
        throw new Error( result.message || 'Failed to create form' );
    }

    return result;
}
