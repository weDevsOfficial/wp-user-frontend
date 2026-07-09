/**
 * SettingsSection — renders one legacy section (title + its fields) for the
 * active tab. A tab may render several sections stacked.
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import FieldRenderer from './FieldRenderer';
import Accordion from './kit/Accordion';
import ProviderTabs from './kit/ProviderTabs';
import PaymentGateways from './kit/PaymentGateways';
import GeneralSettings from './kit/GeneralSettings';
import AISettings from './kit/fields/AISettings';
import EmailIcon from './kit/email-icons';
import { STORE_NAME } from '../../stores-react/settings/constants';
import { stripTags } from './utils';

// Sections whose fields are grouped by provider (Figma gateway/provider cards).
const SVG_FB = '<svg width="22" height="22" viewBox="0 0 24 24" fill="#1877F2" xmlns="http://www.w3.org/2000/svg"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07c0 6.02 4.39 11.01 10.13 11.93v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.08 24 18.09 24 12.07z"/></svg>';
const SVG_X = '<svg width="20" height="20" viewBox="0 0 24 24" fill="#000" xmlns="http://www.w3.org/2000/svg"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.6l5.24 6.93 6.06-6.93zm-1.29 19.5h2.04L6.49 3.24H4.3L17.61 20.65z"/></svg>';
const SVG_GOOGLE = '<svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.3 9.14 5.38 12 5.38z"/></svg>';
const SVG_LINKEDIN = '<svg width="20" height="20" viewBox="0 0 24 24" fill="#0A66C2" xmlns="http://www.w3.org/2000/svg"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/></svg>';
const SVG_INSTAGRAM = '<svg width="20" height="20" viewBox="0 0 24 24" fill="#E4405F" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zm0 10.16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-10.4a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0z"/></svg>';
const SVG_GITHUB = '<svg width="22" height="22" viewBox="0 0 24 24" fill="#181717" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>';

export const PROVIDER_SECTIONS = {
    wpuf_sms: [
        { key: 'twilio', prefix: [ 'twilio_', 'twillo_' ], label: __( 'Twilio', 'wp-user-frontend' ), icon: 'gateways/twilio.png' },
        { key: 'nexmo', prefix: 'nexmo_', label: __( 'Vonage', 'wp-user-frontend' ), icon: 'gateways/vonage.png' },
        { key: 'clickatell', prefix: 'clickatell_', label: __( 'Clickatell', 'wp-user-frontend' ), icon: 'gateways/clickatell.png' },
        { key: 'smsglobal', prefix: 'smsglobal_', label: __( 'SMSGlobal', 'wp-user-frontend' ) },
    ],
    wpuf_social_api: [
        // Real brand marks (inline SVG). The Figma social-frame exports were
        // mislabeled (google.svg held the LinkedIn logo, no Google art), so use
        // the verified brand constants to guarantee each icon matches its label.
        { key: 'fb', prefix: 'fb_', label: __( 'Facebook', 'wp-user-frontend' ), iconSvg: SVG_FB },
        { key: 'twitter', prefix: 'twitter_', label: __( 'X (Twitter)', 'wp-user-frontend' ), iconSvg: SVG_X },
        { key: 'google', prefix: 'google_', label: __( 'Google', 'wp-user-frontend' ), iconSvg: SVG_GOOGLE },
        { key: 'linkedin', prefix: 'linkedin_', label: __( 'LinkedIn', 'wp-user-frontend' ), iconSvg: SVG_LINKEDIN },
        { key: 'instagram', prefix: 'instagram_', label: __( 'Instagram', 'wp-user-frontend' ), iconSvg: SVG_INSTAGRAM },
        { key: 'github', prefix: 'github_', label: __( 'GitHub', 'wp-user-frontend' ), iconSvg: SVG_GITHUB },
    ],
};


/**
 * Conditional fields: a field with `depends_on` only shows when its controlling
 * field's value matches. `depends_on_value` requires an exact match (e.g. n8n
 * authentication_type === 'basic_auth'); without it, any "on"/truthy value shows
 * the field (e.g. turnstile keys when enable_turnstile is on). Re-evaluated on
 * every value change since `values` comes from the store.
 */
