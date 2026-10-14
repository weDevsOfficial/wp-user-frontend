/**
 * "AI Form Builder" and "Add New" (title row and the no-forms state).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { AiButton, Button } from '@wpuf/components';

/**
 * @param {Object}        props
 * @param {Function}      props.onAddNew        Open the templates modal.
 * @param {Function|null} props.onAIFormBuilder Start the AI builder (null hides it).
 */
const CreateButtons = ( { onAddNew, onAIFormBuilder } ) => (
    <div className="flex gap-3 justify-center">
        { onAIFormBuilder && (
            <AiButton size="lg" onClick={ onAIFormBuilder }>
                { __( 'AI Form Builder', 'wp-user-frontend' ) }
            </AiButton>
        ) }
        { /* `new-wpuf-form`: legacy scripts open the templates modal from it. */ }
        <Button size="lg" onClick={ onAddNew } className="new-wpuf-form">
            <Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
            { __( 'Add New', 'wp-user-frontend' ) }
        </Button>
    </div>
);

export default CreateButtons;
