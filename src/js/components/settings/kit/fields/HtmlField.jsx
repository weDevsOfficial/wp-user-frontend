/**
 * Static HTML field — covers the legacy `html` type (informational blocks).
 * Net-new for the settings screen.
 */
export default function HtmlField( { field } ) {
    const html = field.desc || field.default || field.html || '';

    if ( ! html ) {
        return null;
    }

    return (
        <div
            className="wpuf-text-sm wpuf-text-gray-600"
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={ { __html: html } }
        />
    );
}
