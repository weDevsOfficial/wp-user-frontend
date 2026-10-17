/**
 * The setup wizard's steps. Each renders its fields from the step data of
 * the wizard state and hands its values (the names the step handlers of
 * Admin\Onboarding read) to `save`.
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Badge, Button, Modal, Notice, Progress, Select, Spinner, dialogs } from '@wpuf/components';

import { installPlugin } from './api';

import { ActionBar, ChoiceCard, Field, ProBadgeImage, StepShell, SwitchRow, Tick, TickCircle } from './parts';

/**
 * A list with a value added or removed.
 *
 * @param {string[]} list
 * @param {string}   value
 * @param {boolean}  on
 *
 * @return {string[]} New list.
 */
const toggleIn = ( list, value, on ) => ( on ? [ ...list.filter( ( item ) => item !== value ), value ] : list.filter( ( item ) => item !== value ) );

/**
 * Step 1: what the site needs.
 *
 * @param {Object} props Step props (data, nav, save, busy).
 */
export function FeaturesStep( { data, nav, save, busy, state } ) {
    const [ picked, setPicked ] = useState( data.picked || [] );
    const [ directoryOff, setDirectoryOff ] = useState( false );

    const change = async ( key, on ) => {
        // Switching off a directory in use takes its front end pages with it.
        if ( ! on && 'user_directory' === key && data.directory_in_use ) {
            const off = await dialogs.confirm( {
                title: __( 'Turn off the user directory?', 'wp-user-frontend' ),
                message: __( 'It is in use on this site. Its pages stop working on the front end until you turn it on again. Your directories and their settings are kept.', 'wp-user-frontend' ),
                confirmText: __( 'Turn it off', 'wp-user-frontend' ),
                cancelText: __( 'Keep it on', 'wp-user-frontend' ),
            } );

            if ( ! off ) {
                return;
            }

            setDirectoryOff( true );
        }

        if ( on && 'user_directory' === key ) {
            setDirectoryOff( false );
        }

        setPicked( ( list ) => toggleIn( list, key, on ) );
    };

    return (
        <>
            <StepShell title={ __( 'What does your site need?', 'wp-user-frontend' ) } subtitle={ __( 'Tick what you need and we will set it up with you. We will not ask about the rest.', 'wp-user-frontend' ) }>
                <div className="wpuf-onboarding-grid grid gap-4 sm:grid-cols-2">
                    { ( data.definitions || [] ).map( ( feature ) => (
                        <ChoiceCard key={ feature.key } name="features" value={ feature.key } checked={ picked.includes( feature.key ) } onChange={ ( on ) => change( feature.key, on ) }>
                            <strong className="mb-2 block pe-8 text-sm font-semibold text-gray-900">{ feature.name }</strong>
                            <span className="block text-[13px] leading-5 text-gray-500">{ feature.desc }</span>
                        </ChoiceCard>
                    ) ) }
                </div>
                <p className="m-0 mt-6 text-[13px] text-gray-500">
                    { __( 'Unticked features only hide their menus. Bring them back any time by running setup again from', 'wp-user-frontend' ) }{ ' ' }
                    <a href={ state.urls?.tools } className="font-medium text-primary hover:underline">{ __( 'User Frontend › Tools', 'wp-user-frontend' ) }</a>.
                </p>
            </StepShell>
            <ActionBar
                busy={ busy }
                nextLabel={ __( 'Continue', 'wp-user-frontend' ) }
                onPrevious={ nav.previous }
                onNext={ () => save( { features: picked, confirm_directory_off: directoryOff ? '1' : '' } ) }
            />
        </>
    );
}

/**
 * Post form step.
 *
 * @param {Object} props Step props.
 */
