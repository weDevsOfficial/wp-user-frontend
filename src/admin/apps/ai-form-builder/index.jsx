/**
 * Entry point of the AI Form Builder React app (mount #wpuf-ai-form-builder,
 * printed by Admin\Screens\AiFormBuilder, or the admin app's AI routes).
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { doAction } from '@wordpress/hooks';
import { WpufProviders } from '@wpuf/components';

import AIFormBuilderApp from './AIFormBuilderApp';
import { registerScreen } from '../../app/client';

/**
 * Stage keys of the route query (the old page read them from its URL).
 *
 * @param {Object} query Route query.
 *
 * @return {Object} wpufAIFormBuilder stage keys.
 */
const stageFromQuery = ( query ) => ( {
    stage: query.stage || 'input',
    description: query.description || '',
    prompt: query.prompt || '',
    formId: query.form_id || '',
    formTitle: query.form_title || '',
} );

registerScreen( 'ai-form-builder', [ 'wpuf-ai-form-builder' ], ( element, context ) => {
    if ( context ) {
        window.wpufAIFormBuilder = { ...( window.wpufAIFormBuilder || {} ), ...stageFromQuery( context.query ) };
    }

    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <AIFormBuilderApp />
        </WpufProviders>
    );

    return () => root.unmount();
} );

doAction( 'wpuf.aiFormBuilder.init' );
