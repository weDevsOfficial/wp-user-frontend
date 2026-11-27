/**
 * In place of the settings of a switched-off Pro module (IA `off`): what is
 * missing and where to turn it on, instead of a tab that just disappears.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Notice } from '@wpuf/components';
import { openRoute } from '../../../../app/client';
import { settingsData } from '../../boot';

/**
 * @param {Object} props
 * @param {string} props.title Module name.
 */
export default function ModuleOff( { title } ) {
    const modulesUrl = `${ settingsData().admin_url || '' }admin.php?page=wp-user-frontend#/modules`;

    return (
        <Notice
            tone="info"
            className="mb-6"
            /* translators: %s: module name */
            title={ sprintf( __( 'The %s module is off', 'wp-user-frontend' ), title ) }
            action={
                <Button size="sm" variant="secondary" onClick={ () => openRoute( '/modules', modulesUrl ) }>
                    { __( 'Open Modules', 'wp-user-frontend' ) }
                </Button>
            }
        >
            { __( 'Turn the module on in Modules to see and change its settings here.', 'wp-user-frontend' ) }
        </Notice>
    );
}