export function PostFormStep( { data, nav, save, busy } ) {
    const [ template, setTemplate ] = useState( data.selected );
    const [ edit, setEdit ] = useState( !! data.enable_post_edit );
    const [ remove, setRemove ] = useState( !! data.enable_post_del );
    const templates = data.templates || [];
    const current = templates.find( ( item ) => item.value === template );

    return (
        <>
            <StepShell title={ __( 'Post Form', 'wp-user-frontend' ) } subtitle={ __( 'The form visitors fill in to publish. Start from a template, change any field later.', 'wp-user-frontend' ) }>
                <Field
                    label={ __( 'Start from a template', 'wp-user-frontend' ) }
                    htmlFor="wpuf-onboarding-template"
                    help={
                        <>
                            { current?.desc }
                            { data.existing && (
                                <span className="mt-2 block">
                                    { __( 'Your default form now:', 'wp-user-frontend' ) }{ ' ' }
                                    <a href={ data.existing.url } target="_blank" rel="noopener noreferrer" className="font-medium text-primary hover:underline">{ data.existing.title }</a>
                                    { '.' }
                                    { 'skip' !== template && <>{ ' ' }{ __( 'A new one takes its place.', 'wp-user-frontend' ) }</> }
                                </span>
                            ) }
                        </>
                    }
                >
                    <Select id="wpuf-onboarding-template" value={ template } options={ templates.map( ( { value, label } ) => ( { value, label } ) ) } onChange={ setTemplate } />
                </Field>

                <Field label={ __( 'What authors can do after posting', 'wp-user-frontend' ) }>
                    <SwitchRow name="enable_post_edit" checked={ edit } onChange={ setEdit } title={ __( 'Let authors edit their posts', 'wp-user-frontend' ) } desc={ __( 'They can change their own posts from the frontend dashboard.', 'wp-user-frontend' ) } />
                    <SwitchRow name="enable_post_del" checked={ remove } onChange={ setRemove } title={ __( 'Let authors delete their posts', 'wp-user-frontend' ) } desc={ __( 'Deleted posts go to trash, so you can get them back.', 'wp-user-frontend' ) } />
                </Field>
            </StepShell>
            <ActionBar
                busy={ busy }
                onPrevious={ nav.previous }
                onSkip={ nav.skip }
                onNext={ () => save( { post_form_template: template, enable_post_edit: edit ? '1' : '', enable_post_del: remove ? '1' : '' } ) }
            />
        </>
    );
}

/**
 * Login and registration step.
 *
 * @param {Object} props Step props.
 */
