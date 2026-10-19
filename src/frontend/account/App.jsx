/**
 * The account shell in the FlyHR profile layout (frontend-react-architecture.md
 * 2.1, 5.4): profile header card, left rail, content by `?section=` (the URL
 * stays the contract; rail links are real links intercepted with
 * pushState through router.js; state in the `wpuf/account` store). Sections: native (overview, posts, edit profile, change
 * password) or the PHP output of their hook (everything else).
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { applyFilters, doAction } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';
import { Avatar, Button, Notice, Pill, ProfileHero, RailItem, ServerHtml, SkeletonLines, api, icons, loadAssets } from '@wpuf/frontend-kit';
import { AvatarPhoto, CoverPhotoActions } from './PhotoControls';
import { listen, pageOf, urlFor } from './router';
import Overview from './sections/Overview';
import Posts from './sections/Posts';
import { ChangePassword, EditProfile } from './sections/Profile';
import Subscription from './sections/Subscription';
import { store } from './store';

const { CalendarDays, LogOut, Mail, Pencil, iconByName } = icons;

// PHP sections shown in the Change Password look (header + card).
const CARD_SECTIONS = {
    'edit-profile': {
        title: __( 'Account details', 'wp-user-frontend' ),
        lead: __( 'Update your personal information and change your password here.', 'wp-user-frontend' ),
    },
    'billing-address': {
        title: __( 'Billing Address', 'wp-user-frontend' ),
        lead: __( 'The address used on your invoices and payment receipts.', 'wp-user-frontend' ),
    },
};

// WPUF's own server-rendered sections (they bring their own look); any other
// section comes from another plugin and gets the standard frame.
const OWN_SECTIONS = [ 'dashboard', 'subscription', 'edit-profile', 'change-password', 'billing-address', 'submit-post', 'invoices', 'message' ];

function HtmlSection( { slug, initial, search } ) {
    const preloaded = initial && initial.slug === slug ? initial.html : null;
    const [ state, setState ] = useState( { html: preloaded || '', loading: null === preloaded, error: '' } );
    const pagenum = pageOf( search );

    useEffect( () => {
        let cancelled = false;

        // Rendered with the page (Frontend_Account::mount_account()).
        if ( null !== preloaded ) {
            return undefined;
        }

        setState( { html: '', loading: true, error: '' } );
        api.getSection( slug, pagenum )
            // The section's scripts and sheets first, then its markup (SPA: no page load).
            .then( ( data ) => loadAssets( data.assets ).then( () => data ) )
            .then( ( data ) => ! cancelled && setState( { html: data.html, loading: false, error: '' } ) )
            .catch( ( e ) => ! cancelled && setState( { html: '', loading: false, error: e.message } ) );

        return () => {
            cancelled = true;
        };
    }, [ slug, pagenum, preloaded ] );

    if ( state.loading ) {
        return <div className="wpuf-card"><SkeletonLines lines={ 6 } /></div>;
    }

    if ( state.error ) {
        return <Notice kind="error">{ state.error }</Notice>;
    }

    return <ServerHtml html={ state.html } slug={ slug } className="wpuf-account-html" />;
}

/**
 * @param {Object}      props
 * @param {Object}      props.data Boot data, after `wpuf.frontend.account.boot` (index.jsx).
 * @param {HTMLElement} props.root Mount root.
 */
