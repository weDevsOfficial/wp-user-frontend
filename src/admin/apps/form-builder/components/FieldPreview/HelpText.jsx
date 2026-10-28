/**
 * Shared help text component for field previews.
 *
 * Without `className` it is develop's paragraph; templates that printed
 * `<span class="wpuf-help">` pass `className="wpuf-help"`.
 */
export default function HelpText( { text, className } ) {
    if ( ! text ) {
        return null;
    }

    if ( className ) {
        return <span className={ className } dangerouslySetInnerHTML={ { __html: text } } />;
    }

    return (
        <p
            className="mt-2 mb-0 text-sm text-gray-500"
            dangerouslySetInnerHTML={ { __html: text } }
        />
    );
}
