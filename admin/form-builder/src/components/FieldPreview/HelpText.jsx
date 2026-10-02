/**
 * Shared help text component for field previews.
 */
export default function HelpText( { text } ) {
    if ( ! text ) {
        return null;
    }

    return (
        <p
            className="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500"
            dangerouslySetInnerHTML={ { __html: text } }
        />
    );
}
