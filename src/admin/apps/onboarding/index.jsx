/**
 * The setup wizard: admin app route `#/onboarding/:step`
 * (Admin\Screens\Onboarding), full screen like FlyHR's, on the shared
 * components. The wizard state comes with the page or from
 * wpuf/v1/onboarding; each step saves through wpuf/v1/onboarding/{step},
 * which runs the step handler of Platform\Onboarding\Wizard.
 *
 * @since WPUF_SINCE
 */
import { createRoot, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Loading, WpufProviders, notify } from '@wpuf/components';

import { registerScreen } from '../../app/client';
import { api } from './api';
import Confetti from './Confetti';
import { Reveal, RevealWords, afterWords } from './Reveal';
import { Tick } from './parts';
import { CommonStep, FeaturesStep, PluginsStep, PostFormStep, ReadyStep, RegistrationStep } from './steps';

const STEPS = {
    features: FeaturesStep,
    post_form: PostFormStep,
    registration: RegistrationStep,
    common: CommonStep,
    plugins: PluginsStep,
    ready: ReadyStep,
};

// The wizard state outlives a step's mount (each step is its own route).
let cache = window.wpufOnboarding?.state || null;
// The heading arrives once per visit, not on every step.
let headingPlayed = false;

/**
 * The step rail: the picker shows only while it is open; a step before the
 * current one, or saved before, is done.
 *
 * @param {Object}   props
 * @param {Object[]} props.steps
 * @param {string}   props.current
 * @param {string[]} props.completed
 * @param {Function} props.go
 */
function StepRail( { steps, current, completed, go } ) {
    const rail = steps.filter( ( step ) => 'features' !== step.key || 'features' === current );
    const index = steps.findIndex( ( step ) => step.key === current );

    return (
        <nav aria-label={ __( 'Progress', 'wp-user-frontend' ) } className="mx-auto mt-10 w-full max-w-[900px]">
            <ol className="wpuf-onboarding-steps m-0 flex list-none p-0">
                { rail.map( ( step, at ) => {
                    const position = steps.findIndex( ( item ) => item.key === step.key );
                    const active = step.key === current;
                    const done = ! active && ( position < index || completed.includes( step.key ) );

                    return (
                        <li key={ step.key } className={ `relative m-0 flex flex-1 flex-col items-center ${ active ? 'is-active' : '' } ${ done ? 'is-done' : '' }` }>
                            { at < rail.length - 1 && (
                                <span aria-hidden="true" className={ `absolute top-3 h-0.5 start-1/2 w-full ${ position < index ? 'bg-primary' : 'bg-gray-200' }` } />
                            ) }
                            <button
                                type="button"
                                onClick={ () => go( step.key ) }
                                aria-current={ active ? 'step' : undefined }
                                aria-label={ step.label }
                                className={ `wpuf-step-marker relative z-[1] inline-flex size-6 cursor-pointer items-center justify-center rounded-full border-2 border-solid p-0 text-white transition-colors ${ active || done ? 'border-primary bg-primary' : 'border-gray-300 bg-white hover:border-primary' }` }
                            >
                                <Tick />
                            </button>
                            <span className={ `wpuf-step-label mt-3 text-center text-sm font-medium ${ active || done ? 'text-primary' : 'text-gray-600' }` }>{ step.label }</span>
                        </li>
                    );
                } ) }
            </ol>
        </nav>
    );
}

/**
 * @param {Object} props
 * @param {Object} props.context App route context (params, query, navigate).
 */