export function RegistrationStep( { data, nav, save, busy, state } ) {
    const isPro = !! state.is_pro;
    const [ login, setLogin ] = useState( data.login_page );
    const [ reg, setReg ] = useState( data.reg_page );
    const [ account, setAccount ] = useState( data.account_page );
    const [ autologin, setAutologin ] = useState( !! data.autologin );
    const [ layout, setLayout ] = useState( data.layout );
    const [ picking, setPicking ] = useState( false );
    const layouts = data.layouts || [];
    const shown = layouts.find( ( item ) => item.value === layout ) || layouts[ 0 ] || {};

    const values = {
        login_page: login,
        reg_page: reg,
        account_page: account,
        autologin_after_registration: autologin ? '1' : '',
    };

    // Layouts are a Pro feature; the free preview sends nothing.
    if ( isPro ) {
        values.wpuf_login_form_layout = layout;
    }

    return (
        <>
            <StepShell title={ __( 'Login & Registration', 'wp-user-frontend' ) } subtitle={ __( 'Visitors sign up and log in on your own site, in your own theme.', 'wp-user-frontend' ) }>
                { false === data.registration_open && (
                    <Notice
                        tone="warning"
                        className="mb-6"
                        title={ __( 'New user registration is turned off in WordPress', 'wp-user-frontend' ) }
                        action={
                            <Button size="sm" variant="secondary" href={ data.registration_url } target="_blank" rel="noopener noreferrer">
                                { __( 'Allow sign-ups', 'wp-user-frontend' ) }
                            </Button>
                        }
                    >
                        { __( 'The registration page shows visitors "User registration disabled" until "Anyone can register" is turned on in the WordPress settings.', 'wp-user-frontend' ) }
                    </Notice>
                ) }
                <Field label={ __( 'Login page', 'wp-user-frontend' ) } htmlFor="wpuf-onboarding-login-page" required help={ __( 'Pick a page and we add the login form to it, or we make a new page.', 'wp-user-frontend' ) }>
                    <Select id="wpuf-onboarding-login-page" value={ login } options={ data.login_pages } onChange={ setLogin } />
                </Field>

                <Field
                    label={ __( 'Registration page', 'wp-user-frontend' ) }
                    htmlFor="wpuf-onboarding-reg-page"
                    required
                    help={ isPro ? __( 'Same for sign ups. You get a form you can add your own fields to.', 'wp-user-frontend' ) : __( 'Sign ups run on the built-in registration form, on a page in your own theme.', 'wp-user-frontend' ) }
                >
                    <Select id="wpuf-onboarding-reg-page" value={ reg } options={ data.reg_pages } onChange={ setReg } />
                </Field>

                { ! isPro && (
                    <div className="wpuf-onboarding-pro-note -mt-2 mb-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 hover:border-primary">
                        <span className="flex items-center gap-2 text-sm font-medium text-gray-900">
                            { __( 'Custom registration forms', 'wp-user-frontend' ) }
                            <ProBadgeImage src={ state.images?.proBadge } />
                        </span>
                        <span className="mt-0.5 block text-[13px] leading-5 text-gray-500">
                            { __( 'The page above works now. Building your own fields, roles and paid sign ups on top of it comes with Pro.', 'wp-user-frontend' ) }
                        </span>
                    </div>
                ) }

                <Field label={ __( 'Account page', 'wp-user-frontend' ) } htmlFor="wpuf-onboarding-account-page" required help={ __( 'Where members manage their posts, profile and subscription.', 'wp-user-frontend' ) }>
                    <Select id="wpuf-onboarding-account-page" value={ account } options={ data.account_pages } onChange={ setAccount } />
                </Field>

                <Field label={ __( 'After they sign up', 'wp-user-frontend' ) }>
                    <SwitchRow name="autologin_after_registration" checked={ autologin } onChange={ setAutologin } title={ __( 'Log them in straight away', 'wp-user-frontend' ) } desc={ __( 'They are in as soon as they submit. Leave off if you approve members yourself.', 'wp-user-frontend' ) } />
                </Field>

                { layouts.length > 0 && (
                    <div className={ `wpuf-onboarding-layouts mb-6 rounded-[10px] border bg-white shadow-sm ${ isPro ? 'border-gray-200' : 'border-dashed border-gray-300 hover:border-primary' }` }>
                        <div className="flex items-center gap-4 px-4 py-3">
                            { shown.image && <img src={ shown.image } alt="" className="h-10 w-14 rounded border border-solid border-gray-200 object-cover" /> }
                            <span className="min-w-0 flex-1">
                                <span className="flex items-center gap-2 text-sm font-medium text-gray-900">
                                    { __( 'Login form layout', 'wp-user-frontend' ) }
                                    { ! isPro && <ProBadgeImage src={ state.images?.proBadge } /> }
                                </span>
                                <span id="wpuf-onboarding-layout-name" className="mt-0.5 block text-[13px] text-gray-500">{ shown.label }</span>
                            </span>
                            <Button variant="link" onClick={ () => setPicking( ( open ) => ! open ) } aria-expanded={ picking } data-action="change-layout">
                                { picking ? __( 'Close', 'wp-user-frontend' ) : __( 'Change', 'wp-user-frontend' ) }
                            </Button>
                        </div>
                        { picking && (
                            <div className="border-0 border-t border-solid border-gray-200 p-4">
                                <div role="radiogroup" aria-label={ __( 'Login form layout', 'wp-user-frontend' ) } className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    { layouts.map( ( option ) => {
                                        const on = option.value === layout;

                                        return (
                                            <button
                                                key={ option.value }
                                                type="button"
                                                role="radio"
                                                aria-checked={ on }
                                                aria-label={ option.label }
                                                disabled={ ! isPro }
                                                data-value={ option.value }
                                                onClick={ () => {
                                                    setLayout( option.value );
                                                    setPicking( false );
                                                } }
                                                className={ `wpuf-onboarding-layout relative overflow-hidden rounded-lg border-2 border-solid bg-white p-0 disabled:cursor-not-allowed disabled:opacity-60 ${ on ? 'border-primary' : 'cursor-pointer border-gray-200 enabled:hover:border-primary' }` }
                                            >
                                                <img src={ option.image } alt="" className="block h-auto w-full" />
                                                { on && (
                                                    <span className="absolute end-2 top-2">
                                                        <TickCircle on />
                                                    </span>
                                                ) }
                                            </button>
                                        );
                                    } ) }
                                </div>
                                { ! isPro && (
                                    <p className="m-0 mt-3 text-[13px] text-gray-500">
                                        { __( 'Login form layouts come with', 'wp-user-frontend' ) }{ ' ' }
                                        <a href={ state.urls?.pro } target="_blank" rel="noopener noreferrer" className="font-medium text-primary hover:underline">{ __( 'Pro', 'wp-user-frontend' ) }</a>.
                                    </p>
                                ) }
                            </div>
                        ) }
                    </div>
                ) }
            </StepShell>
            <ActionBar busy={ busy } onPrevious={ nav.previous } onSkip={ nav.skip } onNext={ () => save( values ) } />
        </>
    );
}

