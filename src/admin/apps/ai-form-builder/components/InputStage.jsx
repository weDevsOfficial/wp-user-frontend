/**
 * Stage 1, "Create Form with AI": description (300 characters), optional
 * integration, prompt templates, Back and Generate (develop's
 * FormInputStage.vue).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { Button, Select, Textarea } from '@wpuf/components';
import { cn } from '@wedevs/plugin-ui';

import { config, fetchIntegrations } from '../api';
import { SparklesIcon, Spinner } from './icons';

const MAX_DESCRIPTION = 300;

/**
 * Prompt templates for the form type and integration.
 *
 * @param {string} formType    post|profile|registration
 * @param {string} integration Integration id ('' for none).
 *
 * @return {Array} { id, label }.
 */
function promptTemplatesFor( formType, integration ) {
    const key = 'registration' === formType ? 'profile' : formType;
    const templates = ( config().promptTemplates || {} )[ key ] || {};

    return templates[ integration || '' ] || [];
}

/**
 * @param {Object}   props
 * @param {string}   props.description Description.
 * @param {string}   props.promptId    Selected prompt id.
 * @param {string}   props.integration Selected integration.
 * @param {boolean}  props.generating  A generation is running.
 * @param {Function} props.onChange    ( { description?, promptId?, integration? } ) => void.
 * @param {Function} props.onGenerate  ( { description, promptId, integration } ) => void.
 */
export default function InputStage( { description, promptId, integration, generating, onChange, onGenerate } ) {
    const formType = config().formType || 'post';
    const instructions = config().promptAIInstructions || {};
    const [ integrations, setIntegrations ] = useState( [] );
    const [ loading, setLoading ] = useState( true );
    const templates = useMemo( () => promptTemplatesFor( formType, integration ), [ formType, integration ] );

    useEffect( () => {
        let active = true;

        fetchIntegrations( formType )
            .then( ( list ) => {
                if ( ! active ) {
                    return;
                }

                setIntegrations( list );

                // A remembered integration that is gone falls back to none.
                if ( integration && ! list.some( ( item ) => item.id === integration ) ) {
                    onChange( { integration: '' } );
                }
            } )
            .catch( () => {} )
            .finally( () => active && setLoading( false ) );

        return () => {
            active = false;
        };
        // Fetched once on mount, like develop.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [] );

    const editDescription = ( value ) => {
        const next = value.slice( 0, MAX_DESCRIPTION );

        // Editing the text away from a prompt's instruction deselects the prompt.
        if ( promptId && description && next !== description && ! Object.values( instructions ).includes( next ) ) {
            onChange( { description: next, promptId: '' } );
            return;
        }

        onChange( { description: next } );
    };

    const pickPrompt = ( template ) => {
        const instruction = instructions[ template.id ] || template.label;

        onChange( { promptId: template.id, description: instruction.substring( 0, MAX_DESCRIPTION ) } );
    };

    const generate = () => {
        if ( ! description.trim() || generating ) {
            return;
        }

        onGenerate( { description, promptId, integration } );
    };

    const integrationOptions = [
        { value: '', label: loading ? __( 'Loading integrations...', 'wp-user-frontend' ) : __( 'Regular Form (No Integration)', 'wp-user-frontend' ) },
        ...integrations.map( ( item ) => ( { value: item.id, label: item.label } ) ),
    ];

    return (
        <div className="wpuf-ai-form-wrapper relative min-h-screen w-full overflow-hidden bg-[#f0f0f1] font-sans">
            <div className="wpuf-ai-form-content mx-auto mt-16 mb-10 w-full max-w-[720px] px-4">
                <div className="mb-6 text-center">
                    <span className="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"><SparklesIcon /></span>
                    <h2 className="m-0 mb-1 text-2xl font-bold leading-8 text-gray-900">{ __( 'Create Form with AI', 'wp-user-frontend' ) }</h2>
                    <p className="m-0 text-sm text-gray-500">{ __( 'Automatically generate smart, customizable forms using AI.', 'wp-user-frontend' ) }</p>
                </div>

                <div className="mb-6">
                    <Textarea
                        value={ description }
                        onChange={ editDescription }
                        rows={ 6 }
                        maxLength={ MAX_DESCRIPTION }
                        placeholder={ __( 'Describe your form', 'wp-user-frontend' ) }
                        aria-label={ __( 'Describe your form', 'wp-user-frontend' ) }
                        className="wpuf-ai-description w-full resize-none rounded-md border-gray-300 bg-white! px-3 py-2.5 text-sm text-gray-700 focus-visible:border-primary focus-visible:ring-[3px] focus-visible:ring-primary/10"
                    />
                    <div className="mt-1.5 text-right text-[13px] text-gray-500">
                        { description.length }/{ MAX_DESCRIPTION } { __( 'Characters', 'wp-user-frontend' ) }
                    </div>
                </div>

                { ( integrations.length > 0 || loading ) && (
                    <div className="mb-6">
                        <label htmlFor="wpuf-ai-integration" className="mb-1.5 block text-sm font-medium text-gray-900">
                            { __( 'Form Type (Optional)', 'wp-user-frontend' ) }
                        </label>
                        <Select
                            id="wpuf-ai-integration"
                            value={ integration }
                            options={ integrationOptions }
                            disabled={ loading }
                            onChange={ ( next ) => onChange( { integration: next, promptId: '', description: '' } ) }
                            className="w-full"
                        />
                        <p className="m-0 mt-1.5 text-[13px] text-gray-500">
                            { loading ? (
                                <span className="inline-flex items-center gap-2">
                                    <Spinner />
                                    { __( 'Loading available integrations...', 'wp-user-frontend' ) }
                                </span>
                            ) : __( 'Choose a form type if you want to create a form for a specific integration', 'wp-user-frontend' ) }
                        </p>
                    </div>
                ) }

                <div className="mb-6">
                    <p className="m-0 mb-3 text-sm font-medium text-gray-900">{ __( 'Or create using our Prompts:', 'wp-user-frontend' ) }</p>
                    <div className="flex flex-wrap gap-2">
                        { templates.map( ( template ) => (
                            <button
                                key={ template.id }
                                type="button"
                                aria-pressed={ promptId === template.id }
                                onClick={ () => pickPrompt( template ) }
                                className={ cn(
                                    'h-8 cursor-pointer rounded-full border border-solid px-3 text-[13px] font-medium transition-all',
                                    promptId === template.id
                                        ? 'wpuf-prompt-btn-active border-primary bg-primary text-white'
                                        : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary'
                                ) }
                            >
                                { template.label }
                            </button>
                        ) ) }
                    </div>
                </div>

                <div className="flex justify-end gap-3 border-0 border-t border-solid border-gray-200 pt-6">
                    <Button
                        variant="secondary"
                        onClick={ () => window.history.back() }
                        size="lg"
                    >
                        { __( 'Back', 'wp-user-frontend' ) }
                    </Button>
                    <Button
                        onClick={ generate }
                        disabled={ ! description.trim() }
                        busy={ generating }
                        size="lg"
                        className="wpuf-ai-generate"
                    >
                        { generating ? __( 'Generating...', 'wp-user-frontend' ) : __( 'Generate Form', 'wp-user-frontend' ) }
                        { ! generating && <SparklesIcon /> }
                    </Button>
                </div>
            </div>
        </div>
    );
}
