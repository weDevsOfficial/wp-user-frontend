import publish from './publish';

beforeEach( () => {
    global.window = {};
} );

test( 'adds members and keeps the ones already there', () => {
    const existing = () => 'builder';
    window.wpuf = { registerFieldPreview: existing, hooks: { useFieldThing: 1 } };

    publish( { registerFieldPreview: () => 'other', api: { request: 1 }, hooks: { useBoot: 2, useFieldThing: 9 } } );

    expect( window.wpuf.registerFieldPreview ).toBe( existing );
    expect( window.wpuf.api ).toEqual( { request: 1 } );
    expect( window.wpuf.hooks ).toEqual( { useFieldThing: 1, useBoot: 2 } );
} );

test( 'creates window.wpuf when missing', () => {
    publish( { runtimeVersion: '1.0' } );
    expect( window.wpuf.runtimeVersion ).toBe( '1.0' );
} );
