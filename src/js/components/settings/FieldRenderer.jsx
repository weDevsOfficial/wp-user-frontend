/**
 * FieldRenderer — maps a legacy WPUF settings field (from wpuf_settings_fields())
 * onto the reused Form Builder React Settings kit, normalizing the legacy field
 * shape to the kit's `{ field, name, value, onChange }` contract.
 *
 * Existing components are reused wherever one fits; only radio / radio-cards /
 * html (absent from the kit) are net-new.
 */
import TextField from './kit/fields/TextField';
import NumberField from './kit/fields/NumberField';
import TextareaField from './kit/fields/TextareaField';
import SelectField from './kit/fields/SelectField';
import SelectDropdown from './kit/fields/SelectDropdown';
import CheckboxField from './kit/fields/CheckboxField';
import ToggleField from './kit/fields/ToggleField';
import MultiSelectField from './kit/fields/MultiSelectField';
import ColorPickerField from './kit/fields/ColorPickerField';
import PicRadioField from './kit/fields/PicRadioField';
import RadioField from './kit/fields/RadioField';
import RadioCardsField from './kit/fields/RadioCardsField';
import HtmlField from './kit/fields/HtmlField';
import WysiwygField from './kit/fields/WysiwygField';
import MultiSelectChips from './kit/fields/MultiSelectChips';
import FileField from './kit/fields/FileField';
import RoleEmailTemplates from './kit/fields/RoleEmailTemplates';
import ProfileFormRoles from './kit/fields/ProfileFormRoles';
import TaxSettings from './kit/fields/TaxSettings';
import ProBadge from './kit/ProBadge';
import ProPreviewWrapper from './kit/ProPreviewWrapper';
import ProNote from './kit/fields/ProNote';
import { stripTags } from './utils';

/**
 * Normalize a legacy field definition to the kit field shape.
 *
 * - Legacy `label` may carry HTML (e.g. an embedded Pro badge). We strip it to
 *   plain text and surface a separate `isPro` flag so the kit renders a clean
 *   label + our ProBadge — preserving the current Pro display.
 * - Legacy `desc` becomes the kit's `help_text` tooltip.
 */
const normalizeField = ( field ) => {
    const rawLabel = field.label || '';
    // Pro fields are registered by Free_Loader with `is_pro_preview: true`
    // (Pro re-registers them functionally without it). Use that flag as the
    // source of truth; fall back to the legacy pro-icon label marker.
    const isPro = !! field.is_pro_preview || /pro-icon|pro-badge|pro_badge/i.test( rawLabel );

    return {
        ...field,
        label: stripTags( rawLabel ),
        help_text: field.help_text || stripTags( field.desc || '' ),
        options: field.options || {},
        __isPro: isPro,
    };
};

/**
 * Some legacy fields render via a PHP `callback` instead of a `type`. Route
 * them to the right React component, preserving their stored value shape.
 */
// Callbacks that render a NON-standard / Pro / nested-storage field — render a
// safe read-only note (can't crash, never writes a bogus key). Everything else
// falls through to a normal type-based component so editable callback fields
// (AI key, temperature, …) keep their inputs.
const NON_EDITABLE_CALLBACKS = [
    'wpuf_settings_field_profile',                 // Pro, nested wpuf_profile['roles']
    'wpuf_render_login_layout_field',              // Pro, object-shaped options
    'wpuf_render_login_settings_section_header',   // section header, not a field
];

const pickByCallback = ( callback ) => {
    switch ( callback ) {
        case 'wpuf_settings_multiselect':
            // Array of keys — dropdown + searchable chips (User Directory style).
            return MultiSelectChips;
        case 'wpuf_ai_api_key_field':
            return TextField;
        case 'wpuf_ai_temperature_field':
            return NumberField;
        case 'wpuf_descriptive_text':
        case 'wpuf_render_login_settings_section_header':
            // Static heading/description block, not an input. The PHP callback
            // emits classic-screen markup; React renders the field's `desc` HTML.
            return HtmlField;
        case 'wpuf_settings_password_preview':
            // n8n auth secrets (basic_auth_password, header_auth_value, jwt_*).
            // React reads the REAL value from the option (not the masked legacy
            // preview), so a plain text input edits + saves it safely.
            return TextField;
        case 'wpuf_role_email_templates':
            // Pro role-based email template repeater (replaces legacy jQuery).
            return RoleEmailTemplates;
        default:
            // Never return null — an unmapped callback would resolve to a null
            // component and blank/crash the section. Fall back to a safe
            // read-only note so the field is always visible and never writes a
            // bogus key.
            return ProNote;
    }
};

