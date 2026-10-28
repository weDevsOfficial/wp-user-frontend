/**
 * Keeps a crash in one screen part from blanking the whole screen.
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default class ErrorBoundary extends Component {
    constructor( props ) {
        super( props );
        this.state = { error: null };
    }

    static getDerivedStateFromError( error ) {
        return { error };
    }

    componentDidCatch( error, info ) {
        // eslint-disable-next-line no-console
        console.error( '[wpuf]', error, info?.componentStack );
    }

    render() {
        if ( ! this.state.error ) {
            return this.props.children;
        }

        if ( this.props.fallback ) {
            return this.props.fallback;
        }

        return (
            <div data-wpuf-ui="" className="notice notice-error inline" role="alert">
                <p>{ __( 'Something went wrong while loading this part of the screen. Reload the page to try again.', 'wp-user-frontend' ) }</p>
            </div>
        );
    }
}
