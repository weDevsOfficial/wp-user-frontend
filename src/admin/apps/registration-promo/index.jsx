/**
 * User Frontend > Registration Forms without Pro: the admin app route
 * #/registration-forms (Admin\Screens\RegistrationPromo), on the shared
 * components. The free registration shortcode, and what Pro adds.
 *
 * @since WPUF_SINCE
 */
import { createRoot, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, PageHeader, PageShell, ProBadge, WpufProviders, notify } from '@wpuf/components';
import { registerScreen } from '../../app/client';

const data = () => window.wpufRegistrationPromo || {};

const styles = {
    wrap: { margin: '24px 0 0', padding: '0 0 40px', display: 'grid', gap: 20 },
    card: { background: '#fff', border: '1px solid #e5e7eb', borderRadius: 8, overflow: 'hidden' },
    body: { padding: 24 },
    hero: { display: 'flex', gap: 24, alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap' },
    title: { margin: 0, fontSize: 20, lineHeight: '28px', fontWeight: 600, color: '#111827', display: 'flex', alignItems: 'center', gap: 8 },
    text: { margin: '8px 0 0', fontSize: 14, lineHeight: '22px', color: '#6b7280', maxWidth: 560 },
    free: { display: 'inline-flex', padding: '2px 10px', borderRadius: 9999, fontSize: 12, fontWeight: 600, background: '#ecfdf5', color: '#047857' },
    code: { display: 'inline-flex', alignItems: 'center', gap: 8, marginTop: 16 },
    shortcode: { fontFamily: 'Menlo, Consolas, monospace', fontSize: 14, color: '#374151', background: '#f3f4f6', border: '1px solid #e5e7eb', borderRadius: 6, padding: '8px 12px' },
    banner: { width: 240, maxWidth: '100%', height: 'auto' },
    heading: { margin: '0 0 16px', fontSize: 15, fontWeight: 600, color: '#111827' },
    grid: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 12 },
    tile: { display: 'flex', alignItems: 'center', gap: 12, padding: 12, border: '1px solid #f3f4f6', borderRadius: 8, background: '#fafafa' },
    icon: { width: 40, height: 40, flex: '0 0 40px' },
    tileText: { fontSize: 14, fontWeight: 500, color: '#374151' },
    footer: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, flexWrap: 'wrap', padding: '16px 24px', borderTop: '1px solid #e5e7eb', background: '#f9fafb' },
    link: { color: '#059669', textDecoration: 'none', fontWeight: 500, fontSize: 14 },
};

/**
 * A grid of features.
 *
 * @param {Object}   props
 * @param {Object[]} props.items { icon, title }.
 */
function Tiles( { items } ) {
    return (
        <div style={ styles.grid }>
            { ( items || [] ).map( ( item ) => (
                <div key={ item.title } style={ styles.tile }>
                    <img src={ item.icon } alt="" style={ styles.icon } />
                    <span style={ styles.tileText }>{ item.title }</span>
                </div>
            ) ) }
        </div>
    );
}

function RegistrationPromo() {
    const [ copied, setCopied ] = useState( false );
    const promo = data();

    const copy = () => {
        const done = () => {
            setCopied( true );
            notify( __( 'Shortcode copied.', 'wp-user-frontend' ) );
            setTimeout( () => setCopied( false ), 2000 );
        };

        if ( window.navigator.clipboard && window.navigator.clipboard.writeText ) {
            window.navigator.clipboard.writeText( promo.shortcode ).then( done, () => notify( __( 'Copy failed. Select the shortcode and copy it.', 'wp-user-frontend' ), 'error' ) );
        }
    };

    return (
        <PageShell>
            <PageHeader utm="wpuf-registration-forms" helpUrl={ promo.learnUrl } helpLabel={ __( 'Learn more about Registration Forms', 'wp-user-frontend' ) } />
            <div style={ styles.wrap }>
                <section style={ styles.card } className="wpuf-registration-free">
                    <div style={ { ...styles.body, ...styles.hero } }>
                        <div>
                            <h1 style={ styles.title }>
                                { __( 'Registration Form', 'wp-user-frontend' ) }
                                <span style={ styles.free }>{ __( 'Free', 'wp-user-frontend' ) }</span>
                            </h1>
                            <p style={ styles.text }>
                                { __( 'Use the following shortcode to add a simple and default WordPress registration form.', 'wp-user-frontend' ) }
                            </p>
                            <div style={ styles.code }>
                                <code style={ styles.shortcode } data-registration-shortcode>{ promo.shortcode }</code>
                                <Button variant="secondary" onClick={ copy }>
                                    { copied ? __( 'Copied', 'wp-user-frontend' ) : __( 'Copy', 'wp-user-frontend' ) }
                                </Button>
                            </div>
                        </div>
                        { promo.banner && <img src={ promo.banner } alt="" style={ styles.banner } /> }
                    </div>
                    <div style={ styles.footer }>
                        <span style={ { fontSize: 13, color: '#6b7280' } }>
                            { __( 'Put the shortcode on any page to show the form.', 'wp-user-frontend' ) }
                        </span>
                        <a href={ promo.setupUrl } target="_blank" rel="noopener noreferrer" style={ styles.link }>
                            { __( 'How to set up', 'wp-user-frontend' ) } →
                        </a>
                    </div>
                </section>

                <section style={ styles.card } className="wpuf-registration-pro">
                    <div style={ styles.body }>
                        <h2 style={ styles.title }>
                            { __( 'Unlock PRO Features', 'wp-user-frontend' ) }
                            <ProBadge />
                        </h2>
                        <p style={ { ...styles.text, marginBottom: 20 } }>
                            { __( 'The registration form builder is a two way form: use it for user registration and for profile editing.', 'wp-user-frontend' ) }
                        </p>
                        <Tiles items={ promo.features } />
                        <h3 style={ { ...styles.heading, marginTop: 28 } }>{ __( 'Email Marketing Integrations', 'wp-user-frontend' ) }</h3>
                        <Tiles items={ promo.integrations } />
                    </div>
                    <div style={ styles.footer }>
                        <a href={ promo.learnUrl } target="_blank" rel="noopener noreferrer" style={ styles.link }>
                            { __( 'Learn More', 'wp-user-frontend' ) } →
                        </a>
                        <Button onClick={ () => window.open( promo.upgradeUrl, '_blank', 'noopener' ) }>
                            { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
                        </Button>
                    </div>
                </section>
            </div>
        </PageShell>
    );
}

registerScreen( 'registration-promo', [ 'wpuf-registration-promo' ], ( element ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <RegistrationPromo />
        </WpufProviders>
    );

    return () => root.unmount();
} );
