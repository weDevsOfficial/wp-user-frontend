/**
 * The Overview (the old Dashboard section) in the FlyHR shape: the stats
 * strip, an Account card of the user's facts, the subscription and billing
 * cards when the site has them, and the quick links paragraph kept for the
 * `wpuf_my_account_tab_links` consumers.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Card, DetailList, StatCard, icons } from '@wpuf/frontend-kit';

const { Activity, AtSign, BadgeCheck, BadgeDollarSign, CalendarClock, CalendarDays, Files, Globe, Mail, MapPin, NotebookText, UserRound } = icons;

const TONES = [ 'gray', 'green', 'violet', 'amber', 'blue' ];

export default function Overview( { profile, stats, sections, go, pageUrl } ) {
    const subscription = stats.find( ( stat ) => 'subscription' === stat.kind );
    const billing = stats.find( ( stat ) => 'billing' === stat.kind );
    const strip = stats.filter( ( stat ) => 'billing' !== stat.kind );

    const statIcon = ( stat ) => {
        if ( 'subscription' === stat.kind ) {
            return <BadgeDollarSign size={ 20 } />;
        }

        if ( 'posts' === stat.kind ) {
            return <Files size={ 20 } />;
        }

        const Icon = icons.iconByName( stat.icon );

        return <Icon size={ 20 } />;
    };

    const sub = ( stat ) => {
        if ( 'posts' === stat.kind && stat.detail ) {
            const pending = Number( stat.detail.pending || 0 );
            const draft = Number( stat.detail.draft || 0 );
            const parts = [];

            if ( pending ) {
                /* translators: %d: number of posts */
                parts.push( sprintf( __( '%d pending', 'wp-user-frontend' ), pending ) );
            }

            if ( draft ) {
                /* translators: %d: number of posts */
                parts.push( sprintf( __( '%d draft', 'wp-user-frontend' ), draft ) );
            }

            return parts.join( ', ' );
        }

        if ( 'subscription' === stat.kind && stat.detail?.expire ) {
            /* translators: %s: date */
            return sprintf( __( 'until %s', 'wp-user-frontend' ), stat.detail.expire );
        }

        return stat.sub || '';
    };

    const since = profile.registered ? new Date( profile.registered.replace( ' ', 'T' ) + 'Z' ).toLocaleDateString( undefined, { year: 'numeric', month: 'long', day: 'numeric' } ) : '';
    const links = sections.filter( ( section ) => 'dashboard' !== section.slug );

    return (
        <div className="wpuf-overview">
            { strip.length > 0 && (
                <div className="wpuf-overview__stats">
                    { strip.map( ( stat, index ) => (
                        <StatCard
                            key={ stat.id }
                            icon={ statIcon( stat ) }
                            label={ stat.label }
                            value={ Array.isArray( stat.value ) ? stat.value.length : stat.value }
                            sub={ sub( stat ) }
                            tone={ TONES[ index % TONES.length ] }
                            onClick={ stat.section && sections.some( ( section ) => section.slug === stat.section ) ? () => go( stat.section ) : undefined }
                        />
                    ) ) }
                </div>
            ) }

            <Card title={ __( 'Account', 'wp-user-frontend' ) }>
                <DetailList
                    columns={ 3 }
                    items={ [
                        { label: __( 'Name', 'wp-user-frontend' ), value: [ profile.first_name, profile.last_name ].filter( Boolean ).join( ' ' ) || profile.display_name, icon: <UserRound size={ 14 } /> },
                        { label: __( 'Username', 'wp-user-frontend' ), value: profile.username, icon: <AtSign size={ 14 } /> },
                        { label: __( 'Email', 'wp-user-frontend' ), value: profile.email, icon: <Mail size={ 14 } /> },
                        { label: __( 'Role', 'wp-user-frontend' ), value: profile.role_label, icon: <BadgeCheck size={ 14 } /> },
                        { label: __( 'Member since', 'wp-user-frontend' ), value: since, icon: <CalendarDays size={ 14 } /> },
                        { label: __( 'Website', 'wp-user-frontend' ), value: profile.website, icon: <Globe size={ 14 } /> },
                        { label: __( 'Bio', 'wp-user-frontend' ), value: profile.bio, icon: <NotebookText size={ 14 } /> },
                    ] }
                />
            </Card>

            { subscription && (
                <Card title={ __( 'Subscription', 'wp-user-frontend' ) }>
                    <DetailList
                        columns={ 3 }
                        items={ [
                            { label: __( 'Pack', 'wp-user-frontend' ), value: subscription.value, icon: <BadgeDollarSign size={ 14 } /> },
                            { label: __( 'Status', 'wp-user-frontend' ), value: subscription.detail?.status, icon: <Activity size={ 14 } /> },
                            { label: __( 'Expires', 'wp-user-frontend' ), value: subscription.detail?.expire, icon: <CalendarClock size={ 14 } /> },
                        ] }
                    />
                </Card>
            ) }

            { billing && Array.isArray( billing.value ) && billing.value.length > 0 && (
                <Card title={ __( 'Billing Address', 'wp-user-frontend' ) }>
                    <DetailList columns={ 1 } items={ [ { label: __( 'Address', 'wp-user-frontend' ), value: billing.value.join( ', ' ), icon: <MapPin size={ 14 } /> } ] } />
                </Card>
            ) }

            { links.length > 0 && (
                <p className="wpuf-overview__links">
                    { __( 'From your account dashboard you can view your dashboard, manage your', 'wp-user-frontend' ) }{ ' ' }
                    { links.map( ( section, index ) => (
                        <span key={ section.slug }>
                            <a href={ section.url || `${ pageUrl }?section=${ section.slug }` } onClick={ ( event ) => { event.preventDefault(); go( section.slug ); } }>{ section.label }</a>
                            { index < links.length - 1 ? ', ' : '' }
                        </span>
                    ) ) }
                </p>
            ) }
        </div>
    );
}
