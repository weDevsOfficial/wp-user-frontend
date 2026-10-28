/**
 * Field helpers of the AI form builder, ported from develop's
 * FormSuccessStage.vue / AIFormBuilder.vue: preview conversion, the WPUF field
 * shape sent back to the API, and which preview a field gets. The shapes are
 * unchanged so the generate / create-form requests stay byte-compatible.
 *
 * @since WPUF_SINCE
 */

const DEFAULT_COND = () => ( {
    condition_status: 'no',
    cond_field: [],
    cond_operator: [ '=' ],
    cond_option: [ '- Select -' ],
    cond_logic: 'all',
} );

const DEFAULT_VISIBILITY = () => ( { selected: 'everyone', choices: [] } );

// Profile fields with fixed name / meta key (same as the PHP Field_Templates).
const FIXED_META = {
    gender: 'wpuf_gender',
    gender_field: 'wpuf_gender',
    facebook_url: 'wpuf_social_facebook',
    twitter_url: 'wpuf_social_twitter',
    instagram_url: 'wpuf_social_instagram',
    linkedin_url: 'wpuf_social_linkedin',
    profile_photo: 'wpuf_profile_photo',
};

const INPUT_TYPES = {
    text_field: 'text',
    email_address: 'email',
    website_url: 'url',
    numeric_text_field: 'number',
    phone_field: 'tel',
    textarea_field: 'textarea',
    dropdown_field: 'select',
    radio_field: 'radio',
    checkbox_field: 'checkbox',
    multiple_select: 'multiselect',
    file_upload: 'file_upload',
    date_field: 'date',
    time_field: 'time',
    address_field: 'address_field',
    country_list_field: 'select',
    toc: 'checkbox',
    google_map: 'google_map',
    ratings: 'ratings',
};

/**
 * Field type for the preview: input_type, then template, then type.
 *
 * @param {Object} field Field.
 *
 * @return {string} Type.
 */
export function getWPUFFieldType( field ) {
    return field.input_type || field.template || field.type || 'text';
}

/**
 * Options as `[ { value, label } ]`, from an object or an array.
 *
 * @param {*} options Options.
 *
 * @return {Array} Options.
 */
export function normalizeOptions( options ) {
    if ( ! options ) {
        return [];
    }

    if ( Array.isArray( options ) ) {
        return options.map( ( option ) => {
            if ( 'string' === typeof option ) {
                return { value: option, label: option };
            }

            return {
                value: option.value || option.key || option.label,
                label: option.label || option.value || option,
            };
        } );
    }

    if ( 'object' === typeof options ) {
        return Object.entries( options ).map( ( [ value, label ] ) => ( { value, label } ) );
    }

    return [];
}

/**
 * Options as WPUF's `{ value: label }` object.
 *
 * @param {*} options Options (object, array or "value|label" lines).
 *
 * @return {Object} Options.
 */
function toOptionsObject( options ) {
    if ( 'object' === typeof options && ! Array.isArray( options ) ) {
        return options;
    }

    const result = {};

    if ( Array.isArray( options ) ) {
        options.forEach( ( option ) => {
            if ( option && 'object' === typeof option && option.value && option.label ) {
                result[ option.value ] = option.label;
            } else if ( 'string' === typeof option ) {
                result[ option ] = option;
            }
        } );

        return result;
    }

    if ( 'string' === typeof options ) {
        options.split( '\n' ).forEach( ( line ) => {
            if ( line && line.includes( '|' ) ) {
                const [ value, label ] = line.split( '|' );

                if ( value && label ) {
                    result[ value.trim() ] = label.trim();
                }
            } else if ( line && line.trim() ) {
                result[ line.trim() ] = line.trim();
            }
        } );

        return result;
    }

    return {};
}

/**
 * API fields to the preview shape (develop's convertFieldsToPreview).
 *
 * @param {Array} apiFields Fields from the API.
 *
 * @return {Array} Preview fields.
 */
