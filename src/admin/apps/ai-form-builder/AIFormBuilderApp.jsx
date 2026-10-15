/**
 * AI form builder root: input -> generating -> success (develop's
 * AIFormBuilder.vue). Generation errors show develop's "Oops..." dialog and
 * return to the input; "Edit with Builder" creates the form and opens it.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { useRef, useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { Button, dialogs } from '@wpuf/components';

import { config, createForm, generateForm } from './api';
import { buildSummary, newSessionId } from './lib/chat';
import InputStage from './components/InputStage';
import ProcessingStage from './components/ProcessingStage';
import SuccessStage from './components/SuccessStage';
import { openRoute } from '../../app/client';

const DEFAULT_TITLE = 'Generated Form';

/**
 * Develop's "Oops..." for a failed generation (and its hint for requests that
 * are not about forms).
 *
 * @param {string}  message      Message.
 * @param {boolean} notAFormHint Show the "only form creation" hint.
 *
 * @return {Promise} Settles on close.
 */
function showGenerationError( message, notAFormHint ) {
    const i18n = config().i18n || {};

    return dialogs.oops( message, {
        // Develop's AI error title is orange (the builder's Oops alerts are green).
        title: <span style={ { color: '#FF9000' } }>{ __( 'Oops...', 'wp-user-frontend' ) }</span>,
        confirmText: i18n.tryAgain || __( 'Try Again', 'wp-user-frontend' ),
        className: 'wpuf-ai-error-dialog',
        children: notAFormHint ? (
            <div style={ { display: 'flex', alignItems: 'center', gap: 8, margin: '20px 28.8px 4px', padding: 16, color: '#1d4ed8', background: '#f0f9ff', border: '1px solid #dbeafe', borderRadius: 12, fontSize: 14, lineHeight: 1.5, textAlign: 'left' } }>
                { i18n.nonFormRequest || __( 'I can only help with form creation. Try: "Create a contact form"', 'wp-user-frontend' ) }
            </div>
        ) : null,
    } );
}

/**
 * "Pro Feature Required" for a `pro_field_requested` warning (kept from
 * develop; the form is still generated).
 *
 * @param {string} message Warning.
 *
 * @return {Promise} Settles on close.
 */
function showProFieldWarning( message ) {
    const i18n = config().i18n || {};

    return dialogs.alert( {
        title: i18n.proFieldWarning || __( 'Pro Feature Required', 'wp-user-frontend' ),
        message: i18n.proFieldMessage || __( 'This field type requires WP User Frontend Pro. You can continue without it or upgrade to Pro for full functionality.', 'wp-user-frontend' ),
        icon: 'warning',
        confirmText: i18n.continueWithoutPro || __( 'Continue without Pro', 'wp-user-frontend' ),
        tone: 'primary',
        showClose: true,
        className: 'wpuf-ai-pro-warning-dialog',
        children: message ? <div style={ { padding: '10px 12px', borderRadius: 8, background: '#f9fafb', border: '1px solid #e5e7eb', fontSize: 13, lineHeight: '20px', color: '#4b5563' } }>{ message }</div> : null,
        // A plugin-ui outline button beside the action (was a WordPress-blue link button).
        extraActions: (
            <Button variant="secondary" href="https://wedevs.com/wp-user-frontend-pro/pricing/" target="_blank" rel="noreferrer">
                { i18n.upgradeToPro || __( 'Upgrade to Pro', 'wp-user-frontend' ) }
            </Button>
        ),
    } );
}

/**
 * Stage data the server passed (description / prompt prefill).
 *
 * @return {Object} { description, promptId }.
 */
function initialInput() {
    const data = config();

    return { description: data.description || '', promptId: data.prompt || '' };
}

/**
 * The app.
 */
/**
 * Open a form's builder: its admin app route, or the builder page.
 *
 * @param {string} formType  post|profile|registration.
 * @param {number} id        Form ID.
 * @param {string} [pageUrl] Builder page URL (create-form's edit_url).
 */
const openBuilder = ( formType, id, pageUrl ) => {
    const profile = 'profile' === formType || 'registration' === formType;
    const page = profile ? 'wpuf-profile-forms' : 'wpuf-post-forms';

    openRoute( `/${ profile ? 'registration' : 'post' }-forms/${ id }/edit`, pageUrl || `admin.php?page=${ page }&action=edit&id=${ id }` );
};

