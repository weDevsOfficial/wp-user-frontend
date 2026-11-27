import { invalidRequiredSettings, invalidSettingsMessage, settingProblem } from './requiredSettings';

const items = {
    post_settings: {
        section: {
            before_post_settings: {
                label: 'Before Post Settings',
                fields: {
                    submit_text: { label: 'Submit Post Button Text', default: 'Submit' },
                    page_id: { label: 'Page' },
                    url: { label: 'Custom URL' },
                },
            },
            after_post_settings: {
                label: 'After Post Settings',
                fields: { update_text: { label: 'Update Post Button Text' } },
            },
        },
    },
};

const shown = ( settings ) => ( key ) => ( 'page_id' === key ? 'page' === settings.redirect_to : 'url' === key ? 'url' === settings.redirect_to : true );

describe( 'required form settings', () => {
    test( 'empty, whitespace and empty rich text are refused; a page needs an id; a URL needs http(s)', () => {
        expect( settingProblem( 'submit_text', '  ' ) ).not.toBe( '' );
        expect( settingProblem( 'welcome_email_body', '<p> </p>' ) ).not.toBe( '' );
        expect( settingProblem( 'submit_text', 'Create Post' ) ).toBe( '' );
        expect( settingProblem( 'page_id', '0' ) ).not.toBe( '' );
        expect( settingProblem( 'page_id', '-1' ) ).not.toBe( '' );
        expect( settingProblem( 'page_id', 12 ) ).toBe( '' );
        expect( settingProblem( 'url', 'example.com' ) ).not.toBe( '' );
        expect( settingProblem( 'url', 'https://example.com/thanks' ) ).toBe( '' );
    } );

    test( 'only shown settings count; a missing key falls back to the default', () => {
        const settings = { submit_text: '', redirect_to: 'post' };
        const invalid = invalidRequiredSettings( { settings, formType: 'wpuf_forms', items, isVisible: shown( settings ) } );

        // submit_text stored empty, update_text missing with no default; page / URL hidden.
        expect( invalid.map( ( item ) => item.key ) ).toEqual( [ 'submit_text', 'update_text' ] );
        expect( invalid[ 0 ].section ).toBe( 'Before Post Settings' );

        const withPage = { submit_text: 'Go', update_text: 'Update', redirect_to: 'page', page_id: '' };
        expect( invalidRequiredSettings( { settings: withPage, formType: 'wpuf_forms', items, isVisible: shown( withPage ) } ).map( ( item ) => item.key ) ).toEqual( [ 'page_id' ] );

        // Missing submit_text uses its default 'Submit': fine.
        const missing = { update_text: 'Update' };
        expect( invalidRequiredSettings( { settings: missing, formType: 'wpuf_forms', items, isVisible: shown( missing ) } ) ).toEqual( [] );
    } );

    test( 'the message names each setting and its section, escaped', () => {
        const html = invalidSettingsMessage( [ { label: 'Submit <b>', section: 'Before Post Settings', problem: 'This field is required.' } ] );

        expect( html ).toContain( 'Submit &lt;b&gt;' );
        expect( html ).toContain( '(Before Post Settings)' );
    } );
} );
