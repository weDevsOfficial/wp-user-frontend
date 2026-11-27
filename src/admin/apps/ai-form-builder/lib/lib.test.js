/* global describe, test, expect */
/**
 * Unit tests of the AI form builder logic ported from develop's Vue app.
 */
import { buildSummary, chatHistory, generationErrorMessage, shouldShowButtons } from './chat';
import { convertFieldsToPreview, normalizeOptions, normalizeRequired, previewKind, toWpufField } from './fields';
import { isInformationalQuery, isIrrelevantQuery, isModificationRequest, isPredefinedPrompt, shouldMakeAPICall } from './intent';
import { findProFields, proFieldItems } from './proFields';
import { predefinedResponse } from './responses';

describe( 'intent', () => {
    test( 'change requests go to the API', () => {
        [ 'Add a phone number field', 'Please remove the message field', 'make all fields required', 'Can you add a dropdown?', 'Add another field please', 'use a select instead of radio' ]
            .forEach( ( message ) => {
                expect( isModificationRequest( message ) ).toBe( true );
                expect( shouldMakeAPICall( message ) ).toBe( true );
            } );
    } );

    test( 'questions are answered without the API', () => {
        [ 'Why is the email field required?', 'What fields are in this form?', 'explain the purpose of portfolio files' ]
            .forEach( ( message ) => {
                expect( isInformationalQuery( message ) ).toBe( true );
                expect( shouldMakeAPICall( message ) ).toBe( false );
            } );
    } );

    test( 'unclear messages default to the API, off-topic ones are spotted', () => {
        expect( shouldMakeAPICall( 'nice' ) ).toBe( true );
        expect( isIrrelevantQuery( 'tell me a joke' ) ).toBe( true );
        expect( isIrrelevantQuery( 'tell me about the email form field' ) ).toBe( false );
        expect( isPredefinedPrompt( 'A real estate listing form' ) ).toBe( true );
        expect( isPredefinedPrompt( 'A survey' ) ).toBe( false );
    } );

    test( 'canned answers follow the question type', () => {
        const first = () => 0;
        expect( predefinedResponse( 'Why is email required?', first ) ).toMatch( /^The fields in this form are designed/ );
        expect( predefinedResponse( 'what is this?', first ) ).toMatch( /^Each field in this form has a specific purpose/ );
        expect( predefinedResponse( 'tell me a joke', first ) ).toMatch( /form builder assistant/ );
    } );
} );

describe( 'fields', () => {
    test( 'preview conversion keeps develop\'s shape', () => {
        const [ field ] = convertFieldsToPreview( [ { template: 'gender_field', label: 'Gender', required: 'yes', options: [ 'm', { value: 'f', label: 'Female' } ] } ] );
        expect( field ).toMatchObject( { id: 'field_1', type: 'gender_field', template: 'gender_field', name: 'wpuf_gender', meta_key: 'wpuf_gender', required: true, options: { m: 'm', f: 'Female' } } );
        expect( field.wpuf_cond.condition_status ).toBe( 'no' );
        expect( convertFieldsToPreview( [ { type: 'dropdown_field', label: 'X' } ] )[ 0 ].options ).toEqual( {} );
        expect( convertFieldsToPreview( [ { type: 'text_field', options: 'a|A\nb' } ] )[ 0 ].options ).toEqual( { a: 'A', b: 'b' } );
    } );

    test( 'fields sent back to the API get the full WPUF shape', () => {
        const full = toWpufField( { type: 'email_address', label: 'Your Email', required: true }, 0 );
        expect( full ).toMatchObject( { id: 'field_1', input_type: 'email', template: 'email_address', required: 'yes', name: 'your_email', is_meta: 'yes', options: {} } );
        const complete = { input_type: 'text', template: 'text_field', wpuf_cond: {} };
        expect( toWpufField( complete, 3 ) ).toBe( complete );
        expect( normalizeRequired( [ { required: true }, { required: 'no' } ] ).map( ( f ) => f.required ) ).toEqual( [ 'yes', 'no' ] );
    } );

    test( 'preview kind follows develop\'s template order', () => {
        expect( previewKind( { input_type: 'text', template: 'post_title' } ) ).toBe( 'text' );
        expect( previewKind( { input_type: 'checkbox', type: 'checkbox_field' } ) ).toBe( 'checkbox' );
        expect( previewKind( { template: 'post_content', input_type: 'textarea' } ) ).toBe( 'textarea' );
        expect( previewKind( { template: 'date_field', input_type: 'date' } ) ).toBe( 'date' );
        expect( previewKind( { type: 'column_field', template: 'column_field', input_type: 'column_field' } ) ).toBe( 'column' );
        expect( previewKind( { input_type: 'mystery' } ) ).toBe( 'fallback' );
        expect( normalizeOptions( { a: 'A' } ) ).toEqual( [ { value: 'a', label: 'A' } ] );
    } );
} );

