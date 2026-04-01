// DESCRIPTION: Editor component for subscription packs block.
// Renders InspectorControls sidebar and ServerSideRender preview.

import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps }>
            <Placeholder
                icon="cart"
                label={ __( 'Subscription Packs', 'wp-user-frontend' ) }
                instructions={ __( 'Displays your WPUF subscription packs.', 'wp-user-frontend' ) }
            />
        </div>
    );
}
