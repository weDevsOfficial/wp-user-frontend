/**
 * Catalogue of every post-form field the builder offers.
 *
 * `slug`     — the builder's `data-form-field` value (also the stage class suffix)
 * `label`    — default field label, which the frontend prints as `data-label`
 * `group`    — panel section the button lives in
 * `pro`      — ships with wpuf-pro (absent on Lite / unlicensed installs)
 * `control`  — CSS for the control the frontend must render inside the field row
 * `envDep`   — needs external config (API keys, extra plugin, payments) before the
 *              builder will even add it, so it is reported instead of asserted
 * `noRow`    — does not render its own labelled row (step markers, hooks)
 */
export interface FieldType {
    slug: string;
    /** stage class suffix when it differs from the panel slug (taxonomy fields) */
    stage?: string;
    /** rendered without a matchable data-label row */
    frontendSkip?: boolean;
    /** known product defect — asserted separately with test.fail() */
    knownBug?: string;
    label: string;
    group: 'Post Fields' | 'Taxonomies' | 'Custom Fields' | 'Pricing Fields' | 'Others';
    pro?: boolean;
    control?: string;
    envDep?: boolean;
    noRow?: boolean;
}

export const FieldTypes: FieldType[] = [
    // Post Fields
    { slug: 'post_title', label: 'Post Title', group: 'Post Fields', control: 'input[type="text"]' },
    { slug: 'post_content', label: 'Post Content', group: 'Post Fields', control: 'textarea, iframe' },
    { slug: 'post_excerpt', label: 'Post Excerpt', group: 'Post Fields', control: 'textarea' },
    { slug: 'featured_image', label: 'Featured Image', group: 'Post Fields', control: 'input[type="file"], .file-selector, .wpuf-file-upload-zone' },

    // Taxonomies
    { slug: 'category', stage: 'taxonomy', label: 'Category', group: 'Taxonomies', control: 'select, input[type="checkbox"]' },
    { slug: 'post_tag', stage: 'post_tags', label: 'Tags', group: 'Taxonomies', control: 'input, select' },

    // Custom Fields
    { slug: 'text_field', label: 'Text', group: 'Custom Fields', control: 'input[type="text"]' },
    { slug: 'textarea_field', label: 'Textarea', group: 'Custom Fields', control: 'textarea' },
    { slug: 'dropdown_field', label: 'Dropdown', group: 'Custom Fields', control: 'select' },
    { slug: 'multiple_select', label: 'Multi Select', group: 'Custom Fields', control: 'select[multiple], .custom-multiselect' },
    { slug: 'radio_field', label: 'Radio', group: 'Custom Fields', control: 'input[type="radio"]' },
    { slug: 'checkbox_field', label: 'Checkbox', group: 'Custom Fields', control: 'input[type="checkbox"]' },
    { slug: 'website_url', label: 'Website URL', group: 'Custom Fields', control: 'input' },
    { slug: 'email_address', label: 'Email Address', group: 'Custom Fields', control: 'input' },
    { slug: 'custom_hidden_field', label: 'Hidden Field', group: 'Custom Fields', noRow: true },
    { slug: 'image_upload', label: 'Image Upload', group: 'Custom Fields', control: 'input[type="file"], .file-selector, .wpuf-file-upload-zone' },
    { slug: 'repeat_field', label: 'Repeat Field', group: 'Custom Fields', pro: true, control: 'input', knownBug: 'saved as input_type "repeat" but registered as "repeat_field" — never rendered (BUGS-FOUND.md)' },
    { slug: 'date_field', label: 'Date / Time', group: 'Custom Fields', pro: true, control: 'input, select' },
    { slug: 'time_field', label: 'Time Field', group: 'Custom Fields', pro: true, control: 'select, input' },
    { slug: 'file_upload', label: 'File Upload', group: 'Custom Fields', pro: true, control: 'input[type="file"], .file-selector, .wpuf-file-upload-zone' },
    { slug: 'country_list_field', label: 'Country List', group: 'Custom Fields', pro: true, control: 'select' },
    { slug: 'numeric_text_field', label: 'Numeric Field', group: 'Custom Fields', pro: true, control: 'input' },
    { slug: 'phone_field', label: 'Phone Field', group: 'Custom Fields', pro: true, control: 'input' },
    { slug: 'address_field', label: 'Address Field', group: 'Custom Fields', pro: true, control: 'input, select' },
    { slug: 'google_map', label: 'Google Map', group: 'Custom Fields', pro: true, envDep: true, control: 'input, div' },
    { slug: 'step_start', label: 'Step Start', group: 'Custom Fields', pro: true, noRow: true },
    { slug: 'embed', label: 'Embed', group: 'Custom Fields', pro: true, control: 'input, textarea' },

    // Pricing Fields
    { slug: 'price_field', label: 'Price', group: 'Pricing Fields', pro: true, control: 'input' },
    { slug: 'pricing_checkbox', label: 'Pricing Checkbox', group: 'Pricing Fields', pro: true, control: 'input[type="checkbox"]' },
    { slug: 'pricing_radio', label: 'Pricing Radio', group: 'Pricing Fields', pro: true, control: 'input[type="radio"]' },
    { slug: 'pricing_dropdown', label: 'Pricing Dropdown', group: 'Pricing Fields', pro: true, control: 'select' },
    { slug: 'pricing_multiselect', label: 'Pricing Multi-Select', group: 'Pricing Fields', pro: true, control: 'select[multiple], .custom-multiselect' },
    { slug: 'cart_total', label: 'Total', group: 'Pricing Fields', pro: true, envDep: true },

    // Others
    { slug: 'column_field', label: 'Columns', group: 'Others', pro: true, noRow: true },
    { slug: 'section_break', label: 'Section Break', group: 'Others' },
    { slug: 'custom_html', label: 'Custom HTML', group: 'Others' },
    { slug: 'recaptcha', label: 'reCaptcha', group: 'Others', envDep: true },
    { slug: 'cloudflare_turnstile', label: 'Cloudflare Turnstile', group: 'Others', envDep: true },
    { slug: 'shortcode', label: 'Shortcode', group: 'Others', noRow: true },
    { slug: 'action_hook', label: 'YOUR_CUSTOM_HOOK_NAME', group: 'Others', noRow: true },
    { slug: 'toc', label: '', frontendSkip: true, group: 'Others', pro: true, control: 'input[type="checkbox"], .wpuf-toc' },
    { slug: 'ratings', label: 'Ratings', group: 'Others', pro: true, control: 'input, .wpuf-ratings, .star' },
    { slug: 'really_simple_captcha', label: 'Really Simple Captcha', group: 'Others', pro: true, envDep: true },
    { slug: 'math_captcha', label: 'Math Captcha', group: 'Others', pro: true, control: 'input' },
];

export const fieldTypesByGroup = ( group: FieldType[ 'group' ] ): FieldType[] =>
    FieldTypes.filter( ( field ) => field.group === group );