function Onboarding( { context } ) {
    const [ state, setState ] = useState( cache );
    const [ busy, setBusy ] = useState( false );
    const [ playHeading ] = useState( () => ! headingPlayed );
    const steps = state?.steps || [];
    const requested = context?.params?.step || '';
    const step = steps.some( ( item ) => item.key === requested ) ? requested : steps[ 0 ]?.key;
    const celebrate = '1' === context?.query?.celebrate;

    const keep = ( next ) => {
        cache = next;
        setState( next );
    };

    const go = ( key, query = '' ) => context.navigate( `/onboarding/${ key }${ query }` );

    useEffect( () => {
        headingPlayed = true;
    }, [] );

    // Load the state when the page did not bring it, then remember the step.
    useEffect( () => {
        let live = true;

        ( state ? Promise.resolve( state ) : api( '/onboarding' ) )
            .then( ( loaded ) => {
                const key = ( loaded.steps || [] ).some( ( item ) => item.key === requested ) ? requested : loaded.steps?.[ 0 ]?.key;

                if ( live && ! state ) {
                    keep( loaded );
                }

                if ( key && key !== requested ) {
                    context.navigate( `/onboarding/${ key }`, { replace: true } );

                    return null;
                }

                return key ? api( `/onboarding/${ key }/visit`, {} ) : null;
            } )
            .then( ( fresh ) => {
                if ( live && fresh ) {
                    keep( fresh );
                }
            } )
            .catch( ( error ) => notify( error.message, 'error' ) );

        return () => {
            live = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [ requested ] );

    if ( ! state || ! step ) {
        return <div className="flex min-h-screen items-center justify-center"><Loading /></div>;
    }

    const index = steps.findIndex( ( item ) => item.key === step );
    const Step = STEPS[ step ];

    const save = async ( values, leaveTo ) => {
        setBusy( true );

        try {
            const result = await api( `/onboarding/${ step }`, { values } );

            keep( result.state );

            if ( leaveTo ) {
                window.location.href = leaveTo;

                return;
            }

            if ( result.next ) {
                go( result.next, result.celebrate ? '?celebrate=1' : '' );
            } else {
                window.location.href = state.data?.ready?.cta?.url || state.urls?.exit;
            }
        } catch ( error ) {
            notify( error.message, 'error' );
        } finally {
            setBusy( false );
        }
    };

    const nav = {
        previous: index > 0 ? () => go( steps[ index - 1 ].key ) : null,
        skip: index < steps.length - 1 ? () => go( steps[ index + 1 ].key ) : null,
    };

    const title = __( 'Set up User Frontend', 'wp-user-frontend' );

    return (
        <div className="wpuf-onboarding min-h-screen bg-white pb-32 text-gray-700" data-step={ step }>
            <div className="wpuf-onboarding-topbar flex items-center justify-between px-9 py-5">
                <img src={ state.images?.logo } alt={ __( 'User Frontend', 'wp-user-frontend' ) } className="h-[30px] w-auto" />
                <a href={ state.urls?.exit } className="wpuf-onboarding-exit text-sm text-gray-600 hover:text-gray-900">
                    { __( 'Exit setup', 'wp-user-frontend' ) }
                </a>
            </div>

            <div className="wpuf-onboarding-head px-4 text-center">
                { playHeading ? (
                    <RevealWords text={ title } className="m-0 mt-2 text-2xl font-bold leading-8 text-gray-900" />
                ) : (
                    <h1 className="m-0 mt-2 text-2xl font-bold leading-8 text-gray-900">{ title }</h1>
                ) }
                <Reveal arrival="panel" delay={ playHeading ? afterWords( title ) : 0 }>
                    <StepRail steps={ steps } current={ step } completed={ state.progress?.completed || [] } go={ go } />
                </Reveal>
            </div>

            <div className="wpuf-onboarding-content mt-12 px-4" key={ step }>
                { Step ? <Step data={ state.data?.[ step ] || {} } state={ state } nav={ nav } save={ save } busy={ busy } /> : null }
            </div>

            { 'ready' === step && celebrate && <Confetti icon={ state.images?.icon } /> }
        </div>
    );
}

registerScreen( 'onboarding', [ 'wpuf-onboarding-root' ], ( element, context ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <Onboarding context={ context } />
        </WpufProviders>
    );

    return () => root.unmount();
} );