const pickComponent = ( type ) => {
    switch ( type ) {
        case 'number':
            return NumberField;
        case 'wysiwyg':
            return WysiwygField;
        case 'textarea':
            return TextareaField;
        case 'select':
            return SelectDropdown;
        case 'checkbox':
            return CheckboxField;
        case 'toggle':
            return ToggleField;
        case 'multicheck':
        case 'multiselect':
        case 'multi-select':
            // Array of selected keys — dropdown + chips (User Directory style).
            return MultiSelectChips;
        case 'color':
        case 'color-picker':
            return ColorPickerField;
        case 'pic-radio':
            return PicRadioField;
        case 'radio':
        case 'radio_inline':
            return RadioField;
        case 'gateway_selector':
            return RadioCardsField;
        case 'html':
            return HtmlField;
        case 'file':
            return FileField;
        case 'text':
        case 'email':
        case 'url':
        case 'password':
        default:
            return TextField;
    }
};

export default function FieldRenderer( { sectionId, field, value, onChange, forcePro = false } ) {
    const type = field.type || 'text';
    const normalized = normalizeField( field );
    const isPro = normalized.__isPro || forcePro;

    // AI provider renders as single-select provider cards (with logos).
    const isAiProvider = field.name === 'ai_provider';
    // The login-form layout picker is registered with an array `callback`
    // (render_radio_image_field) that would fall through to ProNote — but its
    // schema carries image options, so render the picture-radio directly.
    const isLayoutPicker = field.name === 'wpuf_login_form_layout' && field.options && Object.keys( field.options ).length;
    // Pro role-based email templates register with an ARRAY php callback that
    // can't be matched by name in JSON — route by the field-name suffix instead.
    const isRoleTemplates = typeof field.name === 'string' && /_role_templates$/.test( field.name );
    // Email "Select Roles" — a callback in legacy, a role multiselect in React.
    const isDefaultRoles = typeof field.name === 'string' && /_default_roles$/.test( field.name );
    // Role → profile-form mapping table (nested wpuf_profile['roles'] storage).
    const isProfileRoles = field.name === 'profile_form_roles';
    // Tax base country/state + rate table (own-option storage).
    const isTax = field.name === 'wpuf_base_country_state' || field.name === 'wpuf_tax_rates';
    let Component;
    if ( isAiProvider ) {
        Component = RadioCardsField;
    } else if ( isLayoutPicker ) {
        Component = PicRadioField;
    } else if ( isRoleTemplates ) {
        Component = RoleEmailTemplates;
    } else if ( isDefaultRoles ) {
        Component = MultiSelectChips;
    } else if ( isProfileRoles ) {
        Component = ProfileFormRoles;
    } else if ( isTax ) {
        Component = TaxSettings;
    } else {
        Component = ( field.callback && pickByCallback( field.callback ) ) || pickComponent( type );
    }

    // Adapt the kit's onChange( name, value ) to the section-scoped store setter.
    const handleChange = ( name, val ) => onChange( sectionId, name, val );

    const extraProps = isAiProvider
        ? { single: true }
        : type === 'radio_inline'
            ? { inline: true }
            : {};
    const isProInactive = isPro && ! ( window.wpuf_settings || {} ).is_pro;

    // Render the Pro badge INLINE right after the label text. The kit renders
    // `{field.label}` as a child, so passing a node (text + badge) places the
    // badge next to the label instead of far across the row.
    const fieldForComponent = isPro
        ? {
            ...normalized,
            label: (
                <span className="wpuf-inline-flex wpuf-items-center wpuf-gap-2">
                    { normalized.label }
                    <ProBadge />
                </span>
            ),
        }
        : normalized;

    const control = (
        <Component
            field={ fieldForComponent }
            name={ field.name }
            value={ value }
            onChange={ handleChange }
            { ...extraProps }
        />
    );

    return (
        <div className="wpuf-mt-6 wpuf-input-container">
            { isProInactive ? <ProPreviewWrapper>{ control }</ProPreviewWrapper> : control }
        </div>
    );
}
