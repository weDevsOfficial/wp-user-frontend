import { optionProblems } from './saveValidation';

describe( 'optionProblems', () => {
    test( 'empty and repeated option values are reported', () => {
        expect( optionProblems( [ { value: 'a' }, { value: 'b' } ] ) ).toEqual( [] );
        expect( optionProblems( [ { value: 'a' }, { value: '' } ] ) ).toHaveLength( 1 );
        expect( optionProblems( [ { value: 'a' }, { value: 'a ' } ] )[ 0 ] ).toContain( '"a"' );
    } );
} );