// A controller value satisfies a dependency when it equals the required value;
// when no specific value is required, any "enabled"/truthy value satisfies it.
const valueMatches = ( stored, required ) => {
    if ( required === undefined || required === null || required === '' ) {
        return stored === 'on' || stored === 'yes' || stored === '1' || stored === 1 || stored === true;
    }
    return String( stored ) === String( required );
};

const dependencyMet = ( field, values ) => {
    if ( ! field.depends_on ) {
        return true;
    }

    // Object form — `{ field: requiredValue, … }` — every condition must match
    // (e.g. n8n JWT keys: authentication_type=jwt_auth AND jwt_key_type=passphrase).
    if ( typeof field.depends_on === 'object' && ! Array.isArray( field.depends_on ) ) {
        return Object.keys( field.depends_on ).every(
            ( key ) => valueMatches( values[ key ], field.depends_on[ key ] )
        );
    }

    // String form — optional `depends_on_value`, else a truthy "enabled" check.
    return valueMatches( values[ field.depends_on ], field.depends_on_value );
};

const matchesSearch = ( field, search ) => {
    if ( ! search ) {
        return true;
    }
    const needle = search.toLowerCase();
    const haystack = `${ field.label || '' } ${ field.desc || '' } ${ field.name || '' }`.toLowerCase();
    return haystack.includes( needle );
};

/**
 * A field acts as an accordion/group header when it is an `html` field that
 * carries a label but no editable value (e.g. the Email tab's "Guest Email"
 * heading). Group the fields that follow each header into a collapsible panel.
 */
const isHeading = ( field ) => field.type === 'html' && !! field.label && ! field.callback;

const groupFields = ( fields ) => {
    const groups = [];
    let current = null;

    fields.forEach( ( field ) => {
        if ( isHeading( field ) ) {
            // Heading labels may embed the legacy Pro badge SVG — strip it and
            // surface an isPro flag (from the is_pro_preview flag, with a label
            // marker fallback) so the accordion shows the Pro badge + upsell.
            current = {
                title: stripTags( field.label ),
                desc: stripTags( field.desc || '' ),
                isPro: !! field.is_pro_preview || /pro-icon|pro-badge|pro_badge/i.test( field.label ),
                fields: [],
            };
            groups.push( current );
            return;
        }
        if ( ! current ) {
            current = { title: null, fields: [] };
            groups.push( current );
        }
        current.fields.push( field );
    } );

    return groups;
};