/**
 * A gateway's own logo, or the settings screen's fallback icon.
 *
 * @param {Object} props
 * @param {Object} props.gateway
 */
function GatewayIcon( { gateway } ) {
    if ( gateway.icon ) {
        return <img src={ gateway.icon } alt="" className="h-10 w-auto max-w-[96px] object-contain" />;
    }

    if ( 'bank' === gateway.id ) {
        return (
            <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#787c82" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M3 21h18" /><path d="M3 10h18" /><path d="M12 3l9 7H3l9-7z" /><path d="M5 10v8" /><path d="M9 10v8" /><path d="M15 10v8" /><path d="M19 10v8" />
            </svg>
        );
    }

    return (
        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#787c82" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" /><line x1="1" y1="10" x2="23" y2="10" />
        </svg>
    );
}

/**
 * Settings step: the basics, and payments when picked.
 *
 * @param {Object} props Step props.
 */
export function CommonStep( { data, nav, save, busy, state } ) {
    const gateways = data.gateways || [];
    const [ pages, setPages ] = useState( true );
    const [ adminBar, setAdminBar ] = useState( !! data.hide_admin_bar );
    const [ logout, setLogout ] = useState( true );
    const [ payments, setPayments ] = useState( true );
    const [ active, setActive ] = useState( gateways.filter( ( item ) => item.selected && ! item.is_pro && ! item.needs_module ).map( ( item ) => item.id ) );
    const needsKeys = gateways.filter( ( item ) => item.needs_setup ).map( ( item ) => item.label );

    const values = {
        install_wpuf_pages: pages ? '1' : '',
        hide_admin_bar: adminBar ? '1' : '',
    };

    if ( data.wants_registration ) {
        values.add_logout_menu = logout ? '1' : '';
    }

    if ( data.wants_payments ) {
        values.enable_payment = payments ? '1' : '';
        values.active_gateways = active;
    }

    return (
        <>
            <StepShell title={ __( 'A few basics', 'wp-user-frontend' ) } subtitle={ __( 'The bits that make a frontend site feel finished. All of them live in Settings afterwards.', 'wp-user-frontend' ) }>
                <Field>
                    <SwitchRow
                        name="install_wpuf_pages"
                        checked={ pages }
                        onChange={ setPages }
                        title={ __( 'Install the remaining UF pages', 'wp-user-frontend' ) }
                        desc={ __( 'Adds whichever of the Dashboard, Subscription, Payment, Thank You and Order Received pages your site is still missing. Pages you already have, including the ones picked in the earlier steps, are reused rather than duplicated.', 'wp-user-frontend' ) }
                    />
                    <SwitchRow name="hide_admin_bar" checked={ adminBar } onChange={ setAdminBar } title={ __( 'Hide the admin bar from members', 'wp-user-frontend' ) } desc={ __( 'Members see your site, not the WordPress toolbar.', 'wp-user-frontend' ) } />
                    { data.wants_registration && (
                        <SwitchRow name="add_logout_menu" checked={ logout } onChange={ setLogout } title={ __( 'Add a logout link to the main menu', 'wp-user-frontend' ) } desc={ __( 'So members can sign out without hunting for it.', 'wp-user-frontend' ) } />
                    ) }
                </Field>

                { data.wants_payments && (
                    <>
                        <Field>
                            <SwitchRow
                                name="enable_payment"
                                checked={ payments }
                                onChange={ setPayments }
                                title={ __( 'Enable payments', 'wp-user-frontend' ) }
                                desc={ __( 'Subscription packs, coupons and transactions. Bank transfer works right away; add PayPal or Stripe in Settings.', 'wp-user-frontend' ) }
                            />
                        </Field>

                        { payments && (
                            <Field id="wpuf-onboarding-gateway-field" label={ __( 'How people pay', 'wp-user-frontend' ) }>
                                <div className="wpuf-onboarding-grid is-thirds grid gap-3 sm:grid-cols-3">
                                    { gateways.map( ( gateway ) => {
                                        const body = (
                                            <>
                                                <span className="wpuf-onboarding-card-icon mb-2 flex h-8 items-center justify-center"><GatewayIcon gateway={ gateway } /></span>
                                                <strong className="flex items-center justify-center gap-2 text-sm font-semibold text-gray-900">
                                                    { gateway.label }
                                                    { gateway.is_pro && <ProBadgeImage src={ state.images?.proBadge } /> }
                                                </strong>
                                                { gateway.hint && (
                                                    <span className={ `wpuf-onboarding-card-hint mt-1 block text-xs leading-4 ${ gateway.needs_setup || gateway.is_pro || gateway.needs_module ? 'is-warning text-amber-700' : 'text-gray-500' }` }>{ gateway.hint }</span>
                                                ) }
                                                { /* Locked cards: the way to unlock them. */ }
                                                { ( gateway.needs_module || gateway.is_pro ) && (
                                                    <a
                                                        href={ gateway.needs_module ? state.urls?.modules : state.urls?.pro }
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="mt-1 text-xs font-medium text-primary hover:underline"
                                                    >
                                                        { gateway.needs_module ? __( 'Open Modules', 'wp-user-frontend' ) : __( 'See Pro', 'wp-user-frontend' ) }
                                                    </a>
                                                ) }
                                            </>
                                        );

                                        // Cannot be switched on from here: the card says why and stays inert.
                                        if ( gateway.is_pro || gateway.needs_module ) {
                                            return (
                                                <div key={ gateway.id } data-value={ gateway.id } className={ `wpuf-onboarding-card ${ gateway.is_pro ? 'is-pro' : 'is-unavailable' } flex flex-col items-center rounded-[10px] border border-dashed border-gray-300 bg-gray-50 p-4 text-center hover:border-primary` }>
                                                    { body }
                                                </div>
                                            );
                                        }

                                        return (
                                            <ChoiceCard key={ gateway.id } compact name="active_gateways" value={ gateway.id } checked={ active.includes( gateway.id ) } onChange={ ( on ) => setActive( ( list ) => toggleIn( list, gateway.id, on ) ) }>
                                                { body }
                                            </ChoiceCard>
                                        );
                                    } ) }
                                </div>
                                { needsKeys.length > 0 && (
                                    <p className="wpuf-onboarding-help is-warning m-0 mt-3 text-[13px] leading-5 text-amber-700">
                                        { sprintf(
                                            /* translators: %s: gateway names, for example "PayPal" or "PayPal, Credit Card" */
                                            _n(
                                                'Bank transfer starts taking payments as soon as you finish here. %s still needs its API keys, so add them in Payment settings before you go live.',
                                                'Bank transfer starts taking payments as soon as you finish here. %s still need their API keys, so add them in Payment settings before you go live.',
                                                needsKeys.length,
                                                'wp-user-frontend'
                                            ),
                                            needsKeys.join( ', ' )
                                        ) }{ ' ' }
                                        <a href={ state.urls?.payment } target="_blank" rel="noopener noreferrer" className="font-medium text-primary hover:underline">{ __( 'Payment settings', 'wp-user-frontend' ) }</a>
                                    </p>
                                ) }
                            </Field>
                        ) }
                    </>
                ) }
            </StepShell>
            <ActionBar busy={ busy } onPrevious={ nav.previous } onSkip={ nav.skip } onNext={ () => save( values ) } />
        </>
    );
}

