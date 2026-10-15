import { RawHTML } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import LegacySlot, { getLegacySlots } from '../../common/LegacySlot';
import ToggleField from './fields/ToggleField';
import TextField from './fields/TextField';
import NumberField from './fields/NumberField';
import TextareaField from './fields/TextareaField';
import SelectField from './fields/SelectField';
import MultiSelectField from './fields/MultiSelectField';
import CheckboxField from './fields/CheckboxField';
import TrailingTextField from './fields/TrailingTextField';
import DateField from './fields/DateField';
import ColorPickerField from './fields/ColorPickerField';
import PicRadioField from './fields/PicRadioField';
import RichTextField from './fields/RichTextField';
import CardRadioField from './fields/CardRadioField';
import InlineFieldsGroup from './fields/InlineFieldsGroup';
import SubmitConditionalLogic from '../ConditionalLogic/SubmitConditionalLogic';
import IntegrationConditionalLogic from '../ConditionalLogic/IntegrationConditionalLogic';
import copyMailTag from '../../utils/copyMailTag';
import TaxonomyDefaults from './fields/TaxonomyDefaults';
import SettingInfo from './fields/SettingInfo';
import { Notice } from '@wpuf/components';
import { builderData } from '../../utils/boot';

const FIELD_MAP = {
    toggle: ToggleField,
    text: TextField,
    number: NumberField,
    textarea: TextareaField,
    select: SelectField,
    'multi-select': MultiSelectField,
    checkbox: CheckboxField,
    'trailing-text': TrailingTextField,
    date: DateField,
    'color-picker': ColorPickerField,
    'pic-radio': PicRadioField,
    'rich-text': RichTextField,
    'card-radio': CardRadioField,
};

// The label attribute is HTML-escaped by PHP (esc_attr).
const decodeLabel = ( text ) => {
    const area = document.createElement( 'textarea' );
    area.innerHTML = text || '';
    return area.value;
};

/**
 * Integration conditions shown after a registration settings row. Develop's
 * Pro modules printed a Vue `<integration-conditional-logic>` tag on the
 * row's after-hook (Mailchimp after Double Optin); the bridge leaves WPUF's
 * own listeners out (and wp_kses would strip a Vue tag), so React renders
 * them here (4.5b). The row exists only while its module is active.
 *
 * @param {string} slotKey Settings key of the row.
 * @return {Array} [ { integrationName, settingsPath, label } ].
 */
function integrationConditionsAfter( slotKey ) {
    const data = builderData();

    if ( 'wpuf_profile' !== data.form_type || ! data.is_pro_active ) {
        return [];
    }

    /**
     * Filters the integration conditions rendered after registration settings rows.
     *
     * @param {Object} map Settings key => [ { integrationName, settingsPath, label } ].
     */
    const map = applyFilters( 'wpuf.formBuilder.integrationConditions', {
        enable_double_optin: [
            { integrationName: 'mailchimp', settingsPath: 'integrations.mailchimp.wpuf_cond', label: __( 'Conditional Logic', 'wp-user-frontend' ) },
        ],
    } );

    return Array.isArray( map[ slotKey ] ) ? map[ slotKey ] : [];
}

/**
 * Dispatches to the appropriate field component based on field type.
 *
 * Wraps each field in Vue's `mt-6 wpuf-input-container` div
 * matching the wpuf_render_settings_field() PHP function.
 */
