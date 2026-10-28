/**
 * Pro field detection before "Edit with Builder" on a free site, ported from
 * develop's FormSuccessStage.vue (same identifiers, labels and icons).
 *
 * @since WPUF_SINCE
 */

const PRO_FIELDS = [
    'date_field', 'time_field', 'datetime_field',
    'numeric_text_field', 'numeric_field', 'phone_field', 'phone_number',
    'address_field', 'country_list_field', 'country_list', 'google_map',
    'multiple_select', 'multi_select', 'checkbox_grid', 'multiple_choice_grid',
    'file_upload', 'audio_upload', 'video_upload',
    'pricing_radio', 'pricing_checkbox', 'pricing_dropdown', 'pricing_multiselect', 'cart_total',
    'ratings', 'rating', 'linear_scale', 'qr_code', 'embed', 'shortcode', 'action_hook',
    'toc', 'terms_conditions', 'step_start', 'multistep', 'repeat_field', 'repeater',
    'really_simple_captcha', 'math_captcha',
];

const FREE_FIELDS = [
    'text', 'text_field', 'email', 'email_address', 'url', 'website_url',
    'textarea', 'textarea_field', 'dropdown', 'dropdown_field', 'select',
    'checkbox', 'checkbox_field', 'radio', 'radio_field', 'hidden', 'hidden_field',
    'html', 'section_break', 'post_title', 'post_content', 'post_excerpt',
    'post_tags', 'taxonomy', 'category', 'featured_image', 'image_upload',
    'recaptcha', 'recaptcha_v2', 'recaptcha_v3', 'cloudflare_turnstile',
    'user_login', 'user_email', 'user_url', 'first_name', 'last_name', 'nickname', 'display_name',
    'user_bio', 'biography', 'user_avatar', 'profile_photo', 'password',
    'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',
    'column_field', 'custom_html', 'custom_hidden_field',
];

const PRO_FIELD_LABELS = {
    date_field: 'Date Picker',
    time_field: 'Time Picker',
    datetime_field: 'Date & Time',
    numeric_text_field: 'Numeric Text',
    numeric_field: 'Numeric Text',
    phone_field: 'Phone Number',
    phone_number: 'Phone Number',
    phone: 'Phone Number',
    address_field: 'Address',
    address: 'Address',
    country_list_field: 'Country List',
    country_list: 'Country List',
    google_map: 'Google Map',
    map: 'Google Map',
    multiple_select: 'Multi Select',
    multi_select: 'Multi Select',
    checkbox_grid: 'Checkbox Grid',
    multiple_choice_grid: 'Multiple Choice Grid',
    file_upload: 'File Upload',
    file: 'File Upload',
    audio_upload: 'Audio Upload',
    audio: 'Audio Upload',
    video_upload: 'Video Upload',
    video: 'Video Upload',
    pricing_radio: 'Pricing Radio',
    pricing_checkbox: 'Pricing Checkbox',
    pricing_dropdown: 'Pricing Dropdown',
    pricing_multiselect: 'Pricing Multi Select',
    cart_total: 'Cart Total',
    ratings: 'Ratings',
    rating: 'Ratings',
    linear_scale: 'Linear Scale',
    qr_code: 'QR Code',
    embed: 'Embed',
    shortcode: 'Shortcode',
    action_hook: 'Action Hook',
    toc: 'Terms & Conditions',
    terms_conditions: 'Terms & Conditions',
    column_field: 'Column Field',
    column: 'Column Field',
    step_start: 'Multi-Step Start',
    multistep: 'Multi-Step Start',
    repeat_field: 'Repeat Field',
    repeater: 'Repeat Field',
    really_simple_captcha: 'Really Simple Captcha',
    captcha: 'Captcha',
    recaptcha: 'reCAPTCHA',
    recaptcha_v2: 'reCAPTCHA',
    recaptcha_v3: 'reCAPTCHA v3',
};

const ICONS = {
    date: 'clock',
    date_field: 'clock',
    time: 'clock',
    time_field: 'clock',
    datetime: 'clock',
    datetime_field: 'clock',
    address_field: 'map',
    country_list: 'globe-alt',
    country_list_field: 'globe-alt',
    google_map: 'location-marker',
    numeric_text_field: 'adjustments-horizontal',
    numeric_field: 'adjustments-horizontal',
    phone_field: 'phone',
    phone_number: 'phone',
    file_upload: 'arrow-up-tray',
    audio_upload: 'arrow-up-tray',
    video_upload: 'arrow-up-tray',
    pricing_radio: 'currency-dollar',
    pricing_checkbox: 'currency-dollar',
    pricing_dropdown: 'currency-dollar',
    pricing_multiselect: 'currency-dollar',
    cart_total: 'receipt-percent',
    ratings: 'star',
    rating: 'star',
    linear_scale: 'ellipsis-h',
    qr_code: 'qrcode',
    embed: 'code-bracket-square',
    shortcode: 'code-bracket-square',
    action_hook: 'command-line',
    toc: 'exclamation-circle',
    terms_conditions: 'exclamation-circle',
    step_start: 'play',
    multistep: 'play',
    repeat_field: 'rectangle-stack',
    repeater: 'rectangle-stack',
    really_simple_captcha: 'document-check',
    captcha: 'document-check',
    math_captcha: 'check-circle',
    checkbox_grid: 'th',
    multiple_choice_grid: 'braille',
    column_field: 'th',
    multiple_select: 'squares-2x2',
    multi_select: 'squares-2x2',
};

/**
 * The fields that need Pro (none when every field is free).
 *
 * @param {Array} fields Preview fields.
 *
 * @return {Array} Pro fields.
 */
export function findProFields( fields ) {
    return ( fields || [] ).filter( ( field ) => {
        const keys = [ field.type, field.input_type, field.template ].map( ( key ) => String( key || '' ).toLowerCase() );

        if ( FREE_FIELDS.some( ( free ) => keys.includes( free ) ) ) {
            return false;
        }

        return PRO_FIELDS.some( ( pro ) => keys.includes( pro ) );
    } );
}

/**
 * Up to four distinct Pro field types with label and icon URL.
 *
 * @param {Array}  proFields Pro fields.
 * @param {string} assetUrl  Plugin asset URL.
 *
 * @return {Array} { key, label, icon }.
 */
export function proFieldItems( proFields, assetUrl ) {
    const types = [ ...new Set( proFields.map( ( field ) => field.type || field.input_type || field.template || 'Unknown Field' ) ) ];

    return types.slice( 0, 4 ).map( ( type ) => {
        const key = String( type ).toLowerCase();
        const icon = ICONS[ key ];

        return {
            key,
            label: PRO_FIELD_LABELS[ key ] || PRO_FIELD_LABELS[ type ] || type,
            icon: icon && assetUrl ? `${ assetUrl }/images/${ icon }.svg` : '',
        };
    } );
}