/**
 * Where a plugin is in the install run: its badge on the card.
 *
 * @param {Object} props
 * @param {Object} [props.run] { state: installing|done|failed, message }
 */
function InstallStatus( { run } ) {
    if ( ! run ) {
        return null;
    }

    if ( 'installing' === run.state ) {
        return (
            <Badge variant="pill" tone="primary" className="wpuf-onboarding-install is-installing bg-white shadow-sm">
                <Spinner size="xs" />
                { __( 'Installing…', 'wp-user-frontend' ) }
            </Badge>
        );
    }

    if ( 'done' === run.state ) {
        return (
            <Badge variant="pill" tone="success" className="wpuf-onboarding-install is-done">
                <Tick />
                { __( 'Installed', 'wp-user-frontend' ) }
            </Badge>
        );
    }

    return (
        <Badge variant="pill" tone="danger" className="wpuf-onboarding-install is-failed">
            { __( 'Failed', 'wp-user-frontend' ) }
        </Badge>
    );
}

/**
 * The install run in a dialog: one row per picked plugin with its state and
 * a progress bar. It cannot be closed while plugins install; a failure shows
 * the reason with Try again / Skip.
 *
 * @param {Object}   props
 * @param {boolean}  props.open
 * @param {Object[]} props.items      Picked plugins (slug, name, logo).
 * @param {Object}   props.runs       slug => { state, message }
 * @param {boolean}  props.installing
 * @param {Function} props.onClose
 * @param {Function} props.onRetry
 * @param {Function} [props.onSkip]
 */
