/**
 * Status of a payment row (kit Badge).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Badge } from '@wpuf/components';

const TONE = {
    completed: 'success',
    pending: 'warning',
};

const LABEL = {
    completed: __( 'Completed', 'wp-user-frontend' ),
    pending: __( 'Pending', 'wp-user-frontend' ),
};

/**
 * @param {Object} props
 * @param {string} props.status Row status.
 */
export default function StatusBadge( { status } ) {
    return (
        <Badge tone={ TONE[ status ] || 'neutral' }>
            { LABEL[ status ] || ( status ? status.charAt( 0 ).toUpperCase() + status.slice( 1 ) : '' ) }
        </Badge>
    );
}
