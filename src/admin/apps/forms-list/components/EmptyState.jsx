/**
 * EmptyState component — shown when there are no forms to display
 * (FlyHR empty state on the shared EmptyState, develop's texts; registration
 * texts from develop's Pro list).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Button, EmptyState as SharedEmptyState } from '@wpuf/components';
import { FileText, Inbox, SearchX, UserPlus } from 'lucide-react';
import CreateButtons from './CreateButtons';

const EmptyState = ( { type, formType = 'post', onAddNew, onAIFormBuilder, onClearSearch, onShowAll } ) => {
    if ( type === 'search' ) {
        return (
            <SharedEmptyState
                size="card"
                icon={ SearchX }
                title={ __( 'No forms found matching your search!', 'wp-user-frontend' ) }
                actions={ onClearSearch ? <Button size="sm" variant="secondary" onClick={ onClearSearch }>{ __( 'Clear search', 'wp-user-frontend' ) }</Button> : null }
            />
        );
    }

    if ( type === 'tab-empty' ) {
        return (
            <SharedEmptyState
                size="card"
                icon={ Inbox }
                title={ __( 'No Items Here!', 'wp-user-frontend' ) }
                description={ __( 'No forms have this status.', 'wp-user-frontend' ) }
                actions={ onShowAll ? <Button size="sm" variant="secondary" onClick={ onShowAll }>{ __( 'View all forms', 'wp-user-frontend' ) }</Button> : null }
            />
        );
    }

    return (
        <SharedEmptyState
            size="page"
            icon={ 'profile' === formType ? UserPlus : FileText }
            title={ 'profile' === formType
                ? __( 'No Registration Forms Created Yet', 'wp-user-frontend' )
                : __( 'No Post Forms Created Yet', 'wp-user-frontend' ) }
            description={ 'profile' === formType
                ? __( 'Create a registration form to allow users to sign up with custom fields and roles.', 'wp-user-frontend' )
                : __( 'Start building a post form to let users submit content from the frontend.', 'wp-user-frontend' ) }
            actions={ <CreateButtons onAddNew={ onAddNew } onAIFormBuilder={ onAIFormBuilder } /> }
        />
    );
};

export default EmptyState;