function InstallDialog( { open, items, runs, installing, onClose, onRetry, onSkip } ) {
    const total = items.length;
    const done = items.filter( ( item ) => 'done' === runs[ item.slug ]?.state ).length;
    const failed = items.filter( ( item ) => 'failed' === runs[ item.slug ]?.state );
    const current = items.find( ( item ) => 'installing' === runs[ item.slug ]?.state );
    let title = __( 'Installing plugins', 'wp-user-frontend' );

    if ( ! installing ) {
        title = failed.length ? __( 'Some plugins did not install', 'wp-user-frontend' ) : __( 'Plugins installed', 'wp-user-frontend' );
    }

    const row = ( item ) => {
        const run = runs[ item.slug ];
        const state = run?.state || 'waiting';

        return (
            <li key={ item.slug } data-slug={ item.slug } data-state={ state } className="wpuf-install-row m-0 flex items-start gap-3 border-0 border-b border-solid border-gray-100 py-3 last:border-b-0">
                { item.logo ? <img src={ item.logo } alt="" className="h-8 w-8 shrink-0" /> : <span className="h-8 w-8 shrink-0" /> }
                <span className="min-w-0 flex-1">
                    <span className="block text-sm font-medium text-gray-900">{ item.name }</span>
                    { 'failed' === state && run.message && <span className="mt-0.5 block text-xs leading-4 text-red-700">{ run.message }</span> }
                </span>
                <span className="shrink-0 pt-0.5 text-xs font-medium">
                    { 'installing' === state && <span className="inline-flex items-center gap-1.5 text-primary"><Spinner size="xs" />{ __( 'Installing…', 'wp-user-frontend' ) }</span> }
                    { 'done' === state && <span className="inline-flex items-center gap-1.5 text-emerald-700"><Tick />{ __( 'Installed', 'wp-user-frontend' ) }</span> }
                    { 'failed' === state && <span className="text-red-700">{ __( 'Failed', 'wp-user-frontend' ) }</span> }
                    { 'waiting' === state && <span className="text-gray-400">{ __( 'Waiting', 'wp-user-frontend' ) }</span> }
                </span>
            </li>
        );
    };

    return (
        <Modal open={ open } onClose={ installing ? () => {} : onClose } showClose={ ! installing } title={ title } className="wpuf-install-dialog sm:max-w-md">
            <div aria-live="polite">
                <div className="mb-2 flex items-center justify-between text-[13px] text-gray-600">
                    <span className="wpuf-install-status">
                        { current
                            /* translators: 1: plugin name, 2: position, 3: total */
                            ? sprintf( __( 'Installing %1$s (%2$d of %3$d)', 'wp-user-frontend' ), current.name, Math.min( done + 1, total ), total )
                            /* translators: 1: installed count, 2: total */
                            : sprintf( __( '%1$d of %2$d installed', 'wp-user-frontend' ), done, total ) }
                    </span>
                    <span className="font-medium text-gray-900">{ total ? Math.round( ( done / total ) * 100 ) : 0 }%</span>
                </div>
                <Progress value={ total ? ( done / total ) * 100 : 0 } label={ __( 'Plugins installed', 'wp-user-frontend' ) } />
                <ul className="m-0 mt-3 list-none p-0">{ items.map( row ) }</ul>
                { installing && <p className="m-0 mt-3 text-xs text-gray-500">{ __( 'Keep this page open. Large plugins can take a minute.', 'wp-user-frontend' ) }</p> }
            </div>
            { ! installing && failed.length > 0 && (
                <div className="mt-4 flex justify-end gap-3">
                    { onSkip && <Button variant="secondary" onClick={ onSkip } data-action="install-skip">{ __( 'Skip for now', 'wp-user-frontend' ) }</Button> }
                    <Button onClick={ onRetry } data-action="install-retry">{ __( 'Try again', 'wp-user-frontend' ) }</Button>
                </div>
            ) }
        </Modal>
    );
}