export default function SettingsSection( { sectionId, tabTitle } ) {
    const { section, fields, values, search, forcePro } = useSelect(
        ( select ) => {
            const store = select( STORE_NAME );
            const sections = store.getSections();
            const isPro = ( window.wpuf_settings || {} ).is_pro;
            return {
                section: sections.find( ( s ) => s.id === sectionId ),
                fields: store.getFields()[ sectionId ] || [],
                values: store.getSectionValues( sectionId ),
                search: store.getSearch(),
                // Pro-only section + Pro inactive → every field is upsell.
                forcePro: ! isPro && ( store.getProSections() || [] ).includes( sectionId ),
            };
        },
        [ sectionId ]
    );

    const { setValue } = useDispatch( STORE_NAME );

    const providers = PROVIDER_SECTIONS[ sectionId ];
    const searchActive = !! ( search && search.trim() );
    const term = searchActive ? search.trim().toLowerCase() : '';
    const sectionTitleText = stripTags( section && section.title ).toLowerCase();

    // Dependency gating always applies (a field hidden by depends_on never shows).
    const depFields = fields.filter( ( f ) => f.name && dependencyMet( f, values ) );

    // A provider-section search can match on the provider (label/key) so its
    // otherwise-generic credential fields ("App Id", "App Secret") surface too.
    const providerMatches = providers && providers.some( ( p ) =>
        ( p.label || '' ).toLowerCase().includes( term ) || ( p.key || '' ).toLowerCase().includes( term )
    );

    let visibleFields;
    if ( providers ) {
        // Keep ALL dependency-valid fields — ProviderTabs does its own
        // provider-aware filtering (and search auto-selects the matched provider).
        if ( searchActive && ! providerMatches
            && ! depFields.some( ( f ) => matchesSearch( f, search ) )
            && ! sectionTitleText.includes( term ) ) {
            return null;
        }
        visibleFields = depFields;
    } else {
        visibleFields = searchActive
            ? depFields.filter( ( f ) => matchesSearch( f, search ) || sectionTitleText.includes( term ) )
            : depFields;
        if ( ! visibleFields.length ) {
            return null;
        }
    }

    const renderField = ( field ) => (
        <FieldRenderer
            key={ field.name }
            sectionId={ sectionId }
            field={ field }
            value={ values[ field.name ] }
            onChange={ setValue }
            forcePro={ forcePro }
        />
    );

    const hasHeadings = ! providers && visibleFields.some( isHeading );
    const groups = hasHeadings ? groupFields( visibleFields ) : null;

    // Section titles can carry HTML (e.g. a Pro badge) — render as plain text.
    // Hide the section heading when it just repeats the active tab title.
    const title = stripTags( section && section.title );
    const showTitle = title && title.toLowerCase() !== ( tabTitle || '' ).toLowerCase();

    return (
        <section className="wpuf-mb-10">
            { showTitle ? (
                <>
                    <h3 className="wpuf-mb-1 wpuf-text-lg wpuf-font-semibold wpuf-text-gray-900">
                        { title }
                    </h3>
                    <div className="wpuf-mb-4 wpuf-border-b wpuf-border-gray-200 wpuf-pb-2" />
                </>
            ) : null }

            { sectionId === 'wpuf_general' ? (
                <GeneralSettings
                    fields={ visibleFields }
                    renderField={ renderField }
                    values={ values }
                    onField={ ( name, val ) => setValue( sectionId, name, val ) }
                />
            ) : sectionId === 'wpuf_ai' ? (
                <AISettings fields={ visibleFields } renderField={ renderField } />
            ) : sectionId === 'wpuf_payment' && visibleFields.some( ( f ) => f.name === 'active_gateways' ) ? (
                <PaymentGateways
                    fields={ visibleFields }
                    renderField={ renderField }
                    gatewayValue={ values.active_gateways }
                    onGatewayChange={ ( v ) => setValue( sectionId, 'active_gateways', v ) }
                />
            ) : providers ? (
                <ProviderTabs providers={ providers } fields={ visibleFields } renderField={ renderField } values={ values } pro={ forcePro } search={ search } />
            ) : groups
                ? groups.map( ( group, idx ) =>
                    group.title ? (
                        <Accordion
                            key={ idx }
                            title={ group.title }
                            desc={ group.desc }
                            icon={ sectionId === 'wpuf_mails' ? <EmailIcon title={ group.title } /> : null }
                            isPro={ group.isPro || forcePro }
                            defaultOpen={ idx === 0 }
                        >
                            { group.fields.length
                                ? group.fields.map( renderField )
                                : ( group.isPro || forcePro ) ? (
                                    <p className="wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-px-3 wpuf-py-2 wpuf-text-xs wpuf-text-gray-500">
                                        { __( 'This is a WP User Frontend Pro feature.', 'wp-user-frontend' ) }
                                    </p>
                                ) : null }
                        </Accordion>
                    ) : (
                        <div key={ idx }>{ group.fields.map( renderField ) }</div>
                    )
                )
                : visibleFields.map( renderField ) }
        </section>
    );
}
