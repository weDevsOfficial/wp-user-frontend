import { generateFieldId } from '../store/reducer';

/**
 * Create a new field from a template definition.
 * Deep clones field_props, assigns a random ID, and auto-generates a name.
 *
 * @param {string} template      Field template name (e.g. 'text_field')
 * @param {Object} fieldSettings The full fieldSettings object from the store
 * @param {Array}  existingFields Current form fields (for name collision check)
 * @return {Object} New field object ready to add to the form
 */
export function createField( template, fieldSettings, existingFields = [] ) {
    const definition = fieldSettings[ template ];

    if ( ! definition || ! definition.field_props ) {
        return null;
    }

    const field = JSON.parse( JSON.stringify( definition.field_props ) );
    field.id = generateFieldId();
    field.is_new = true;

    if ( ! field.name && field.label ) {
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