export function convertFieldsToPreview( apiFields ) {
    return ( apiFields || [] ).map( ( field, index ) => {
        const converted = {
            id: field.id || `field_${ index + 1 }`,
            type: field.type || field.template || field.input_type || 'text_field',
            input_type: field.input_type || field.type || 'text',
            template: field.template || field.type || 'text_field',
            label: field.label || field.name || 'Untitled Field',
            name: field.name || field.label?.toLowerCase().replace( /\s+/g, '_' ) || `field_${ index + 1 }`,
            placeholder: field.placeholder || field.help || '',
            required: 'yes' === field.required || true === field.required || 'true' === field.required,
            default: field.default || '',
            help_text: field.help || field.description || '',
            is_meta: field.is_meta || 'yes',
            size: field.size || '40',
            width: field.width || 'large',
            css: field.css || '',
            shortcode: field.shortcode || '',
            wpuf_cond: field.wpuf_cond || DEFAULT_COND(),
            wpuf_visibility: field.wpuf_visibility || DEFAULT_VISIBILITY(),
        };

        const fixed = FIXED_META[ converted.template || '' ];

        if ( fixed ) {
            converted.name = fixed;
            converted.meta_key = fixed;
        }

        if ( [ 'textarea', 'post_content', 'post_excerpt' ].includes( field.template ) || 'textarea' === field.type ) {
            converted.rows = field.rows || '5';
            converted.cols = field.cols || '25';
            converted.rich = field.rich || 'no';

            if ( 'post_content' === field.template ) {
                converted.rich = field.rich || 'yes';
                converted.insert_image = field.insert_image || 'yes';
            }
        }

        if ( 'file_upload' === field.template || 'file' === field.template || 'file_upload' === field.type ) {
            converted.extension = field.extension || [];
            converted.max_size = field.max_size || '2048';
            converted.count = field.count || '1';
        }

        if ( undefined !== field.options && null !== field.options ) {
            converted.options = toOptionsObject( field.options );
        } else if ( [ 'radio_field', 'checkbox_field', 'dropdown_field' ].includes( converted.type ) ||
            [ 'radio', 'checkbox', 'select', 'dropdown' ].includes( converted.input_type ) ) {
            converted.options = {};
        }

        return converted;
    } );
}

/**
 * WPUF input type of a field template.
 *
 * @param {string} fieldType Template.
 *
 * @return {string} Input type.
 */
function mapToInputType( fieldType ) {
    return INPUT_TYPES[ fieldType ] || 'text';
}

/**
 * Whether a field stores post meta (not a core post field).
 *
 * @param {string} fieldName Name or label.
 *
 * @return {boolean} Meta.
 */
function shouldBeMeta( fieldName ) {
    return ! [ 'title', 'content', 'excerpt', 'author', 'category', 'tags' ].includes( fieldName?.toLowerCase() );
}

/**
 * A preview field in the full WPUF shape the API expects (develop's
 * callChatAPI mapping); fields that already have it pass through.
 *
 * @param {Object} field         Field.
 * @param {number} index         Position.
 * @param {*}      emptyOptions  Options when the field has none ({} for chat, [] for create).
 * @param {string} fallbackLabel Label when the field has none.
 *
 * @return {Object} Field.
 */
export function toWpufField( field, index, emptyOptions = {}, fallbackLabel = 'Field' ) {
    if ( field.input_type && field.template && field.wpuf_cond ) {
        return field;
    }

    const fieldType = field.type || 'text_field';

    return {
        id: field.id || `field_${ index + 1 }`,
        type: fieldType,
        input_type: mapToInputType( fieldType ),
        template: fieldType,
        required: true === field.required || 'yes' === field.required ? 'yes' : 'no',
        label: field.label || fallbackLabel,
        name: field.name || ( field.label ? field.label.toLowerCase().replace( /\s+/g, '_' ).replace( /[^a-z0-9_]/g, '' ) : `field_${ index + 1 }` ),
        meta_key: field.meta_key || '',
        is_meta: shouldBeMeta( field.name || field.label ) ? 'yes' : 'no',
        help: field.help_text || field.help || '',
        css: field.css || '',
        placeholder: field.placeholder || '',
        default: field.default || '',
        size: field.size || '40',
        width: field.width || 'large',
        options: field.options || emptyOptions,
        wpuf_cond: field.wpuf_cond || DEFAULT_COND(),
        wpuf_visibility: field.wpuf_visibility || DEFAULT_VISIBILITY(),
    };
}

/**
 * A field added by a chat `add_field` change (develop's formattedField).
 *
 * @param {Object} newField Field from the API.
 * @param {number} position Fields already in the form.
 *
 * @return {Object} Preview field.
 */
export function formatAddedField( newField, position ) {
    return {
        id: newField.id || `field_${ position + 1 }`,
        type: newField.type || 'text_field',
        label: newField.label || 'New Field',
        name: newField.name || newField.label?.toLowerCase().replace( /\s+/g, '_' ).replace( /[^a-z0-9_]/g, '' ) || 'new_field',
        required: newField.required || false,
        placeholder: newField.placeholder || '',
        help_text: newField.help || '',
        options: newField.options || [],
        default: newField.default || '',
    };
}