/**
 * Companion plugins step: the picked plugins install one by one (each card
 * shows its progress, a bar shows the run), then the step saves. A failure
 * keeps the admin on the step with the reason, to retry or skip.
 *
 * @param {Object} props Step props.
 */
export function PluginsStep( { data, nav, save, busy } ) {
    const items = data.items || [];
    const [ picked, setPicked ] = useState( data.can_install ? items.map( ( item ) => item.slug ) : [] );
    const [ runs, setRuns ] = useState( {} );
    const [ installing, setInstalling ] = useState( false );
    const [ dialog, setDialog ] = useState( false );
    const queue = picked.filter( ( slug ) => 'done' !== runs[ slug ]?.state );
    const failed = items.filter( ( item ) => 'failed' === runs[ item.slug ]?.state );

    const mark = ( slug, run ) => setRuns( ( all ) => ( { ...all, [ slug ]: run } ) );

    const run = async () => {
        let ok = true;

        setInstalling( true );
        setDialog( true );

        for ( const slug of queue ) {
            mark( slug, { state: 'installing' } );

            try {
                await installPlugin( slug );
                mark( slug, { state: 'done' } );
            } catch ( error ) {
                ok = false;
                mark( slug, { state: 'failed', message: error.message } );
            }
        }

        setInstalling( false );

        // Everything picked runs now: the save has nothing left to install (the
        // dialog goes with the step). A failure keeps the dialog open with the reason.
        if ( ok ) {
            save( { plugins: [] } );
        }
    };

    const body = ( item ) => (
        <>
            { item.logo && <img src={ item.logo } alt="" className="wpuf-onboarding-logo mb-3 h-10 w-10" /> }
            <strong className="mb-1 block pe-24 text-sm font-semibold text-gray-900">{ item.name }</strong>
            <span className="block text-[13px] leading-5 text-gray-500">{ item.desc }</span>
            { 'failed' === runs[ item.slug ]?.state && runs[ item.slug ].message && (
                <span className="wpuf-onboarding-install-error mt-2 block text-xs leading-4 text-red-700">{ runs[ item.slug ].message }</span>
            ) }
        </>
    );

    const nextLabel = () => {
        if ( installing ) {
            return __( 'Installing…', 'wp-user-frontend' );
        }

        if ( failed.length ) {
            return __( 'Try again', 'wp-user-frontend' );
        }

        return queue.length ? __( 'Install & Continue', 'wp-user-frontend' ) : __( 'Continue', 'wp-user-frontend' );
    };

    return (
        <>
            <StepShell title={ __( 'Plugins that work well alongside', 'wp-user-frontend' ) } subtitle={ __( 'Free weDevs plugins for the members and content you collect. Untick any you do not want.', 'wp-user-frontend' ) }>
                { ( data.errors || [] ).length > 0 && ! Object.keys( runs ).length && (
                    <Notice tone="warning" title={ __( 'These did not install last time:', 'wp-user-frontend' ) } className="mb-6" role="alert">
                        { data.errors.map( ( error ) => (
                            <span key={ error.name } className="block"><strong>{ error.name }:</strong> { error.message }</span>
                        ) ) }
                    </Notice>
                ) }

                { failed.length > 0 && ! installing && (
                    <Notice tone="error" className="wpuf-onboarding-install-failed mb-6" role="alert">
                        { sprintf(
                            /* translators: %s: plugin names */
                            _n( '%s did not install. Try again, or skip this step and install it later from Plugins.', '%s did not install. Try again, or skip this step and install them later from Plugins.', failed.length, 'wp-user-frontend' ),
                            failed.map( ( item ) => item.name ).join( ', ' )
                        ) }
                    </Notice>
                ) }

                <div className="wpuf-onboarding-grid grid gap-4 sm:grid-cols-2">
                    { items.map( ( item ) => ( data.can_install ? (
                        <ChoiceCard
                            key={ item.slug }
                            name="plugins"
                            value={ item.slug }
                            checked={ picked.includes( item.slug ) }
                            disabled={ installing || 'done' === runs[ item.slug ]?.state }
                            status={ runs[ item.slug ] ? <InstallStatus run={ runs[ item.slug ] } /> : null }
                            onChange={ ( on ) => setPicked( ( list ) => toggleIn( list, item.slug, on ) ) }
                        >
                            { body( item ) }
                            { item.installed && ! runs[ item.slug ] && <Badge variant="pill" tone="success" className="wpuf-onboarding-badge mt-3">{ __( 'Will be activated', 'wp-user-frontend' ) }</Badge> }
                        </ChoiceCard>
                    ) : (
                        <div key={ item.slug } data-value={ item.slug } className="wpuf-onboarding-card is-installed flex flex-col items-start rounded-[10px] border border-solid border-gray-200 bg-gray-50 p-5">
                            { body( item ) }
                            <Badge variant="pill" tone="danger" className="wpuf-onboarding-badge is-error mt-3">{ __( 'No permission to install', 'wp-user-frontend' ) }</Badge>
                        </div>
                    ) ) ) }
                </div>
            </StepShell>
            <InstallDialog
                open={ dialog }
                items={ items.filter( ( item ) => picked.includes( item.slug ) ) }
                runs={ runs }
                installing={ installing }
                onClose={ () => setDialog( false ) }
                onRetry={ run }
                onSkip={ nav.skip ? () => {
                    setDialog( false );
                    nav.skip();
                } : null }
            />
            <ActionBar
                busy={ busy || installing }
                nextLabel={ nextLabel() }
                onPrevious={ nav.previous }
                onSkip={ nav.skip }
                onNext={ () => ( queue.length && data.can_install ? run() : save( { plugins: [] } ) ) }
            />
        </>
    );
}

