import { isResponseLike, parseJsonBody, readJsonResponse } from './parse';

const json = '{"success":true,"data":{"items":[1,2],"note":"a { brace } inside"}}';

test( 'parseJsonBody parses clean JSON', () => {
    expect( parseJsonBody( json ) ).toEqual( JSON.parse( json ) );
    expect( parseJsonBody( '[1,2]' ) ).toEqual( [ 1, 2 ] );
} );

test( 'parseJsonBody skips stray output in front of the document', () => {
    expect( parseJsonBody( '\nDeprecated: foo() in [file.php] on line 5\n<br />{"x"\n' + json ) ).toEqual( JSON.parse( json ) );
    expect( parseJsonBody( '<b>Warning</b>: bar[1,2,3]' ) ).toEqual( [ 1, 2, 3 ] );
} );

test( 'parseJsonBody throws when there is no document', () => {
    expect( () => parseJsonBody( '' ) ).toThrow( SyntaxError );
    expect( () => parseJsonBody( '<option value="1">Child</option>' ) ).toThrow( SyntaxError );
    expect( () => parseJsonBody( 'Notice: x {"unterminated": tr' ) ).toThrow( SyntaxError );
} );

test( 'readJsonResponse: 204 and empty bodies give null, noise is skipped, no JSON is invalid_json', async () => {
    const response = ( status, text ) => ( { status, text: async () => text } );

    expect( isResponseLike( response( 200, '' ) ) ).toBe( true );
    expect( isResponseLike( { code: 'x' } ) ).toBe( false );
    await expect( readJsonResponse( response( 204, 'ignored' ) ) ).resolves.toBeNull();
    await expect( readJsonResponse( response( 200, '  \n' ) ) ).resolves.toBeNull();
    await expect( readJsonResponse( response( 200, 'Notice: x\n' + json ) ) ).resolves.toEqual( JSON.parse( json ) );
    await expect( readJsonResponse( response( 500, '<html>Fatal</html>' ) ) ).rejects.toMatchObject( { code: 'invalid_json', status: 500 } );
} );
