import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Catches render errors in a settings section so one bad field cannot blank the
 * whole tab. Shows the error inline and logs it for debugging.
 */
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
        console.error( 'WPUF settings render error:', error, info );
    }

    render() {
        if ( this.state.error ) {
            return (
                <div className="wpuf-mb-6 wpuf-rounded-md wpuf-bg-red-50 wpuf-px-4 wpuf-py-3 wpuf-text-sm wpuf-text-red-700">
                    { __( 'A field failed to render:', 'wp-user-frontend' ) }{ ' ' }
                    { String( this.state.error && this.state.error.message ) }
                </div>
            );
        }
        return this.props.children;
    }
}