describe( 'chat', () => {
    test( 'summary is a list for the chat and develop\'s HTML for the API', () => {
        const summary = buildSummary( 'Contact', [ { label: 'Email', required: 'yes', input_type: 'email' } ], '' );
        expect( summary.items ).toEqual( [ { label: 'Email', required: true, description: 'Input field' } ] );
        expect( summary.content ).toContain( '<li>Email (Required) - Input field</li>' );
        expect( summary.description ).toMatch( /ready/ );
    } );

    test( 'Accept / Reject only for real changes', () => {
        const change = { success: true, data: { wpuf_fields: [ {} ] } };
        expect( shouldShowButtons( change, 'Form has been updated successfully.', 3 ) ).toBe( true );
        expect( shouldShowButtons( { success: true, message: 'x' }, 'The fields are helpful', 3 ) ).toBe( false );
        expect( shouldShowButtons( { success: false }, 'Form updated', 3 ) ).toBe( false );
        expect( shouldShowButtons( change, 'Perfect! I\'ve created a form', 3 ) ).toBe( false );
    } );

    test( 'history drops processing and error messages, keeps the last eight', () => {
        const messages = Array.from( { length: 12 }, ( _, i ) => ( { type: 'user', content: String( i ) } ) );
        messages.push( { type: 'ai', content: '', isProcessing: true }, { type: 'ai', content: 'x', isError: true } );
        expect( chatHistory( messages ).map( ( m ) => m.content ) ).toEqual( [ '4', '5', '6', '7', '8', '9', '10', '11' ] );
    } );

    test( 'error message prefers the provider message inside the WordPress one', () => {
        expect( generationErrorMessage( { message: 'OpenAI API returned HTTP 500: {"error":{"message":"Bad key"}}' }, 'fallback' ) ).toBe( 'Bad key' );
        expect( generationErrorMessage( { message: 'Plain', data: { details: 'more' } }, 'fallback' ) ).toBe( 'Plain (more)' );
        expect( generationErrorMessage( {}, 'fallback' ) ).toBe( 'fallback' );
    } );
} );

describe( 'pro fields', () => {
    test( 'Pro fields are found, free fields are not', () => {
        const fields = [ { type: 'phone_field', template: 'phone_field', input_type: 'tel' }, { type: 'email_address', template: 'email_address' }, { type: 'date_field', template: 'date_field', input_type: 'date' } ];
        expect( findProFields( fields ).map( ( f ) => f.type ) ).toEqual( [ 'phone_field', 'date_field' ] );
        expect( proFieldItems( findProFields( fields ), 'https://x/assets' ) ).toEqual( [
            { key: 'phone_field', label: 'Phone Number', icon: 'https://x/assets/images/phone.svg' },
            { key: 'date_field', label: 'Date Picker', icon: 'https://x/assets/images/clock.svg' },
        ] );
    } );
} );
