/**
 * Welcome to WP User Frontend: admin app route `#/welcome`
 * (Admin\Screens\Welcome) on the shared components, with FlyHR's motion; and
 * the one-time welcome over the first admin app page an admin opens.
 *
 * @since WPUF_SINCE
 */
import { createRoot, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Modal, PageHeader, PageShell, WpufProviders } from '@wpuf/components';

import { openRoute, registerScreen } from '../../app/client';
import { Reveal, RevealWords, afterWords } from '../onboarding/Reveal';
import WelcomeIntro from './WelcomeIntro';

const data = () => window.wpufWelcome || {};

function Check() {
    return (
        <span aria-hidden="true" className="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-white">
            <svg width="9" height="7" viewBox="0 0 9 7" fill="none"><path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" /></svg>
        </span>
    );
}

function Welcome() {
    const page = data();
    const [ video, setVideo ] = useState( false );
    const title = __( 'Welcome to WP User Frontend', 'wp-user-frontend' );
    const after = afterWords( title );
    const sections = page.sections || [];

    return (
        <PageShell>
            <PageHeader utm="wpuf-welcome" />
            <div className="wpuf-welcome mt-6 grid gap-6 pb-10">
                <section className="wpuf-welcome-hero relative overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white">
                    <span aria-hidden="true" className="wpuf-welcome-glow pointer-events-none absolute -start-24 -top-24 size-80 rounded-full bg-emerald-300/25 blur-3xl" />
                    <span aria-hidden="true" className="wpuf-welcome-glow wpuf-welcome-glow-late pointer-events-none absolute -bottom-32 -end-16 size-96 rounded-full bg-emerald-400/15 blur-3xl" />
                    <div className="relative grid items-center gap-8 p-8 lg:grid-cols-2 lg:p-10">
                        <div>
                            <Reveal arrival="panel">
                                <img src={ page.logo } alt="" className="wpuf-float mb-6 h-9 w-auto" />
                            </Reveal>
                            <RevealWords text={ title } className="m-0 text-2xl font-bold leading-tight tracking-tight text-gray-900" />
                            <Reveal as="p" delay={ after } className="m-0 mt-4 max-w-xl text-base leading-7 text-gray-500">
                                { __( 'Let people post, sign up, manage their account and pay, all from the frontend of your site. Start with a form, or let the setup wizard build the pages for you.', 'wp-user-frontend' ) }
                            </Reveal>
                            <Reveal delay={ after + 120 } className="mt-6 flex flex-wrap items-center gap-3">
                                <Button onClick={ () => openRoute( '/post-forms', page.urls?.postForms ) }>{ __( 'Create Your First Form', 'wp-user-frontend' ) }</Button>
                                <Button variant="secondary" onClick={ () => openRoute( '/onboarding/features', page.urls?.onboarding ) }>{ __( 'Run the Setup Wizard', 'wp-user-frontend' ) }</Button>
                                <a href={ page.urls?.guide } target="_blank" rel="noopener noreferrer" className="text-sm font-medium text-primary hover:underline">
                                    { __( 'Read the Full Guide', 'wp-user-frontend' ) } →
                                </a>
                            </Reveal>
                        </div>
                        { page.video?.id && (
                            <Reveal arrival="panel" delay={ after + 200 }>
                                <button
                                    type="button"
                                    onClick={ () => setVideo( true ) }
                                    aria-label={ __( 'Watch the post form builder video', 'wp-user-frontend' ) }
                                    className="wpuf-welcome-video group relative block w-full cursor-pointer overflow-hidden rounded-lg border border-solid border-gray-200 bg-emerald-50 p-0 shadow-sm"
                                >
                                    <img src={ page.video.thumb } alt="" className="block h-auto w-full transition-transform duration-500 group-hover:scale-[1.02]" />
                                    <span className="absolute inset-0 flex items-center justify-center">
                                        <span className="inline-flex size-16 items-center justify-center rounded-full bg-primary text-white shadow-lg transition-transform group-hover:scale-110">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" /></svg>
                                        </span>
                                    </span>
                                </button>
                            </Reveal>
                        ) }
                    </div>
                </section>

                <Reveal delay={ after + 260 }>
                    <h2 className="m-0 text-lg font-semibold text-gray-900">{ __( 'What you can build', 'wp-user-frontend' ) }</h2>
                    <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Each one opens its guide.', 'wp-user-frontend' ) }</p>
                </Reveal>

                <div className="grid gap-6 xl:grid-cols-3">
                    { sections.map( ( section, at ) => (
                        <Reveal key={ section.title } arrival="panel" delay={ after + 320 + at * 90 } as="section" className="wpuf-welcome-section rounded-[10px] border border-solid border-gray-200 bg-white">
                            <h3 className="m-0 border-0 border-b border-solid border-gray-200 px-6 py-4 text-base font-semibold text-gray-900">{ section.title }</h3>
                            <ul className="m-0 list-none p-0">
                                { ( section.items || [] ).map( ( item ) => (
                                    <li key={ item.title } className="m-0">
                                        <a href={ item.url } target="_blank" rel="noopener noreferrer" className="group flex items-start gap-4 px-6 py-4 no-underline hover:bg-emerald-50/50">
                                            <img src={ item.icon } alt="" className="size-10 shrink-0" />
                                            <span className="min-w-0">
                                                <span className="block text-sm font-semibold text-gray-900 group-hover:text-primary">{ item.title }</span>
                                                <span className="mt-0.5 block text-[13px] leading-5 text-gray-500">{ item.text }</span>
                                            </span>
                                        </a>
                                    </li>
                                ) ) }
                            </ul>
                        </Reveal>
                    ) ) }
                </div>

                { ! page.isPro && ( page.proFeatures || [] ).length > 0 && (
                    <Reveal arrival="panel" delay={ after + 620 } as="section" className="wpuf-welcome-pro overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white">
                        <div className="grid gap-6 p-8 lg:grid-cols-[1fr_auto] lg:items-center">
                            <div>
                                <h2 className="m-0 text-lg font-semibold text-gray-900">{ __( 'Upgrade to PRO', 'wp-user-frontend' ) }</h2>
                                <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Everything above, plus the tools that run a membership site.', 'wp-user-frontend' ) }</p>
                                <ul className="m-0 mt-5 grid list-none gap-3 p-0 sm:grid-cols-2">
                                    { page.proFeatures.map( ( feature ) => (
                                        <li key={ feature } className="m-0 flex items-center gap-2 text-sm text-gray-700"><Check />{ feature }</li>
                                    ) ) }
                                </ul>
                            </div>
                            <div className="flex flex-col items-start gap-3 lg:items-end">
                                <Button onClick={ () => window.open( page.urls?.upgrade, '_blank', 'noopener' ) }>{ __( 'Upgrade to PRO', 'wp-user-frontend' ) }</Button>
                                <a href={ page.urls?.features } target="_blank" rel="noopener noreferrer" className="text-sm font-medium text-primary hover:underline">
                                    { __( 'See All Features', 'wp-user-frontend' ) } →
                                </a>
                            </div>
                        </div>
                    </Reveal>
                ) }
            </div>

            <Modal open={ video } onClose={ () => setVideo( false ) } title={ __( 'The post form builder', 'wp-user-frontend' ) } className="w-[min(960px,92vw)] max-w-none p-4 sm:max-w-none">
                { video && (
                    <div className="relative w-full overflow-hidden rounded-md bg-black pt-[56.25%]">
                        <iframe
                            className="absolute inset-0 size-full border-0"
                            src={ `https://www.youtube-nocookie.com/embed/${ encodeURIComponent( page.video.id ) }?autoplay=1&rel=0` }
                            title={ __( 'The post form builder', 'wp-user-frontend' ) }
                            allow="autoplay; encrypted-media; picture-in-picture"
                            allowFullScreen
                        />
                    </div>
                ) }
            </Modal>
        </PageShell>
    );
}

registerScreen( 'welcome', [ 'wpuf-welcome-root' ], ( element ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <Welcome />
        </WpufProviders>
    );

    return () => root.unmount();
} );

// The one-time welcome, over the page the admin app opened with (not over
// the setup wizard or the welcome page, which say hello themselves).
const intro = window.wpufWelcomeIntro;

if ( intro ) {
    const show = () => {
        const route = new URLSearchParams( window.location.search ).get( 'wpuf_route' ) || window.location.hash.slice( 1 );

        if ( /^\/(onboarding|welcome)/.test( route ) ) {
            return;
        }

        const host = document.createElement( 'div' );
        const root = createRoot( host );

        document.body.append( host );
        root.render(
            <WelcomeIntro
                data={ intro }
                onDone={ () => {
                    root.unmount();
                    host.remove();
                } }
            />
        );
    };

    if ( 'loading' === document.readyState ) {
        document.addEventListener( 'DOMContentLoaded', show );
    } else {
        show();
    }
}