/**
 * `required` as "yes" / "no" (what "Edit with Builder" sends).
 *
 * @param {Array} fields Fields.
 *
 * @return {Array} Fields.
 */
export function normalizeRequired( fields ) {
    return fields.map( ( field ) => ( { ...field, required: 'yes' === field.required || true === field.required ? 'yes' : 'no' } ) );
}

/**
 * Columns of a column field preview, 1 to 3 (default 2).
 *
 * @param {Object} field Field.
 *
 * @return {number} Columns.
 */
export function getColumnCount( field ) {
    return Math.max( 1, Math.min( 3, parseInt( field.columns, 10 ) || 2 ) );
}

/**
 * Which preview a field gets, in develop's template order (first match wins).
 *
 * @param {Object} field Field.
 *
 * @return {string} Preview kind.
 */
export function previewKind( field ) {
    const type = getWPUFFieldType( field );

    if ( [ 'text', 'email', 'url', 'number', 'tel', 'post_title' ].includes( type ) ) {
        return 'text';
    }
    if ( [ 'select', 'dropdown', 'dropdown_field', 'gender_field' ].includes( type ) ) {
        return 'select';
    }
    if ( 'radio' === type ) {
        return 'radio';
    }
    if ( [ 'checkbox_field', 'checkbox' ].includes( field.type ) ) {
        return 'checkbox';
    }
    if ( [ 'pricing_radio', 'pricing_checkbox', 'pricing_dropdown', 'pricing_multiselect', 'cart_total' ].includes( type ) ) {
        return type;
    }
    if ( 'toc' === field.type ) {
        return 'toc';
    }
    if ( [ 'image_upload', 'file', 'featured_image', 'file_upload', 'avatar', 'profile_photo' ].includes( type ) ) {
        return 'file';
    }
    if ( [ 'textarea', 'post_content', 'post_excerpt' ].includes( type ) ) {
        return 'textarea';
    }
    if ( [ 'multiple_select', 'multiselect', 'multi_select', 'country_list_field' ].includes( type ) ) {
        return 'multiselect';
    }
    if ( 'date' === field.input_type || 'date_field' === field.template || [ 'date_field', 'time_field', 'date', 'time', 'datetime' ].includes( field.type ) ) {
        return 'date';
    }
    if ( [ 'ratings', 'linear_scale' ].includes( field.type ) ) {
        return 'rating';
    }
    if ( [ 'checkbox_grid', 'multiple_choice_grid' ].includes( field.type ) ) {
        return 'grid';
    }
    if ( [ 'google_map', 'address_field', 'embed', 'qr_code' ].includes( field.type ) ) {
        return 'special';
    }
    if ( [ 'really_simple_captcha', 'math_captcha' ].includes( field.type ) ) {
        return 'captcha';
    }
    if ( 'taxonomy' === field.type ) {
        return 'taxonomy';
    }
    if ( [ 'post_title', 'post_content', 'post_excerpt', 'post_tags' ].includes( field.type ) ) {
        return 'post';
    }
    if ( 'column_field' === field.type || 'column_field' === field.template ) {
        return 'column';
    }
    if ( [ 'section_break', 'step_start' ].includes( field.type ) ) {
        return 'layout';
    }
    if ( [ 'custom_html', 'shortcode', 'action_hook' ].includes( field.type ) ) {
        return 'custom';
    }

    return 'fallback';
}

/**
 * Display type used in the first chat summary (AIFormBuilder.vue).
 *
 * @param {Object} field Field.
 *
 * @return {string} Type.
 */
export function summaryFieldType( field ) {
    if ( 'post_title' === field.template || 'text' === field.input_type ) {
        return 'text_field';
    }
    if ( 'post_content' === field.template || 'textarea' === field.input_type ) {
        return 'textarea_field';
    }
    if ( 'select' === field.input_type || 'dropdown' === field.input_type ) {
        return 'dropdown_field';
    }
    if ( 'radio' === field.input_type ) {
        return 'radio_field';
    }
    if ( 'checkbox' === field.input_type ) {
        return 'checkbox_field';
    }
    if ( 'date' === field.input_type ) {
        return 'date_field';
    }
    if ( 'email' === field.input_type ) {
        return 'email_address';
    }

    return field.input_type || field.type || 'text_field';
}
