import { registerFieldPreview } from '../../extensions/registry';

// Post fields
import PostTitlePreview from './PostTitlePreview';
import PostContentPreview from './PostContentPreview';
import PostExcerptPreview from './PostExcerptPreview';
import FeaturedImagePreview from './FeaturedImagePreview';
import PostTagsPreview from './PostTagsPreview';

// Basic custom fields
import TextFieldPreview from './TextFieldPreview';
import TextareaPreview from './TextareaPreview';
import DropdownPreview from './DropdownPreview';
import MultiSelectPreview from './MultiSelectPreview';
import RadioPreview from './RadioPreview';
import CheckboxPreview from './CheckboxPreview';
import EmailPreview from './EmailPreview';
import WebsiteUrlPreview from './WebsiteUrlPreview';

// Advanced fields
import TaxonomyPreview from './TaxonomyPreview';
import ImageUploadPreview from './ImageUploadPreview';
import SectionBreakPreview from './SectionBreakPreview';
import CustomHtmlPreview from './CustomHtmlPreview';
import HiddenFieldPreview from './HiddenFieldPreview';
import RecaptchaPreview from './RecaptchaPreview';
import TurnstilePreview from './TurnstilePreview';
import ColumnFieldPreview from './ColumnFieldPreview';

/**
 * Register all free field preview components.
 * Template names match the PHP field_settings keys.
 */
export function registerFreeFieldPreviews() {
    // Post fields
    registerFieldPreview( 'post_title', PostTitlePreview );
    registerFieldPreview( 'post_content', PostContentPreview );
    registerFieldPreview( 'post_excerpt', PostExcerptPreview );
    registerFieldPreview( 'featured_image', FeaturedImagePreview );
    registerFieldPreview( 'post_tags', PostTagsPreview );

    // Basic custom fields
    registerFieldPreview( 'text_field', TextFieldPreview );
    registerFieldPreview( 'textarea_field', TextareaPreview );
    registerFieldPreview( 'dropdown_field', DropdownPreview );
    registerFieldPreview( 'multiple_select', MultiSelectPreview );
    registerFieldPreview( 'radio_field', RadioPreview );
    registerFieldPreview( 'checkbox_field', CheckboxPreview );
    registerFieldPreview( 'email_address', EmailPreview );
    registerFieldPreview( 'website_url', WebsiteUrlPreview );

    // Advanced fields
    registerFieldPreview( 'taxonomy', TaxonomyPreview );
    registerFieldPreview( 'image_upload', ImageUploadPreview );
    registerFieldPreview( 'section_break', SectionBreakPreview );
    registerFieldPreview( 'custom_html', CustomHtmlPreview );
    registerFieldPreview( 'custom_hidden_field', HiddenFieldPreview );
    registerFieldPreview( 'recaptcha', RecaptchaPreview );
    registerFieldPreview( 'cloudflare_turnstile', TurnstilePreview );

    // Layout fields
    registerFieldPreview( 'column_field', ColumnFieldPreview );
}
