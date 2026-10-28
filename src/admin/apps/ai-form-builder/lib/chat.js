/**
 * Chat helpers of the AI form builder (develop's AIFormBuilder.vue and
 * FormSuccessStage.vue): session ids, the first summary message, when a reply
 * gets Accept / Reject, the request context and API error messages.
 *
 * @since WPUF_SINCE
 */
import { summaryFieldType } from './fields';

const TYPE_DESCRIPTIONS = {
    text: 'Text input field',
    email: 'Email address field',
    tel: 'Phone number field',
    url: 'Website URL field',
    number: 'Numeric input field',
    textarea: 'Multi-line text area',
    select: 'Dropdown selection',
    radio: 'Single choice selection',
    checkbox: 'Multiple choice selection',
    file: 'File upload field',
    date: 'Date picker field',
    time: 'Time picker field',
    datetime: 'Date and time picker',
};

const INFORMATIONAL_PHRASES = [
    'here are some suggestions', 'you can try', 'for example', 'available options include',
    'wpuf supports', 'you might want to', 'consider using', 'alternatively', 'note that',
    'please note', 'keep in mind', 'remember that', 'tip:', 'helpful tip', 'pro tip',
    'suggestion:', 'recommendations', 'best practices', 'you can also', 'if you need',
    'to learn more', 'for more information', 'documentation', 'help', 'guide', 'tutorial',
    'how to', 'instructions',
];

const SIMPLE_CONFIRMATIONS = [ 'understood', 'got it', 'okay', 'sure', 'no problem', 'will do', 'of course', 'absolutely', 'certainly' ];

const ACTIONABLE_WORDS = [ 'added', 'removed', 'changed', 'updated', 'modified', 'converted', 'replaced', 'field', 'form', 'button', 'title', 'description' ];

/**
 * A session id in develop's format (matches the API's `^[a-zA-Z0-9_-]{1,64}$`).
 *
 * @param {string} prefix wpuf_ai_session | wpuf_chat_session
 *
 * @return {string} Session id.
 */
export function newSessionId( prefix ) {
    return `${ prefix }_${ Date.now() }_${ Math.random().toString( 36 ).substring( 2, 11 ) }`;
}

/**
 * Description of a field type in the first summary (develop's fallback text).
 *
 * @param {string} type Display type.
 *
 * @return {string} Description.
 */
function fieldTypeDescription( type ) {
    return TYPE_DESCRIPTIONS[ type ] || 'Input field';
}

/**
 * The summary of a generated form: shown as a list in the chat, and sent as
 * develop's HTML text in the chat history (`content`), so the API gets the
 * same context.
 *
 * @param {string} title       Form title.
 * @param {Array}  fields      Generated fields.
 * @param {string} description Form description.
 *
 * @return {Object} { title, items, description, content }.
 */
export function buildSummary( title, fields, description ) {
    const items = ( fields || [] ).map( ( field ) => ( {
        label: field.label,
        required: 'yes' === field.required || true === field.required,
        description: fieldTypeDescription( summaryFieldType( field ) ),
    } ) );
    const text = description || 'The form is ready and you can customize it further in the form builder!';
    const list = items.map( ( item ) => `<li>${ item.label }${ item.required ? ' (Required)' : '' } - ${ item.description }</li>` ).join( '' );

    return {
        title,
        items,
        description: text,
        content: `Perfect! I've created a "${ title }" form for you with the following fields:
                    <ul>${ list }</ul>
                    ${ text }`,
    };
}

/**
 * Whether a reply gets Accept / Reject (develop's shouldShowButtons).
 *
 * @param {Object} response     API (or canned) response.
 * @param {string} content      Reply text.
 * @param {number} messageCount Messages in the chat before the reply.
 *
 * @return {boolean} Show the buttons.
 */
export function shouldShowButtons( response, content, messageCount ) {
    if ( ! response || ! response.success || response.error ) {
        return false;
    }

    if ( content.includes( 'Processing' ) || content.includes( 'Loading' ) ) {
        return false;
    }

    if ( content.includes( 'Perfect! I\'ve created' ) || content.includes( 'Successfully created the form' ) || 0 === messageCount ) {
        return false;
    }

    const lower = content.toLowerCase();

    if ( INFORMATIONAL_PHRASES.some( ( phrase ) => lower.includes( phrase ) ) ) {
        return false;
    }

    if ( SIMPLE_CONFIRMATIONS.some( ( phrase ) => lower.includes( phrase ) ) && lower.length < 100 ) {
        return false;
    }

    if ( response.form_data || response.data ) {
        const changes = response.form_data?.wpuf_fields || response.form_data?.fields || response.data?.modification_type || response.data?.changes;

        if ( changes ) {
            return true;
        }
    }

    return messageCount > 1 && ACTIONABLE_WORDS.some( ( word ) => lower.includes( word ) );
}

/**
 * The last eight chat messages for the API (no processing or error messages).
 *
 * @param {Array} messages Chat messages.
 *
 * @return {Array} { type, content, timestamp }.
 */
export function chatHistory( messages ) {
    return messages
        .filter( ( message ) => ! message.isProcessing && ! message.isError && message.type )
        .slice( -8 )
        .map( ( message ) => ( { type: message.type, content: message.content, timestamp: message.timestamp } ) );
}

/**
 * A readable message from a failed generate response (develop: the provider's
 * own `error.message` when the WP error wraps its JSON, plus `data.details`).
 *
 * @param {Object} errorData Parsed error body.
 * @param {string} fallback  Message when nothing better is found.
 *
 * @return {string} Message.
 */
export function generationErrorMessage( errorData, fallback ) {
    let message = fallback;

    if ( errorData && errorData.message ) {
        message = errorData.message;

        const json = errorData.message.match( /\{[\s\S]*\}/ );

        if ( json ) {
            try {
                const parsed = JSON.parse( json[ 0 ] );

                if ( parsed.error && parsed.error.message ) {
                    message = parsed.error.message;
                }
            } catch {
                // Not JSON: keep the WordPress message.
            }
        }
    }

    if ( errorData && errorData.data && errorData.data.details ) {
        const details = 'string' === typeof errorData.data.details ? errorData.data.details : JSON.stringify( errorData.data.details );

        if ( ! message.includes( details ) ) {
            message += ` (${ details })`;
        }
    }

    return message;
}

/**
 * Develop's form hash for "template modified" detection.
 *
 * @param {string} title  Form title.
 * @param {Array}  fields Fields.
 *
 * @return {number} Hash.
 */
export function formHash( title, fields ) {
    const state = JSON.stringify( {
        title,
        fields: fields.map( ( field ) => ( { type: field.type, label: field.label, required: field.required, options: field.options } ) ),
    } );

    // eslint-disable-next-line no-bitwise -- develop's string hash.
    return state.split( '' ).reduce( ( hash, char ) => ( ( hash << 5 ) - hash + char.charCodeAt( 0 ) ) & 0xffffffff, 0 );
}