/**
 * Ready step: what is set up, the diagnostics opt-in, then off.
 *
 * @param {Object} props Step props.
 */
export function ReadyStep( { data, save, busy, state } ) {
    const [ share, setShare ] = useState( !! data.share );
    const finish = ( url ) => save( { share_essentials: share ? '1' : '' }, url );

    return (
        <>
            <StepShell title={ __( 'Your frontend is ready', 'wp-user-frontend' ) } subtitle={ __( 'Anything still grey is worth a look. Each row opens the screen that handles it.', 'wp-user-frontend' ) }>
                <ul className="wpuf-onboarding-checklist m-0 mb-6 list-none overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white p-0 shadow-sm">
                    { ( data.checklist || [] ).map( ( item ) => (
                        <li key={ item.label } className={ `m-0 flex items-center gap-3 border-0 border-b border-solid border-gray-200 px-4 py-3 last:border-b-0 ${ item.done ? 'is-done' : '' }` }>
                            <span aria-hidden="true" className={ `inline-flex size-6 shrink-0 items-center justify-center rounded-full text-white ${ item.done ? 'bg-primary' : 'bg-gray-200' }` }>
                                <Tick />
                            </span>
                            <span className={ `flex-1 text-sm ${ item.done ? 'text-gray-900' : 'text-gray-500' }` }>{ item.label }</span>
                            <a href={ item.url } className="text-sm font-medium text-primary hover:underline">{ item.link }</a>
                        </li>
                    ) ) }
                </ul>

                <Notice tone="success" icon={ false } className="wpuf-onboarding-note mb-6">
                    { __( 'Run this setup again any time from', 'wp-user-frontend' ) }{ ' ' }
                    <a href={ state.urls?.tools } className="font-medium text-primary hover:underline">{ __( 'User Frontend › Tools', 'wp-user-frontend' ) }</a>.
                </Notice>

                <SwitchRow
                    name="share_essentials"
                    checked={ share }
                    onChange={ setShare }
                    title={ __( 'Share diagnostic data', 'wp-user-frontend' ) }
                    desc={ __( 'Versions, site name and your email. It tells us what to fix first. Never your members\' data.', 'wp-user-frontend' ) }
                />
            </StepShell>
            <ActionBar
                busy={ busy }
                left={
                    <Button variant="secondary" size="lg" onClick={ () => finish( state.urls?.settings ) } disabled={ busy } data-action="settings">
                        { __( 'Go to full settings', 'wp-user-frontend' ) }
                    </Button>
                }
                nextLabel={ data.cta?.label }
                onNext={ () => finish( data.cta?.url ) }
            />
        </>
    );
}
