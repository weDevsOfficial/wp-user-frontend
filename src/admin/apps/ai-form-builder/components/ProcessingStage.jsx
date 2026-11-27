/**
 * Stage 2, "Generating your form...": four steps 1.5 s apart; once the AI
 * answered (`done`), confetti after 0.5 s and `onComplete` after 2.5 s
 * (develop's FormProcessingStage.vue timing).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { cn } from '@wedevs/plugin-ui';

import { assetUrl } from '../api';

const STEP_DELAY = 1500;
const CONFETTI_DELAY = 500;
const COMPLETE_DELAY = 2500;

const STEPS = () => [
    __( 'Analyzing your request and detecting the form type...', 'wp-user-frontend' ),
    __( 'Finalizing the title, required fields, and labels...', 'wp-user-frontend' ),
    __( 'Almost done! Generating your form preview...', 'wp-user-frontend' ),
    __( "Here's your AI-generated form - ready to customize and use!", 'wp-user-frontend' ),
];

/**
 * Step marker: check (done), spinner (current) or empty circle.
 *
 * @param {Object} props
 * @param {string} props.state done|current|todo
 */
function StepMarker( { state } ) {
    if ( 'done' === state ) {
        return (
            <span className="flex size-5 items-center justify-center rounded-full bg-emerald-600">
                <svg className="size-3 text-white" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                </svg>
            </span>
        );
    }

    if ( 'current' === state ) {
        return <span className="block size-5 animate-spin rounded-full border-2 border-emerald-600 border-t-transparent" />;
    }

    return <span className="block size-5 rounded-full border-2 border-gray-300" />;
}

/**
 * @param {Object}   props
 * @param {boolean}  props.done       The AI response arrived.
 * @param {Function} props.onComplete Called when the animation finished.
 */
export default function ProcessingStage( { done, onComplete } ) {
    const [ step, setStep ] = useState( 1 );
    const [ confetti, setConfetti ] = useState( false );
    const base = assetUrl();

    useEffect( () => {
        const timers = [ 2, 3, 4 ].map( ( next, index ) => setTimeout( () => setStep( next ), STEP_DELAY * ( index + 1 ) ) );

        return () => timers.forEach( clearTimeout );
    }, [] );

    useEffect( () => {
        if ( ! done ) {
            return undefined;
        }

        const timers = [
            setTimeout( () => setConfetti( true ), CONFETTI_DELAY ),
            setTimeout( () => onComplete?.(), COMPLETE_DELAY ),
        ];

        return () => timers.forEach( clearTimeout );
        // Runs once when the response arrives.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [ done ] );

    return (
        <div className="wpuf-ai-form-wrapper relative flex h-screen w-full items-center justify-center overflow-hidden bg-[#F5F5F5] font-sans" role="status" aria-live="polite">
            <div className="wpuf-ai-form-content mx-4 h-auto min-h-[416px] w-full max-w-[768px] rounded-lg border border-slate-300 bg-white p-6 sm:mx-auto sm:p-9">
                <div className="mb-5 flex justify-center">
                    <img src={ `${ base }/images/ai-star.gif` } alt={ __( 'Processing', 'wp-user-frontend' ) } className="size-24" />
                </div>
                <h3 className="m-0 mb-6 text-center text-xl font-semibold text-gray-900">{ __( 'Generating your form...', 'wp-user-frontend' ) }</h3>
                <div className="grid grid-cols-1 justify-items-center gap-2">
                    { STEPS().map( ( text, index ) => {
                        const number = index + 1;
                        let state = 'todo';

                        if ( step > number ) {
                            state = 'done';
                        } else if ( step === number ) {
                            state = 'current';
                        }

                        return (
                            <div key={ number } className={ cn( 'flex w-full max-w-md items-center justify-center gap-3', step >= number ? 'opacity-100' : 'opacity-40' ) }>
                                <div className="shrink-0"><StepMarker state={ state } /></div>
                                <p className="m-0 flex-1 text-sm leading-6 text-gray-600">{ text }</p>
                            </div>
                        );
                    } ) }
                </div>
                { confetti && base && (
                    <div className="pointer-events-none absolute inset-0 z-50 flex items-center justify-center">
                        <img src={ `${ base }/images/confetti_transparent.gif` } alt="" className="size-full object-cover" />
                    </div>
                ) }
            </div>
        </div>
    );
}
