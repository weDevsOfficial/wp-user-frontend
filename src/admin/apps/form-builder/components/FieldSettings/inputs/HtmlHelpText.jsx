/**
 * Static HTML help text for field settings.
 * Replaces Vue field-html_help_text component.
 *
 * Simply renders the option_field.text as raw HTML.
 */
export default function HtmlHelpText( { optionField } ) {
    return (
        <div
            className="panel-field-opt panel-field-html-help-text"
            dangerouslySetInnerHTML={ { __html: optionField.text || '' } }
        />
    );
}
