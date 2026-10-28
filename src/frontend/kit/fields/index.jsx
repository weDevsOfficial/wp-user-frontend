/**
 * The field registry: builder template => component. Pro and add-ons add
 * theirs with `registerField( template, Component )` (or the
 * `wpuf.frontend.form.fields` JS filter) before the form mounts; a
 * template nobody registered renders a plain text input with a console
 * note, so a form never breaks on an unknown field.
 *
 * @since WPUF_SINCE
 */
import { applyFilters } from '@wordpress/hooks';
import { CheckboxField, HiddenField, HtmlField, MultiSelectField, RadioField, SectionBreakField, SelectField, TagsField, TextareaField, TextField } from './basic';
import RichTextField from './RichText';
import TaxonomyField from './Taxonomy';
import UploadField from './Upload';
import { RecaptchaField, TurnstileField } from './Captcha';
import ColumnField from './Column';

/** A textarea field: the editor when `rich` is yes / teeny, a plain textarea otherwise. */
function TextareaOrRich( props ) {
    const rich = props.field.rich && 'no' !== props.field.rich;

    return rich ? <RichTextField { ...props } /> : <TextareaField { ...props } />;
}

/** Step markers are read by the form (multistep fieldsets), never rendered as a row. */
const Nothing = () => null;

const registry = {
    post_title: TextField,
    text_field: TextField,
    email_address: TextField,
    website_url: TextField,
    numeric_text_field: TextField,
    custom_hidden_field: HiddenField,
    textarea_field: TextareaOrRich,
    post_content: RichTextField,
    post_excerpt: TextareaField,
    post_tags: TagsField,
    dropdown_field: SelectField,
    multiple_select: MultiSelectField,
    radio_field: RadioField,
    checkbox_field: CheckboxField,
    custom_html: HtmlField,
    section_break: SectionBreakField,
    column_field: ColumnField,
    taxonomy: TaxonomyField,
    image_upload: UploadField,
    featured_image: UploadField,
    recaptcha: RecaptchaField,
    cloudflare_turnstile: TurnstileField,
    step_start: Nothing,
};

const warned = new Set();

function UnknownField( props ) {
    if ( ! warned.has( props.field.template ) ) {
        warned.add( props.field.template );
        // eslint-disable-next-line no-console
        console.warn( `[WPUF] No React field registered for "${ props.field.template }"; rendered as text.` );
    }

    return <TextField { ...props } />;
}

export function registerField( template, Component ) {
    registry[ template ] = Component;
}

export function fieldComponent( template ) {
    const all = applyFilters( 'wpuf.frontend.form.fields', registry );

    return all[ template ] || UnknownField;
}

export { registry };