export default function AIFormBuilderApp() {
    const start = initialInput();
    const [ stage, setStage ] = useState( 'input' );
    const [ input, setInput ] = useState( { description: start.description, promptId: start.promptId, integration: '' } );
    const [ generating, setGenerating ] = useState( false );
    const [ responded, setResponded ] = useState( false );
    const [ generated, setGenerated ] = useState( null );
    const [ title, setTitle ] = useState( config().formTitle || DEFAULT_TITLE );
    const [ saving, setSaving ] = useState( false );
    const session = useRef( null );
    const formId = useRef( config().formId || '' );

    const sessionId = () => {
        if ( ! session.current ) {
            session.current = newSessionId( 'wpuf_ai_session' );
        }

        return session.current;
    };

    const fail = ( message, notAFormHint = false ) => {
        setGenerating( false );
        setResponded( false );
        setStage( 'input' );
        showGenerationError( message, notAFormHint );
    };

    const generate = async ( { description, promptId, integration } ) => {
        setInput( { description, promptId, integration: integration || '' } );
        setGenerating( true );
        setResponded( false );
        setStage( 'generating' );

        let result;

        try {
            result = await generateForm( { prompt: description, sessionId: sessionId(), integration } );
        } catch ( error ) {
            const message = 'TypeError' === error.name
                ? __( 'Cannot connect to server. Please check if WordPress REST API is accessible.', 'wp-user-frontend' )
                : error.message || __( 'Network error occurred', 'wp-user-frontend' );

            fail( message );
            return;
        }

        if ( result.success ) {
            const data = result.data || {};

            setGenerated( data );
            setTitle( data.form_title || DEFAULT_TITLE );

            if ( data.integration ) {
                setInput( ( current ) => ( { ...current, integration: data.integration } ) );
            }

            setResponded( true );
            return;
        }

        if ( 'invalid_request' === result.code || 'generation_failed' === result.code ) {
            fail( result.message || __( 'Form generation failed', 'wp-user-frontend' ), true );
            return;
        }

        if ( result.warning && 'pro_field_requested' === result.warning_type && result.form_data ) {
            setGenerated( result.form_data );
            setTitle( result.form_data.form_title || DEFAULT_TITLE );
            setResponded( true );

            if ( ! config().isProActive ) {
                setTimeout( () => showProFieldWarning( result.message ), 500 );
            }

            return;
        }

        fail( result.message || __( 'Form generation failed', 'wp-user-frontend' ) );
    };

    const finishGenerating = () => {
        setGenerating( false );
        setStage( 'success' );
    };

    const fields = generated ? generated.wpuf_fields || generated.fields || [] : [];

    const initialMessages = () => {
        const messages = [
            { type: 'user', content: input.description || 'Create a form' },
            { type: 'ai', content: '...' },
        ];

        if ( generated ) {
            const summary = buildSummary( title, fields, generated.form_description );

            messages.push( { type: 'ai', content: summary.content, summary, showButtons: false, status: __( 'Successfully created the form.', 'wp-user-frontend' ) } );
        }

        return messages;
    };

    const editInBuilder = async ( editedFields ) => {
        const formType = config().formType || 'post';

        if ( formId.current ) {
            openBuilder( formType, formId.current );
            return;
        }

        /**
         * Filters the form data sent to create-form from the AI form builder.
         *
         * @param {Object} formData { form_title, form_description, wpuf_fields, form_settings }.
         */
        const formData = applyFilters( 'wpuf.aiFormBuilder.createFormData', {
            form_title: title,
            form_description: generated?.form_description || '',
            wpuf_fields: editedFields,
            form_settings: generated?.form_settings || {},
        } );

        setSaving( true );

        try {
            const result = await createForm( formData );

            formId.current = result.form_id;
            openBuilder( formType, result.form_id, result.edit_url );
        } catch ( error ) {
            setSaving( false );
            dialogs.alert( {
                title: __( 'Error', 'wp-user-frontend' ),
                message: error.message,
                icon: 'warning',
                confirmText: __( 'OK', 'wp-user-frontend' ),
                tone: 'primary',
            } );
        }
    };

    if ( 'generating' === stage ) {
        return <ProcessingStage done={ responded } onComplete={ finishGenerating } />;
    }

    if ( 'success' === stage ) {
        return (
            <SuccessStage
                title={ title }
                initialFields={ fields }
                initialMessages={ initialMessages() }
                saving={ saving }
                onTitle={ setTitle }
                onFormUpdated={ ( update ) => {
                    setGenerated( ( current ) => ( { ...current, ...update } ) );

                    if ( update.form_title ) {
                        setTitle( update.form_title );
                    }
                } }
                onRegenerate={ () => {
                    // Develop keeps the generation session; the chat starts a new one.
                    setGenerated( null );
                    setInput( ( current ) => ( { description: '', promptId: '', integration: current.integration } ) );
                    setStage( 'input' );
                } }
                onEditInBuilder={ editInBuilder }
            />
        );
    }

    return (
        <InputStage
            description={ input.description }
            promptId={ input.promptId }
            integration={ input.integration }
            generating={ generating }
            onChange={ ( change ) => setInput( ( current ) => ( { ...current, ...change } ) ) }
            onGenerate={ generate }
        />
    );
}
