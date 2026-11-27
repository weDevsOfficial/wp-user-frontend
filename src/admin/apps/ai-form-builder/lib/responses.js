/**
 * Canned chat answers for questions, greetings and off-topic messages (no
 * API call), ported from develop's FormSuccessStage.vue; the texts are now
 * translatable.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

import { isInformationalQuery, isIrrelevantQuery } from './intent';

/**
 * Pick one item (random, like develop; tests pass their own picker).
 *
 * @param {string[]} items Items.
 * @param {Function} pick  ( length ) => index.
 *
 * @return {string} Item.
 */
const one = ( items, pick ) => items[ pick( items.length ) ] || items[ 0 ];

const randomIndex = ( length ) => Math.floor( Math.random() * length );

/**
 * What the assistant can do, with examples.
 *
 * @return {string} Text.
 */
function helpfulExamples() {
    return [
        __( "I'm a form builder assistant. I can help you with form-related tasks only.", 'wp-user-frontend' ),
        '',
        __( 'Here are some examples of what you can ask:', 'wp-user-frontend' ),
        '',
        '**' + __( 'To modify fields:', 'wp-user-frontend' ) + '**',
        '• ' + __( '"Add a phone number field"', 'wp-user-frontend' ),
        '• ' + __( '"Remove the years of experience field"', 'wp-user-frontend' ),
        '• ' + __( '"Change email field to required"', 'wp-user-frontend' ),
        '• ' + __( '"Add a file upload field for documents"', 'wp-user-frontend' ),
        '',
        '**' + __( 'To get information:', 'wp-user-frontend' ) + '**',
        '• ' + __( '"What fields are in this form?"', 'wp-user-frontend' ),
        '• ' + __( '"Why is the email field required?"', 'wp-user-frontend' ),
        '• ' + __( '"Explain the purpose of portfolio files"', 'wp-user-frontend' ),
        '',
        '**' + __( 'To customize the form:', 'wp-user-frontend' ) + '**',
        '• ' + __( '"Make all fields optional except email"', 'wp-user-frontend' ),
        '• ' + __( '"Add a dropdown for country selection"', 'wp-user-frontend' ),
        '• ' + __( '"Include a terms and conditions checkbox"', 'wp-user-frontend' ),
        '',
        __( 'What would you like me to help you with?', 'wp-user-frontend' ),
    ].join( '\n' );
}

/**
 * Short examples for greetings.
 *
 * @return {string} Text.
 */
function quickExamples() {
    return [
        __( 'Try asking me to:', 'wp-user-frontend' ),
        '• ' + __( 'Add a new field: "Add a phone number field"', 'wp-user-frontend' ),
        '• ' + __( 'Remove a field: "Remove the experience field"', 'wp-user-frontend' ),
        '• ' + __( 'Modify a field: "Make email field optional"', 'wp-user-frontend' ),
        '• ' + __( 'Get information: "What fields are required?"', 'wp-user-frontend' ),
    ].join( '\n' );
}

/**
 * The canned answer for a message that does not go to the API.
 *
 * @param {string}   message Message.
 * @param {Function} [pick]  ( length ) => index.
 *
 * @return {string} Answer.
 */
export function predefinedResponse( message = '', pick = randomIndex ) {
    if ( isIrrelevantQuery( message ) ) {
        return helpfulExamples();
    }

    if ( isInformationalQuery( message ) ) {
        const lower = String( message ).toLowerCase().trim();

        if ( lower.includes( 'meaning' ) || lower.includes( 'purpose' ) || lower.includes( 'what is' ) ) {
            return __( 'Each field in this form has a specific purpose. The required fields ensure we collect essential information, while optional fields allow you to provide additional details. Which specific field would you like to know more about?', 'wp-user-frontend' );
        }

        if ( lower.includes( 'why' ) || lower.includes( 'reason' ) ) {
            return __( 'The fields in this form are designed to collect all necessary information for proper processing. Required fields are essential for submission, while optional fields provide additional context that may be helpful.', 'wp-user-frontend' );
        }

        if ( lower.includes( 'how' ) || lower.includes( 'help' ) ) {
            return helpfulExamples();
        }

        if ( lower.includes( 'how are you' ) || lower.includes( 'hello' ) || lower.includes( 'hi' ) ) {
            return __( "I'm a form builder assistant. I can help you with form-related tasks.", 'wp-user-frontend' ) + '\n\n' + quickExamples();
        }

        return one( [
            __( 'I can help explain the form fields. Each field serves a specific purpose for collecting information. Which field would you like to know more about?', 'wp-user-frontend' ),
            __( 'The form fields are designed to collect all required information. If you have questions about specific fields or need to make changes, please let me know.', 'wp-user-frontend' ),
            __( "I'm here to help with form-related tasks. You can ask about specific fields, or request to add, remove, or modify fields as needed.", 'wp-user-frontend' ),
            __( 'This form contains the essential fields for your submission. Need clarification about any field or want to make changes? Just let me know.', 'wp-user-frontend' ),
        ], pick );
    }

    return one( [
        __( "This form is based on our predefined template. The current fields are optimized for this type of submission. If you'd like to modify the form, please let me know what changes you need.", 'wp-user-frontend' ),
        __( "The form is ready with all essential fields. If you need to add, remove, or modify any fields, just tell me what changes you'd like to make.", 'wp-user-frontend' ),
        __( 'This form uses our predefined structure which works well for most cases. Would you like me to add any additional fields or modify the existing ones?', 'wp-user-frontend' ),
        __( "The current form is optimized for this type of submission. Need any customizations? Please let me know what specific changes you'd like.", 'wp-user-frontend' ),
    ], pick );
}