export default function App( { data, root } ) {
    const { settings, nonces } = data;
    const sections = useMemo( () => applyFilters( 'wpuf.frontend.account.sections', data.sections || [] ), [ data.sections ] );
    // The query string: a PHP section reloads when it changes (its pagination).
    const { current, search, stats, person } = useSelect( ( select ) => ( {
        current: select( store ).getSection( settings.default_tab ),
        search: select( store ).getSearch(),
        stats: select( store ).getStats(),
        person: select( store ).getProfile(),
    } ), [ settings.default_tab ] );
    const { navigate, setLocation, refreshAccount, setProfile } = useDispatch( store );
    const [ menuOpen, setMenuOpen ] = useState( false );
    // The section the server rendered with the page belongs to the first URL only.
    const [ firstSearch ] = useState( search );

    const known = sections.some( ( section ) => section.slug === current );
    const fallback = sections.some( ( item ) => item.slug === settings.default_tab ) ? settings.default_tab : ( sections[ 0 ] || {} ).slug;
    const active = known ? current : fallback;
    const section = sections.find( ( item ) => item.slug === active ) || sections[ 0 ];

    const go = useCallback( ( slug, extra = {} ) => {
        navigate( urlFor( settings.page_url, slug, extra ) );
        setMenuOpen( false );
        root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
    }, [ settings.page_url, root, navigate ] );

    useEffect( () => listen( setLocation ), [ setLocation ] );

    useEffect( () => {
        doAction( 'wpuf.frontend.account.section', active, root );
    }, [ active, root ] );

    // Back on the dashboard after another section (billing address, subscription,
    // a post deleted): its cards show fresh facts, not the ones from page load.
    const [ seen, setSeen ] = useState( active );

    useEffect( () => {
        if ( active !== seen ) {
            setSeen( active );

            if ( 'dashboard' === active ) {
                refreshAccount();
            }
        }
    }, [ active, seen, refreshAccount ] );

    // Links inside a PHP section to an account section or page (pagination)
    // stay in the app instead of loading the page again.
    const onContentClick = useCallback( ( event ) => {
        const link = event.target.closest && event.target.closest( '.wpuf-account-html a[href]' );

        if ( ! link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || 0 !== event.button || ( link.target && '_self' !== link.target ) || link.hasAttribute( 'download' ) ) {
            return;
        }

        const url = new URL( link.href, window.location.href );
        const page = new URL( settings.page_url, window.location.origin );
        const slug = url.searchParams.get( 'section' );

        // A link that does something (`action=del`, `action=edit` of the classic
        // posts table) is a real request, not a section switch.
        if ( url.origin !== page.origin || url.pathname !== page.pathname || ! slug || url.searchParams.get( 'action' ) || ! sections.some( ( item ) => item.slug === slug ) ) {
            return;
        }

        event.preventDefault();
        go( slug, { pagenum: url.searchParams.get( 'pagenum' ) } );
    }, [ settings.page_url, sections, go ] );

    const since = person.registered ? new Date( person.registered.replace( ' ', 'T' ) + 'Z' ) : null;

    let body;

    /**
     * A React component for a section (add-ons: e.g. Pro's Invoices), or null
     * for the built-in render (the PHP section for add-on sections).
     *
     * @param {Function|null} component Component
     * @param {Object}        section   The section (slug, label, kind)
     */
    const Custom = section ? applyFilters( 'wpuf.frontend.account.section.component', null, section ) : null;

    if ( ! section ) {
        body = <Notice kind="info">{ __( 'There is nothing to show on this page yet.', 'wp-user-frontend' ) }</Notice>;
    } else if ( 'dashboard' === section.slug && 'native' === section.kind ) {
        body = <Overview profile={ person } stats={ stats } sections={ sections } quickLinks={ settings.quick_links } go={ go } pageUrl={ settings.page_url } />;
    } else if ( 'posts' === section.kind ) {
        body = <Posts type={ section.post_type } label={ section.label } pageUrl={ settings.page_url } />;
    } else if ( 'edit-profile' === section.slug && 'native' === section.kind ) {
        body = <EditProfile profile={ person } nonce={ nonces.profile } onSaved={ ( fresh ) => { setProfile( fresh ); refreshAccount(); } } />;
    } else if ( 'change-password' === section.slug && 'native' === section.kind ) {
        body = <ChangePassword nonce={ nonces.password } />;
    } else if ( 'subscription' === section.kind ) {
        body = <Subscription />;
    } else if ( Custom ) {
        body = <Custom section={ section } pageUrl={ settings.page_url } search={ search } go={ go } />;
    } else {
        const html = <HtmlSection key={ search } slug={ section.slug } search={ search } initial={ search === firstSearch ? data.section_html : null } />;
        const card = CARD_SECTIONS[ section.slug ];

        // Edit Profile (a profile form) and Billing Address in the Change Password look:
        // section header, one card, the form's fields in the kit style (account.css).
        body = card ? (
            <div className={ `wpuf-account-form-section wpuf-account-form-section--${ section.slug }` }>
                <header className="wpuf-section-head">
                    <h2 className="wpuf-section-head__title">{ card.title }</h2>
                    <p className="wpuf-section-head__lead">{ card.lead }</p>
                </header>
                <section className="wpuf-card wpuf-account-form-card">
                    <div className="wpuf-card__body">{ html }</div>
                </section>
            </div>
        ) : html;

        // Another plugin's section (`wpuf_account_sections` + `wpuf_account_content_{slug}`):
        // its markup as printed, in the section header and card every section uses.
        if ( ! card && ! section.post_type && ! OWN_SECTIONS.includes( section.slug ) ) {
            body = (
                <div className={ `wpuf-account-form-section wpuf-account-custom-section wpuf-account-custom-section--${ section.slug }` }>
                    <header className="wpuf-section-head">
                        <h2 className="wpuf-section-head__title">{ section.label }</h2>
                    </header>
                    <section className="wpuf-card wpuf-account-form-card">
                        <div className="wpuf-card__body">{ html }</div>
                    </section>
                </div>
            );
        }
    }

    return (
        <div className="wpuf-account-container wpuf-account-shell">
            <ProfileHero
                person={ person }
                coverActions={ <CoverPhotoActions person={ person } /> }
                avatar={
                    <AvatarPhoto person={ person }>
                        <Avatar url={ person.avatar?.url } initials={ person.avatar?.initials } alt={ person.display_name } size={ 96 } className="wpuf-profile-avatar" />
                    </AvatarPhoto>
                }
                meta={
                    <>
                        { since && (
                            <span className="wpuf-meta-pill">
                                <CalendarDays size={ 14 } aria-hidden="true" />
                                { /* translators: %s: date */ }
                                { sprintf( __( 'Member since %s', 'wp-user-frontend' ), since.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' } ) ) }
                            </span>
                        ) }
                        { person.email && (
                            <span className="wpuf-meta-pill">
                                <Mail size={ 14 } aria-hidden="true" /> { person.email }
                            </span>
                        ) }
                        { person.website && (
                            <a className="wpuf-meta-pill" href={ person.website } target="_blank" rel="noopener noreferrer">{ person.website.replace( /^https?:\/\//, '' ) }</a>
                        ) }
                    </>
                }
                actions={
                    <>
                        { person.role_label && <Pill tone="green" className="wpuf-profile-role">{ person.role_label }</Pill> }
                        { sections.some( ( item ) => 'edit-profile' === item.slug ) && (
                            <Button className="wpuf-edit-profile-btn" onClick={ () => go( 'edit-profile' ) }>
                                <Pencil size={ 14 } aria-hidden="true" />
                                { __( 'Edit Profile', 'wp-user-frontend' ) }
                            </Button>
                        ) }
                    </>
                }
            />

            <div className="wpuf-account-body">
                <aside className="wpuf-account-sidebar">
                    <button type="button" className="wpuf-account-menu-toggle" aria-expanded={ menuOpen } onClick={ () => setMenuOpen( ( open ) => ! open ) }>
                        { section ? section.label : __( 'Menu', 'wp-user-frontend' ) }
                        <icons.ChevronDown size={ 16 } aria-hidden="true" />
                    </button>
                    <nav className={ 'wpuf-account-nav' + ( menuOpen ? ' is-open' : '' ) } aria-label={ __( 'Account sections', 'wp-user-frontend' ) }>
                        <ul>
                            { sections.map( ( item ) => {
                                const Icon = iconByName( item.icon );

                                return (
                                    <RailItem
                                        key={ item.slug }
                                        icon={ <Icon size={ 16 } /> }
                                        label={ item.label }
                                        href={ item.url || urlFor( settings.page_url, item.slug ) }
                                        active={ item.slug === active }
                                        onSelect={ () => go( item.slug ) }
                                    />
                                );
                            } ) }
                        </ul>
                    </nav>
                    { /* `.wpuf-logout-section`: the classic wrapper (Elementor account widget styles it). */ }
                    <div className="wpuf-logout-section">
                        <a className="wpuf-logout-link" href={ person.logout_url }>
                            <LogOut size={ 16 } aria-hidden="true" />
                            { /* "Log out" as develop's sidebar; "Sign out" is the greeting's link on the dashboard. */ }
                            { __( 'Log out', 'wp-user-frontend' ) }
                        </a>
                    </div>
                </aside>

                { /* eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions -- delegates real link clicks */ }
                <div className="wpuf-account-content" key={ active } onClick={ onContentClick }>
                    { body }
                </div>
            </div>
        </div>
    );
}
