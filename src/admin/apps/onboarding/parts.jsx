/**
 * Building blocks of the setup wizard's steps, on the shared components.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Button, Toggle } from '@wpuf/components';

import { Reveal, RevealWords, afterWords } from './Reveal';

/**
 * The tick of the step rail, the cards and the checklist.
 *
 * @param {Object} props
 * @param {string} [props.className]
 */
export function Tick( { className = '' } ) {
    return (
        <svg width="9" height="7" viewBox="0 0 9 7" fill="none" aria-hidden="true" className={ className }>
            <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * A round tick: green when on, an empty ring when off.
 *
 * @param {Object}  props
 * @param {boolean} props.on
 */
export function TickCircle( { on } ) {
    return (
        <span
            aria-hidden="true"
            className={ `inline-flex size-6 shrink-0 items-center justify-center rounded-full border transition-colors ${ on ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white text-white' }` }
        >
            <Tick />
        </span>
    );
}

/**
 * The Pro badge, as an image: inside the wizard it is not a link that would
 * take the admin out of the setup.
 *
 * @param {Object} props
 * @param {string} props.src Badge image.
 */
export function ProBadgeImage( { src } ) {
    return <img src={ src } alt={ __( 'PRO', 'wp-user-frontend' ) } width="39" height="22" className="wpuf-onboarding-pro-badge inline-block h-[22px] w-[39px] max-w-none align-middle" />;
}

/**
 * A step's title, subtitle and body, arriving in turn.
 *
 * @param {Object} props
 * @param {string} props.title
 * @param {string} props.subtitle
 * @param {*}      props.children
 */
export function StepShell( { title, subtitle, children } ) {
    const after = afterWords( title );

    return (
        <div className="mx-auto w-full max-w-[720px]">
            <RevealWords text={ title } as="h2" className="m-0 mb-2 text-center text-xl font-semibold leading-7 text-gray-900" />
            <Reveal as="p" delay={ after } className="m-0 mb-10 text-center text-sm leading-6 text-gray-500">
                { subtitle }
            </Reveal>
            <Reveal arrival="panel" delay={ after + 90 }>
                { children }
            </Reveal>
        </div>
    );
}

/**
 * A labelled field.
 *
 * @param {Object}  props
 * @param {string}  [props.label]
 * @param {string}  [props.htmlFor]
 * @param {boolean} [props.required]
 * @param {*}       [props.help]
 * @param {*}       props.children
 */
export function Field( { label, htmlFor, required = false, help, children, className = '', id } ) {
    const Label = htmlFor ? 'label' : 'span';

    return (
        <div id={ id } className={ `mb-6 ${ className }` }>
            { label && (
                <Label htmlFor={ htmlFor } className="mb-2 block text-sm font-medium text-gray-900">
                    { label }
                    { required && (
                        <>
                            <span className="wpuf-onboarding-required ms-1 text-red-500" aria-hidden="true">*</span>
                            <span className="screen-reader-text">{ __( 'required', 'wp-user-frontend' ) }</span>
                        </>
                    ) }
                </Label>
            ) }
            { children }
            { help && <p className="m-0 mt-2 text-[13px] leading-5 text-gray-500">{ help }</p> }
        </div>
    );
}

/**
 * A bordered row with a title, a line of help and a switch.
 *
 * @param {Object}   props
 * @param {string}   props.name     Submitted field name.
 * @param {boolean}  props.checked
 * @param {Function} props.onChange ( checked ) => void
 * @param {string}   props.title
 * @param {string}   [props.desc]
 */
export function SwitchRow( { name, checked, onChange, title, desc } ) {
    const id = `wpuf-onboarding-${ name.replace( /_/g, '-' ) }`;

    return (
        <div className="wpuf-onboarding-switch mb-3 flex items-center gap-4 rounded-[10px] border border-gray-200 bg-white px-4 py-3 shadow-sm" data-name={ name }>
            <label htmlFor={ id } className="min-w-0 flex-1 cursor-pointer">
                <span className="block text-sm font-medium text-gray-900">{ title }</span>
                { desc && <span className="mt-0.5 block text-[13px] leading-5 text-gray-500">{ desc }</span> }
            </label>
            <Toggle id={ id } name={ name } value={ checked } checkedValue={ true } uncheckedValue={ false } onChange={ onChange } />
        </div>
    );
}

/**
 * A card that ticks on and off (a checkbox).
 *
 * @param {Object}   props
 * @param {boolean}  props.checked
 * @param {Function} props.onChange ( checked ) => void
 * @param {string}   props.name     Submitted list name.
 * @param {string}   props.value    Submitted value.
 * @param {*}        props.children
 * @param {boolean}  [props.compact] Smaller, centred card (gateways).
 * @param {boolean}  [props.disabled] Locked (while plugins install).
 * @param {*}        [props.status]   Replaces the tick (install progress).
 */
export function ChoiceCard( { checked, onChange, name, value, children, compact = false, disabled = false, status = null } ) {
    return (
        <button
            type="button"
            role="checkbox"
            aria-checked={ checked }
            data-name={ name }
            data-value={ value }
            disabled={ disabled }
            onClick={ () => onChange( ! checked ) }
            className={ `wpuf-onboarding-card relative flex w-full cursor-pointer flex-col rounded-[10px] border shadow-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-default ${ compact ? 'items-center p-4 text-center' : 'items-start p-5 text-start' } ${ checked ? 'is-selected border-primary bg-primary/5 ring-2 ring-primary/15' : 'border-gray-200 bg-white enabled:hover:border-primary' }` }
        >
            <span className={ `absolute ${ compact ? 'end-3 top-3' : 'end-5 top-5' }` }>
                { status || <TickCircle on={ checked } /> }
            </span>
            { children }
        </button>
    );
}

/**
 * The fixed bar under the step: Previous on the left, Skip and the step's
 * submit on the right.
 *
 * @param {Object}   props
 * @param {Function} [props.onPrevious]
 * @param {Function} [props.onSkip]
 * @param {Function} props.onNext
 * @param {string}   [props.nextLabel]
 * @param {boolean}  [props.busy]
 * @param {*}        [props.left] Replaces Previous.
 */
export function ActionBar( { onPrevious, onSkip, onNext, nextLabel, busy = false, left } ) {
    return (
        <div className="wpuf-onboarding-footer fixed inset-x-0 bottom-0 z-20 border-0 border-t border-solid border-gray-200 bg-white">
            <div className="mx-auto flex max-w-[848px] items-center justify-between gap-4 px-4 py-4">
                <div>
                    { left || ( onPrevious && (
                        <Button variant="secondary" size="lg" onClick={ onPrevious } disabled={ busy } data-action="previous">
                            { __( 'Previous', 'wp-user-frontend' ) }
                        </Button>
                    ) ) }
                </div>
                <div className="flex items-center gap-4">
                    { onSkip && (
                        <Button variant="link" onClick={ onSkip } disabled={ busy } data-action="skip" className="text-gray-500 enabled:hover:text-primary enabled:hover:no-underline">
                            { __( 'Skip this step', 'wp-user-frontend' ) }
                        </Button>
                    ) }
                    <Button size="lg" onClick={ onNext } busy={ busy } data-action="next">
                        { nextLabel || __( 'Save & Continue', 'wp-user-frontend' ) }
                    </Button>
                </div>
            </div>
        </div>
    );
}