export default function SettingsField( { slotKey, hideControl = false, ...props } ) {
    const row = slotKey ? getLegacySlots().settings[ slotKey ] : null;
    // Pro's submit-button conditions follow the Limit Form Entries row on post
    // forms, shown even while the limit is off (develop printed them on the
    // after-limit_message hook, outside the row's hidden container).
    const data = builderData();
    const submitConditions = 'limit_message' === slotKey && data.is_pro_active && 'wpuf_profile' !== data.form_type;
    const integrations = integrationConditionsAfter( slotKey );

    if ( ! row ) {
        return (
            <>
                { ! hideControl && <SettingsFieldControl fieldKey={ slotKey } { ...props } /> }
                { submitConditions && <SubmitConditionalLogic /> }
                { integrations.map( ( item ) => <IntegrationConditionalLogic key={ item.settingsPath } { ...item } /> ) }
            </>
        );
    }

    // Pro prints develop's Vue submit-button conditions on the after-limit_message
    // hook; React renders that block itself (4.4e) and keeps the rest of the output.
    const vueSubmit = /<submit-button-conditional-logics[\s\S]*?<\/submit-button-conditional-logics>/;
    const hasSubmitConditions = vueSubmit.test( row.after || '' );
    const after = hasSubmitConditions ? row.after.replace( vueSubmit, '' ) : row.after;
    const submitLabel = hasSubmitConditions ? ( /label="([^"]*)"/.exec( row.after.match( vueSubmit )[ 0 ] ) || [] )[ 1 ] : '';

    // Output other plugins printed before / after this row (develop's
    // wpuf_before|after_{post,registration}_form_settings_field hooks). It stays
    // when a dependency hides the row: develop hid only the row's input container.
    return (
        <>
            <LegacySlot id={ `setting-before-${ slotKey }` } html={ row.before } />
            { ! hideControl && <SettingsFieldControl fieldKey={ slotKey } { ...props } /> }
            <LegacySlot id={ `setting-after-${ slotKey }` } html={ after } />
            { ( hasSubmitConditions || submitConditions ) && <SubmitConditionalLogic label={ decodeLabel( submitLabel ) } /> }
            { integrations.map( ( item ) => <IntegrationConditionalLogic key={ item.settingsPath } { ...item } /> ) }
        </>
    );
}

/**
 * The control for one settings row.
 */
function SettingsFieldControl( { fieldKey, field, name, value, onChange, settings, resolveValue } ) {
    // Develop replaces the static Default Category row with one row per
    // hierarchical taxonomy of the selected post type (form-builder.js
    // populate_default_categories).
    if ( 'default_category' === fieldKey ) {
        return <TaxonomyDefaults settings={ settings || {} } onChange={ onChange } />;
    }

    // inline_fields is a special container type — Vue uses mt-6 flex wpuf-input-container
    if ( field.type === 'inline_fields' || ( ! field.type && field.fields ) ) {
        return (
            <div className="mt-5 flex wpuf-input-container">
                <InlineFieldsGroup
                    field={ field }
                    resolveValue={ resolveValue }
                    onChange={ onChange }
                />
            </div>
        );
    }

    // submit-button-conditional-logics is a special Vue component — render React equivalent
    if ( field.type === 'submit-button-conditional-logics' ) {
        return (
            <div className="mt-5 wpuf-input-container">
                <SubmitConditionalLogic label={ field.label } />
            </div>
        );
    }

    // `note`: a warning notice (e.g. a newsletter module with no API key). The
    // text is wp_kses_post-filtered HTML (it may carry a link).
    if ( field.type === 'note' ) {
        return (
            <div className="my-4 wpuf-input-container">
                <Notice tone="warning">
                    <RawHTML>{ field.note || '' }</RawHTML>
                </Notice>
            </div>
        );
    }

    const FieldComponent = FIELD_MAP[ field.type ];

    if ( ! FieldComponent ) {
        return null;
    }

    // Vue wraps every field in <div class="mt-6 wpuf-input-container"> and
    // prints `long_help` under any field. `note` and `long_help` are filtered
    // with wp_kses_post before they leave PHP.
    return (
        <div className="mt-5 wpuf-input-container">
            <FieldComponent
                field={ field }
                name={ name }
                value={ value }
                onChange={ onChange }
            />
            { /* `info` (structured help) wins over the classic builder's `long_help` HTML. */ }
            { field.info ? <SettingInfo info={ field.info } /> : field.long_help && (
                <RawHTML className="text-sm mt-4 wpuf-long-help" onClick={ copyMailTag }>{ field.long_help }</RawHTML>
            ) }
        </div>
    );
}
