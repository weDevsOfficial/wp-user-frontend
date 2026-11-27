import { generateFieldId } from '../store/reducer';

/**
 * Create a new field from a template definition.
 * Deep clones field_props, assigns a random ID, and auto-generates a name.
 *
 * @param {string} template      Field template name (e.g. 'text_field')
 * @param {Object} fieldSettings The full fieldSettings object from the store
 * @param {Array}  existingFields Current form fields (for name collision check)
 * @param {Object} options        `{ innerField: true }` for column / repeat inner fields
 * @return {Object} New field object ready to add to the form
 */
export function createField( template, fieldSettings, existingFields = [], options = {} ) {
    const definition = fieldSettings[ template ];

    if ( ! definition || ! definition.field_props ) {
        return null;
    }

    const field = JSON.parse( JSON.stringify( definition.field_props ) );
    field.id = generateFieldId();
    field.is_new = true;

    if ( options.innerField ) {
        // Column and repeat inner fields: only meta fields get a name, always with
        // a random suffix, as the Vue column / repeat components did.
        if ( 'yes' === field.is_meta && ! field.name && field.label ) {
            field.name = field.label.replace( /\W/g, '_' ).toLowerCase() + '_' + generateFieldId();
        }
    } else if ( ! field.name && field.label ) {
        field.name = field.label.replace( /\W/g, '_' ).toLowerCase();

        const sameTemplateCount = existingFields.filter(
            ( f ) => f.template === template
        ).length;

        if ( sameTemplateCount > 0 ) {
            field.name += '_' + sameTemplateCount;
        }
    }

    return field;
}

/**
 * Check if a field template allows only a single instance per form.
 *
 * @param {string} template       Field template name
 * @param {Array}  singleObjects  List of single-instance template names from wpuf_single_objects
 * @return {boolean}
 */
export function isFieldSingleInstance( template, singleObjects ) {
    if ( ! singleObjects || ! Array.isArray( singleObjects ) ) {
        return false;
    }
    return singleObjects.includes( template );
}

/**
 * Check if the form already contains a field with the given template or name.
 * Searches top-level fields, column inner_fields (object), and repeat inner_fields (array).
 *
 * @param {Array}  fields    Form fields array
 * @param {string} fieldName Template or field name to search for
 * @return {boolean}
 */
export function containsField( fields, fieldName ) {
    for ( const field of fields ) {
        if ( field.template === fieldName || field.name === fieldName ) {
            return true;
        }

        // Column field: inner_fields is object
        if ( field.template === 'column_field' && field.inner_fields ) {
            for ( const col in field.inner_fields ) {
                if ( ! field.inner_fields.hasOwnProperty( col ) ) {
                    continue;
                }
                for ( const innerField of field.inner_fields[ col ] ) {
                    if ( innerField.template === fieldName ) {
                        return true;
                    }
                }
            }
        }

        // Repeat field: inner_fields is array
        if ( field.template === 'repeat_field' && Array.isArray( field.inner_fields ) ) {
            for ( const innerField of field.inner_fields ) {
                if ( innerField.template === fieldName ) {
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * Find a field by ID across all field levels.
 * Searches top-level, column inner_fields (object), and repeat inner_fields (array).
 *
 * @param {Array}  fields Form fields array
 * @param {number} id     Field ID to find
 * @return {Object|null}
 */
export function findFieldById( fields, id ) {
    const targetId = parseInt( id );

    for ( const field of fields ) {
        if ( parseInt( field.id ) === targetId ) {
            return field;
        }

        if ( field.template === 'column_field' && field.inner_fields ) {
            for ( const col in field.inner_fields ) {
                if ( ! field.inner_fields.hasOwnProperty( col ) ) {
                    continue;
                }
                for ( const innerField of field.inner_fields[ col ] ) {
                    if ( parseInt( innerField.id ) === targetId ) {
                        return innerField;
                    }
                }
            }
        }

        if ( field.template === 'repeat_field' && Array.isArray( field.inner_fields ) ) {
            for ( const innerField of field.inner_fields ) {
                if ( parseInt( innerField.id ) === targetId ) {
                    return innerField;
                }
            }
        }
    }

    return null;
}

/**
 * Whether a textarea / post content / excerpt / bio field renders the rich
 * editor: only `yes` and `teeny`, as the frontend (Form_Field_Textarea,
 * Form_Field_Post_Content). The builder preview follows it (R14: develop's
 * preview showed the editor for any value but `no`, a missing one included).
 *
 * @since WPUF_SINCE
 *
 * @param {*} rich Stored `rich` value.
 *
 * @return {boolean} Editor.
 */
export function isRichEditor( rich ) {
    return 'yes' === rich || 'teeny' === rich;
}

/**
 * PHP truthiness of a stored value (`'0'`, `''`, `0`, `[]`, `false`, null are
 * off), for previews of options the frontend tests with `if ( $value )`.
 *
 * @since WPUF_SINCE
 *
 * @param {*} value Stored value.
 *
 * @return {boolean} On.
 */
export function isPhpTruthy( value ) {
    if ( Array.isArray( value ) ) {
        return value.length > 0;
    }

    if ( value && 'object' === typeof value ) {
        return Object.keys( value ).length > 0;
    }

    return ! ( false === value || null === value || undefined === value || '' === value || '0' === value || 0 === value );
}
