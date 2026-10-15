import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../../stores/settings/constants';
import { Button } from '@wpuf/components';
import MultiSelectChips from './MultiSelectChips';
import TextField from './TextField';
import WysiwygField from './WysiwygField';

/**
 * Role-based email template repeater. Replaces the legacy jQuery
 * `role-email-templates.js` (which only worked on the old settings DOM). For an
 * email type it lets you add per-role templates (roles + subject + body).
 *
 * Data lives in the `role_email_templates` side-channel (`extra`), keyed by
 * email type → array of `{ roles, subject, body }`, mirroring the Pro option
 * `wpuf_role_based_email_templates`. Saved server-side via `wpuf_settings_saved`.
 * Buttons on the shared Button since 4.6b.
 */
const TEMPLATES_KEY = 'role_email_templates';
const ROLES_KEY = 'email_role_options';

export default function RoleEmailTemplates( { field } ) {
    const emailType = field.email_type || '';

    const { all, roles } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            all: store.getExtraValue( TEMPLATES_KEY ) || {},
            roles: store.getExtraValue( ROLES_KEY ) || {},
        };
    }, [] );

    const { setExtraValue } = useDispatch( STORE_NAME );

    const rows = Array.isArray( all[ emailType ] ) ? all[ emailType ] : [];

    const commit = ( nextRows ) => setExtraValue( TEMPLATES_KEY, { ...all, [ emailType ]: nextRows } );
    const update = ( i, patch ) => commit( rows.map( ( r, idx ) => ( idx === i ? { ...r, ...patch } : r ) ) );
    const add = () => commit( [ ...rows, { roles: [], subject: '', body: '' } ] );
    const remove = ( i ) => commit( rows.filter( ( _, idx ) => idx !== i ) );

    return (
        <div className="mt-4">
            { rows.map( ( row, i ) => (
                <div key={ i } className="mb-3 rounded-lg border border-gray-200 p-4">
                    <div className="mb-2 flex items-center justify-between">
                        <span className="text-sm font-semibold text-gray-900">
                            { __( 'Role-specific Template', 'wp-user-frontend' ) } #{ i + 1 }
                        </span>
                        <Button variant="link" size="sm" className="text-red-600 font-medium enabled:hover:text-red-700" onClick={ () => remove( i ) }>
                            { __( 'Remove', 'wp-user-frontend' ) }
                        </Button>
                    </div>
                    <MultiSelectChips
                        field={ { label: __( 'Roles', 'wp-user-frontend' ), options: roles } }
                        name={ `roles_${ i }` }
                        value={ row.roles || [] }
                        onChange={ ( n, val ) => update( i, { roles: val } ) }
                    />
                    <TextField
                        field={ { label: __( 'Subject', 'wp-user-frontend' ) } }
                        name={ `subject_${ i }` }
                        value={ row.subject || '' }
                        onChange={ ( n, val ) => update( i, { subject: val } ) }
                    />
                    <WysiwygField
                        field={ { label: __( 'Body', 'wp-user-frontend' ) } }
                        name={ `wpuf_role_tpl_${ emailType }_${ i }` }
                        value={ row.body || '' }
                        onChange={ ( n, val ) => update( i, { body: val } ) }
                    />
                </div>
            ) ) }
            <Button variant="secondary" className="font-medium" onClick={ add }>
                + { __( 'Add New', 'wp-user-frontend' ) }
            </Button>
        </div>
    );
}
