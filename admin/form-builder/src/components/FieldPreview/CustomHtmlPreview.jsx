export default function CustomHtmlPreview( { field } ) {
    return (
        <div className="wpuf-fields">
            <div dangerouslySetInnerHTML={ { __html: field.html || '' } } />
        </div>
    );
}
