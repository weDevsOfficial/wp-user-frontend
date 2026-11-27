import copyMailTag from './copyMailTag';

// A tag element as the click handler sees it (no DOM in this Jest setup).
function fakeTag( text ) {
    const attributes = { 'data-clipboard-text': text };
    const classes = new Set();

    return {
        getAttribute: ( name ) => ( name in attributes ? attributes[ name ] : null ),
        setAttribute: ( name, value ) => {
            attributes[ name ] = value;
        },
        removeAttribute: ( name ) => {
            delete attributes[ name ];
        },
        hasAttribute: ( name ) => name in attributes,
        classList: {
            add: ( ...names ) => names.forEach( ( name ) => classes.add( name ) ),
            remove: ( ...names ) => names.forEach( ( name ) => classes.delete( name ) ),
        },
        classes,
    };
}

describe( 'copyMailTag', () => {
    let writeText;

    beforeEach( () => {
        jest.useFakeTimers();
        writeText = jest.fn( () => Promise.resolve() );
        global.window = { navigator: { clipboard: { writeText } } };
    } );

    afterEach( () => {
        jest.useRealTimers();
        delete global.window;
    } );

    it( 'copies the clicked tag and shows Copied! for a second', () => {
        const tag = fakeTag( '{post_title}' );

        copyMailTag( { target: { closest: () => tag } } );

        expect( writeText ).toHaveBeenCalledWith( '{post_title}' );
        expect( tag.getAttribute( 'data-original-title' ) ).toBe( 'Copied!' );
        expect( tag.classes.has( 'after:content-[attr(data-original-title)]' ) ).toBe( true );

        jest.advanceTimersByTime( 1000 );

        expect( tag.hasAttribute( 'data-original-title' ) ).toBe( false );
        expect( tag.classes.size ).toBe( 0 );
    } );

    it( 'ignores clicks outside a tag', () => {
        copyMailTag( { target: { closest: () => null } } );

        expect( writeText ).not.toHaveBeenCalled();
    } );
} );
