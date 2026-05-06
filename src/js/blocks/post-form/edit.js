// DESCRIPTION: Editor component for the post form block in wp-user-frontend.
// Content settings in default InspectorControls, style controls in InspectorControls group="styles".

import {
    InspectorControls,
    PanelColorSettings,
    useBlockProps,
    useSettings,
    __experimentalFontFamilyControl as FontFamilyControl,
    __experimentalFontAppearanceControl as FontAppearanceControl,
} from '@wordpress/block-editor';
import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import {
    PanelBody,
    SelectControl,
    BorderBoxControl,
    __experimentalUnitControl as UnitControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import { TEMPLATES } from './templates';

const FONT_SIZE_UNITS = [
    { value: 'px', label: 'px', default: 14 },
    { value: 'em', label: 'em', default: 1 },
    { value: 'rem', label: 'rem', default: 1 },
];

const SPACING_UNITS = [
    { value: 'px', label: 'px', default: 8 },
    { value: 'em', label: 'em', default: 0.5 },
    { value: 'rem', label: 'rem', default: 0.5 },
    { value: '%', label: '%', default: 5 },
];

const BORDER_WIDTH_UNITS = [
    { value: 'px', label: 'px', default: 1 },
    { value: 'em', label: 'em', default: 0.1 },
    { value: 'rem', label: 'rem', default: 0.1 },
];

const BORDER_RADIUS_UNITS = [
    { value: 'px', label: 'px', default: 4 },
    { value: 'em', label: 'em', default: 0.25 },
    { value: 'rem', label: 'rem', default: 0.25 },
    { value: '%', label: '%', default: 5 },
];

/**
 * Get the merged flat array of font families from theme settings.
 *
 * useSettings('typography.fontFamilies') returns an object with
 * { default, theme, custom } keys. FontFamilyControl expects a
 * flat array, so we merge all sources here.
 *
 * @returns {Array} Flat array of font family objects
 */
function useMergedFontFamilies() {
    const [ fontFamilies ] = useSettings( 'typography.fontFamilies' );

    if ( ! fontFamilies ) {
        return [];
    }

    return [
        ...( fontFamilies?.default || [] ),
        ...( fontFamilies?.theme || [] ),
        ...( fontFamilies?.custom || [] ),
    ];
}

/**
 * Get the fontFace array for a given font-family value from theme settings.
 *
 * @param {string} fontFamily         The selected font-family CSS value
 * @param {Array}  mergedFontFamilies Pre-merged flat array from useMergedFontFamilies
 *
 * @returns {Array} Array of font face objects for the selected family
 */
function getFontFamilyFaces( fontFamily, mergedFontFamilies ) {
    if ( ! fontFamily || ! mergedFontFamilies?.length ) {
        return [];
    }

    const match = mergedFontFamilies.find( ( f ) => f.fontFamily === fontFamily );

    return match?.fontFace || [];
}

/**
 * Generate a short random string for block scoping.
 *
 * @returns {string}
 */
function generateBlockId() {
    return 'wpuf-pf-' + Math.random().toString(36).slice(2, 9);
}

/**
 * Template picker card with realistic form mini-preview.
 * Shows a label bar, input field, and button using the template's actual colors.
 */
function TemplateCard({ template, isActive, onSelect }) {
    const { preview, attributes: tplAttrs } = template;
    return (
        <button
            type="button"
            className={`wpuf-pf-template-card${isActive ? ' is-active' : ''}`}
            onClick={() => onSelect(template)}
            aria-pressed={isActive}
        >
            <span className="wpuf-pf-template-card__badge">✓</span>
            <div
                className="wpuf-pf-template-preview"
                style={{ background: preview.bg }}
            >
                {/* First field row */}
                <div className="wpuf-pf-template-preview__row">
                    <div
                        className="wpuf-pf-template-preview__label"
                        style={{ background: preview.text }}
                    />
                    <div
                        className="wpuf-pf-template-preview__input"
                        style={{
                            background: tplAttrs.fieldBackgroundColor || preview.bg,
                            borderColor: preview.border,
                            borderWidth: tplAttrs.fieldBorder?.width || '1px',
                            borderRadius: tplAttrs.fieldBorderRadius,
                        }}
                    />
                </div>
                {/* Second field row */}
                <div className="wpuf-pf-template-preview__row">
                    <div
                        className="wpuf-pf-template-preview__label"
                        style={{ background: preview.text }}
                    />
                    <div
                        className="wpuf-pf-template-preview__input"
                        style={{
                            background: tplAttrs.fieldBackgroundColor || preview.bg,
                            borderColor: preview.border,
                            borderWidth: tplAttrs.fieldBorder?.width || '1px',
                            borderRadius: tplAttrs.fieldBorderRadius,
                        }}
                    />
                </div>
                {/* Button */}
                <div
                    className="wpuf-pf-template-preview__button"
                    style={{
                        background: preview.button,
                        borderRadius: tplAttrs.buttonBorderRadius,
                    }}
                />
            </div>
            <div className="wpuf-pf-template-card__info">
                <p className="wpuf-pf-template-card__label">{template.label}</p>
                <p className="wpuf-pf-template-card__desc">{template.description}</p>
            </div>
        </button>
    );
}

export default function Edit({ attributes, setAttributes }) {
    const {
        blockId,
        formId,
        activeTemplate,
        labelPosition,
        labelFontSize,
        labelColor,
        labelFontWeight,
        labelFontFamily,
        labelFontStyle,
        helpTextColor,
        helpTextFontFamily,
        helpTextFontSize,
        helpTextFontStyle,
        helpTextFontWeight,
        fieldBorder,
        fieldBorderRadius,
        fieldPaddingV,
        fieldPaddingH,
        fieldBackgroundColor,
        fieldTextColor,
        fieldPlaceholderColor,
        fieldFontFamily,
        fieldFontSize,
        fieldFontStyle,
        fieldFontWeight,
        fieldMarginBottom,
        fieldFocusBorderColor,
        buttonBackgroundColor,
        buttonTextColor,
        buttonFontFamily,
        buttonFontStyle,
        buttonFontWeight,
        buttonBorderWidth,
        buttonBorderRadius,
        buttonFontSize,
        buttonPaddingV,
        buttonPaddingH,
        uploadButtonBackgroundColor,
        uploadButtonTextColor,
        uploadButtonFontFamily,
        uploadButtonFontStyle,
        uploadButtonFontWeight,
        uploadButtonBorderColor,
        uploadButtonBorderWidth,
        uploadButtonBorderRadius,
        uploadButtonFontSize,
        uploadButtonPaddingV,
        uploadButtonPaddingH,
        msButtonBackgroundColor,
        msButtonTextColor,
        msButtonFontFamily,
        msButtonFontStyle,
        msButtonFontWeight,
        msButtonBorderColor,
        msButtonBorderWidth,
        msButtonBorderRadius,
        msButtonFontSize,
        msButtonPaddingV,
        msButtonPaddingH,
        msActiveBgColor,
        msActiveTextColor,
        msInactiveBgColor,
    } = attributes;

    const blockProps = useBlockProps();
    const { createNotice } = useDispatch(noticesStore);

    const mergedFontFamilies    = useMergedFontFamilies();
    const labelFontFaces        = getFontFamilyFaces( labelFontFamily, mergedFontFamilies );
    const fieldFontFaces        = getFontFamilyFaces( fieldFontFamily, mergedFontFamilies );
    const helpTextFontFaces     = getFontFamilyFaces( helpTextFontFamily, mergedFontFamilies );
    const buttonFontFaces       = getFontFamilyFaces( buttonFontFamily, mergedFontFamilies );
    const uploadButtonFontFaces = getFontFamilyFaces( uploadButtonFontFamily, mergedFontFamilies );
    const msButtonFontFaces     = getFontFamilyFaces( msButtonFontFamily, mergedFontFamilies );

    // Assign blockId once on first insert
    useEffect(() => {
        if (!blockId) {
            setAttributes({ blockId: generateBlockId() });
        }
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    // SSR loading overlay state
    const [isUpdating, setIsUpdating] = useState(false);
    const debounceTimerRef = useRef(null);
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        if (debounceTimerRef.current) {
            clearTimeout(debounceTimerRef.current);
        }
        debounceTimerRef.current = setTimeout(() => {
            setIsUpdating(true);
        }, 150);
        return () => clearTimeout(debounceTimerRef.current);
    }, [attributes]);

    // Store previous attributes for undo
    const prevAttrsRef = useRef(null);

    const handleTemplateSelect = useCallback(
        (template) => {
            if (template.id === activeTemplate) {
                return;
            }

            // Save current state for undo
            const snapshot = { ...attributes };
            prevAttrsRef.current = snapshot;

            // Apply immediately
            setAttributes(template.attributes);

            // Show snackbar with undo
            createNotice(
                'info',
                template.label + ' ' + __('template applied.', 'wp-user-frontend'),
                {
                    type: 'snackbar',
                    isDismissible: true,
                    actions: [
                        {
                            label: __('Undo', 'wp-user-frontend'),
                            onClick: () => {
                                if (prevAttrsRef.current) {
                                    setAttributes(prevAttrsRef.current);
                                    prevAttrsRef.current = null;
                                }
                            },
                        },
                    ],
                }
            );
        },
        [activeTemplate, attributes, setAttributes, createNotice]
    );

    const editorData = window.wpufPostForm || {};
    const forms = editorData.forms || [];

    // Build SelectControl options: "Select a form" placeholder + all post forms
    const formOptions = [
        { value: '0', label: __('— Select a post form —', 'wp-user-frontend') },
        ...forms.map((f) => ({
            value: String(f.id),
            label: f.title,
        })),
    ];

    const selectedForm = forms.find( ( f ) => f.id === formId );
    const isMultistep  = selectedForm?.is_multistep || false;

    const handleFormChange = (val) => {
        setAttributes({
            formId: parseInt(val, 10),
        });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody
                    title={__('Form Settings', 'wp-user-frontend')}
                    initialOpen={true}
                >
                    <SelectControl
                        label={__('Post Form', 'wp-user-frontend')}
                        value={String(formId || 0)}
                        options={formOptions}
                        onChange={handleFormChange}
                        help={__(
                            'Select the post form to display.',
                            'wp-user-frontend'
                        )}
                    />
                </PanelBody>
            </InspectorControls>

            <InspectorControls group="styles">
                <PanelBody
                    title={__('Templates', 'wp-user-frontend')}
                    initialOpen={true}
                >
                    <div className="wpuf-pf-templates">
                        {TEMPLATES.map((tpl) => (
                            <TemplateCard
                                key={tpl.id}
                                template={tpl}
                                isActive={activeTemplate === tpl.id}
                                onSelect={handleTemplateSelect}
                            />
                        ))}
                    </div>
                </PanelBody>

                <PanelBody
                    title={__('Label Styles', 'wp-user-frontend')}
                    initialOpen={false}
                >
                    <FontFamilyControl
                        __next40pxDefaultSize
                        fontFamilies={mergedFontFamilies}
                        value={labelFontFamily}
                        onChange={(val) => setAttributes({ labelFontFamily: val })}
                    />

                    <FontAppearanceControl
                        __next40pxDefaultSize
                        value={{ fontStyle: labelFontStyle, fontWeight: labelFontWeight }}
                        onChange={({ fontStyle, fontWeight }) =>
                            setAttributes({ labelFontStyle: fontStyle, labelFontWeight: fontWeight })
                        }
                        fontFamilyFaces={labelFontFaces}
                        hasFontStyles={true}
                        hasFontWeights={true}
                    />

                    <ToggleGroupControl
                        label={__('Label Position', 'wp-user-frontend')}
                        value={labelPosition}
                        className="wpuf-mt-2"
                        onChange={(val) => setAttributes({ labelPosition: val })}
                        isBlock
                    >
                        <ToggleGroupControlOption value="above" label={__('Above', 'wp-user-frontend')} />
                        <ToggleGroupControlOption value="left" label={__('Left', 'wp-user-frontend')} />
                        <ToggleGroupControlOption value="right" label={__('Right', 'wp-user-frontend')} />
                        <ToggleGroupControlOption value="hidden" label={__('Hidden', 'wp-user-frontend')} />
                    </ToggleGroupControl>

                    <UnitControl
                        label={__('Font Size', 'wp-user-frontend')}
                        value={labelFontSize}
                        onChange={(val) => setAttributes({ labelFontSize: val })}
                        units={FONT_SIZE_UNITS}
                    />
                </PanelBody>

                <PanelBody
                    title={__('Field Styles', 'wp-user-frontend')}
                    initialOpen={false}
                >
                    <FontFamilyControl
                        __next40pxDefaultSize
                        fontFamilies={mergedFontFamilies}
                        value={fieldFontFamily}
                        onChange={(val) => setAttributes({ fieldFontFamily: val })}
                    />

                    <FontAppearanceControl
                        __next40pxDefaultSize
                        value={{ fontStyle: fieldFontStyle, fontWeight: fieldFontWeight }}
                        onChange={({ fontStyle, fontWeight }) =>
                            setAttributes({ fieldFontStyle: fontStyle, fieldFontWeight: fontWeight })
                        }
                        fontFamilyFaces={fieldFontFaces}
                        hasFontStyles={true}
                        hasFontWeights={true}
                    />

                    <UnitControl
                        label={__('Font Size', 'wp-user-frontend')}
                        value={fieldFontSize}
                        onChange={(val) => setAttributes({ fieldFontSize: val })}
                        units={FONT_SIZE_UNITS}
                    />

                    <BorderBoxControl
                        __next40pxDefaultSize
                        label={__('Border', 'wp-user-frontend')}
                        value={fieldBorder}
                        onChange={(val) => setAttributes({ fieldBorder: val || {} })}
                    />

                    <UnitControl
                        label={__('Border Radius', 'wp-user-frontend')}
                        value={fieldBorderRadius}
                        onChange={(val) => setAttributes({ fieldBorderRadius: val })}
                        units={BORDER_RADIUS_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Vertical', 'wp-user-frontend')}
                        value={fieldPaddingV}
                        onChange={(val) => setAttributes({ fieldPaddingV: val })}
                        units={SPACING_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Horizontal', 'wp-user-frontend')}
                        value={fieldPaddingH}
                        onChange={(val) => setAttributes({ fieldPaddingH: val })}
                        units={SPACING_UNITS}
                    />

                    <UnitControl
                        label={__('Margin Bottom', 'wp-user-frontend')}
                        value={fieldMarginBottom}
                        onChange={(val) => setAttributes({ fieldMarginBottom: val })}
                        units={SPACING_UNITS}
                    />
                </PanelBody>

                <PanelBody
                    title={__('Help Text Styles', 'wp-user-frontend')}
                    initialOpen={false}
                >
                    <FontFamilyControl
                        __next40pxDefaultSize
                        fontFamilies={mergedFontFamilies}
                        value={helpTextFontFamily}
                        onChange={(val) => setAttributes({ helpTextFontFamily: val })}
                    />

                    <FontAppearanceControl
                        __next40pxDefaultSize
                        value={{ fontStyle: helpTextFontStyle, fontWeight: helpTextFontWeight }}
                        onChange={({ fontStyle, fontWeight }) =>
                            setAttributes({ helpTextFontStyle: fontStyle, helpTextFontWeight: fontWeight })
                        }
                        fontFamilyFaces={helpTextFontFaces}
                        hasFontStyles={true}
                        hasFontWeights={true}
                    />

                    <UnitControl
                        label={__('Font Size', 'wp-user-frontend')}
                        value={helpTextFontSize}
                        onChange={(val) => setAttributes({ helpTextFontSize: val })}
                        units={FONT_SIZE_UNITS}
                    />
                </PanelBody>

                <PanelBody
                    title={__('Button Styles', 'wp-user-frontend')}
                    initialOpen={false}
                >
                    <FontFamilyControl
                        __next40pxDefaultSize
                        fontFamilies={mergedFontFamilies}
                        value={buttonFontFamily}
                        onChange={(val) => setAttributes({ buttonFontFamily: val })}
                    />

                    <FontAppearanceControl
                        __next40pxDefaultSize
                        value={{ fontStyle: buttonFontStyle, fontWeight: buttonFontWeight }}
                        onChange={({ fontStyle, fontWeight }) =>
                            setAttributes({ buttonFontStyle: fontStyle, buttonFontWeight: fontWeight })
                        }
                        fontFamilyFaces={buttonFontFaces}
                        hasFontStyles={true}
                        hasFontWeights={true}
                    />

                    <UnitControl
                        label={__('Font Size', 'wp-user-frontend')}
                        value={buttonFontSize}
                        onChange={(val) => setAttributes({ buttonFontSize: val })}
                        units={FONT_SIZE_UNITS}
                    />

                    <UnitControl
                        label={__('Border Width', 'wp-user-frontend')}
                        value={buttonBorderWidth}
                        onChange={(val) => setAttributes({ buttonBorderWidth: val })}
                        units={BORDER_WIDTH_UNITS}
                    />

                    <UnitControl
                        label={__('Border Radius', 'wp-user-frontend')}
                        value={buttonBorderRadius}
                        onChange={(val) => setAttributes({ buttonBorderRadius: val })}
                        units={BORDER_RADIUS_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Vertical', 'wp-user-frontend')}
                        value={buttonPaddingV}
                        onChange={(val) => setAttributes({ buttonPaddingV: val })}
                        units={SPACING_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Horizontal', 'wp-user-frontend')}
                        value={buttonPaddingH}
                        onChange={(val) => setAttributes({ buttonPaddingH: val })}
                        units={SPACING_UNITS}
                    />
                </PanelBody>

                <PanelBody
                    title={__('Upload Button Styles', 'wp-user-frontend')}
                    initialOpen={false}
                >
                    <FontFamilyControl
                        __next40pxDefaultSize
                        fontFamilies={mergedFontFamilies}
                        value={uploadButtonFontFamily}
                        onChange={(val) => setAttributes({ uploadButtonFontFamily: val })}
                    />

                    <FontAppearanceControl
                        __next40pxDefaultSize
                        value={{ fontStyle: uploadButtonFontStyle, fontWeight: uploadButtonFontWeight }}
                        onChange={({ fontStyle, fontWeight }) =>
                            setAttributes({ uploadButtonFontStyle: fontStyle, uploadButtonFontWeight: fontWeight })
                        }
                        fontFamilyFaces={uploadButtonFontFaces}
                        hasFontStyles={true}
                        hasFontWeights={true}
                    />

                    <UnitControl
                        label={__('Font Size', 'wp-user-frontend')}
                        value={uploadButtonFontSize}
                        onChange={(val) => setAttributes({ uploadButtonFontSize: val })}
                        units={FONT_SIZE_UNITS}
                    />

                    <UnitControl
                        label={__('Border Width', 'wp-user-frontend')}
                        value={uploadButtonBorderWidth}
                        onChange={(val) => setAttributes({ uploadButtonBorderWidth: val })}
                        units={BORDER_WIDTH_UNITS}
                    />

                    <UnitControl
                        label={__('Border Radius', 'wp-user-frontend')}
                        value={uploadButtonBorderRadius}
                        onChange={(val) => setAttributes({ uploadButtonBorderRadius: val })}
                        units={BORDER_RADIUS_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Vertical', 'wp-user-frontend')}
                        value={uploadButtonPaddingV}
                        onChange={(val) => setAttributes({ uploadButtonPaddingV: val })}
                        units={SPACING_UNITS}
                    />

                    <UnitControl
                        label={__('Padding Horizontal', 'wp-user-frontend')}
                        value={uploadButtonPaddingH}
                        onChange={(val) => setAttributes({ uploadButtonPaddingH: val })}
                        units={SPACING_UNITS}
                    />
                </PanelBody>

                <PanelColorSettings
                    title={__('Colors', 'wp-user-frontend')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: labelColor,
                            onChange: (val) => setAttributes({ labelColor: val || '' }),
                            label: __('Label', 'wp-user-frontend'),
                        },
                        {
                            value: helpTextColor,
                            onChange: (val) => setAttributes({ helpTextColor: val || '' }),
                            label: __('Help Text', 'wp-user-frontend'),
                        },
                        {
                            value: fieldFocusBorderColor,
                            onChange: (val) => setAttributes({ fieldFocusBorderColor: val || '' }),
                            label: __('Field Focus Border', 'wp-user-frontend'),
                        },
                        {
                            value: fieldBackgroundColor,
                            onChange: (val) => setAttributes({ fieldBackgroundColor: val || '' }),
                            label: __('Field Background', 'wp-user-frontend'),
                        },
                        {
                            value: fieldTextColor,
                            onChange: (val) => setAttributes({ fieldTextColor: val || '' }),
                            label: __('Field Text', 'wp-user-frontend'),
                        },
                        {
                            value: fieldPlaceholderColor,
                            onChange: (val) => setAttributes({ fieldPlaceholderColor: val || '' }),
                            label: __('Placeholder', 'wp-user-frontend'),
                        },
                        {
                            value: buttonBackgroundColor,
                            onChange: (val) => setAttributes({ buttonBackgroundColor: val || '' }),
                            label: __('Button Background', 'wp-user-frontend'),
                        },
                        {
                            value: buttonTextColor,
                            onChange: (val) => setAttributes({ buttonTextColor: val || '' }),
                            label: __('Button Text', 'wp-user-frontend'),
                        },
                        {
                            value: uploadButtonBackgroundColor,
                            onChange: (val) => setAttributes({ uploadButtonBackgroundColor: val || '' }),
                            label: __('Upload Button Background', 'wp-user-frontend'),
                        },
                        {
                            value: uploadButtonTextColor,
                            onChange: (val) => setAttributes({ uploadButtonTextColor: val || '' }),
                            label: __('Upload Button Text', 'wp-user-frontend'),
                        },
                        {
                            value: uploadButtonBorderColor,
                            onChange: (val) => setAttributes({ uploadButtonBorderColor: val || '' }),
                            label: __('Upload Button Border', 'wp-user-frontend'),
                        },
                    ]}
                />

                {isMultistep && (
                    <PanelBody
                        title={__('Prev / Next Button Styles', 'wp-user-frontend')}
                        initialOpen={false}
                    >
                        <FontFamilyControl
                            __next40pxDefaultSize
                            fontFamilies={mergedFontFamilies}
                            value={msButtonFontFamily}
                            onChange={(val) => setAttributes({ msButtonFontFamily: val })}
                        />

                        <FontAppearanceControl
                            __next40pxDefaultSize
                            value={{ fontStyle: msButtonFontStyle, fontWeight: msButtonFontWeight }}
                            onChange={({ fontStyle, fontWeight }) =>
                                setAttributes({ msButtonFontStyle: fontStyle, msButtonFontWeight: fontWeight })
                            }
                            fontFamilyFaces={msButtonFontFaces}
                            hasFontStyles={true}
                            hasFontWeights={true}
                        />

                        <UnitControl
                            label={__('Font Size', 'wp-user-frontend')}
                            value={msButtonFontSize}
                            onChange={(val) => setAttributes({ msButtonFontSize: val })}
                            units={FONT_SIZE_UNITS}
                        />

                        <UnitControl
                            label={__('Border Width', 'wp-user-frontend')}
                            value={msButtonBorderWidth}
                            onChange={(val) => setAttributes({ msButtonBorderWidth: val })}
                            units={BORDER_WIDTH_UNITS}
                        />

                        <UnitControl
                            label={__('Border Radius', 'wp-user-frontend')}
                            value={msButtonBorderRadius}
                            onChange={(val) => setAttributes({ msButtonBorderRadius: val })}
                            units={BORDER_RADIUS_UNITS}
                        />

                        <UnitControl
                            label={__('Padding Vertical', 'wp-user-frontend')}
                            value={msButtonPaddingV}
                            onChange={(val) => setAttributes({ msButtonPaddingV: val })}
                            units={SPACING_UNITS}
                        />

                        <UnitControl
                            label={__('Padding Horizontal', 'wp-user-frontend')}
                            value={msButtonPaddingH}
                            onChange={(val) => setAttributes({ msButtonPaddingH: val })}
                            units={SPACING_UNITS}
                        />
                    </PanelBody>
                )}

                {isMultistep && (
                    <PanelColorSettings
                        title={__('Multistep Colors', 'wp-user-frontend')}
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: msActiveBgColor,
                                onChange: (val) => setAttributes({ msActiveBgColor: val || '' }),
                                label: __('Active Step Background', 'wp-user-frontend'),
                            },
                            {
                                value: msActiveTextColor,
                                onChange: (val) => setAttributes({ msActiveTextColor: val || '' }),
                                label: __('Active Step Text', 'wp-user-frontend'),
                            },
                            {
                                value: msInactiveBgColor,
                                onChange: (val) => setAttributes({ msInactiveBgColor: val || '' }),
                                label: __('Inactive Step Background', 'wp-user-frontend'),
                            },
                            {
                                value: msButtonBackgroundColor,
                                onChange: (val) => setAttributes({ msButtonBackgroundColor: val || '' }),
                                label: __('Prev / Next Button Background', 'wp-user-frontend'),
                            },
                            {
                                value: msButtonTextColor,
                                onChange: (val) => setAttributes({ msButtonTextColor: val || '' }),
                                label: __('Prev / Next Button Text', 'wp-user-frontend'),
                            },
                            {
                                value: msButtonBorderColor,
                                onChange: (val) => setAttributes({ msButtonBorderColor: val || '' }),
                                label: __('Prev / Next Button Border', 'wp-user-frontend'),
                            },
                        ]}
                    />
                )}
            </InspectorControls>

            <div style={{ position: 'relative' }}>
                { ! formId ? (
                    <p style={{ padding: '12px', textAlign: 'center', color: '#757575' }}>
                        { __( 'Please select a post form to display.', 'wp-user-frontend' ) }
                    </p>
                ) : (
                    <>
                        { isUpdating && (
                            <div className="wpuf-ssr-loading-overlay">
                                <div className="wpuf-ssr-loading" />
                                <p style={{ marginTop: '12px', fontSize: '13px' }}>
                                    { __( 'Updating preview…', 'wp-user-frontend' ) }
                                </p>
                            </div>
                        ) }
                        <ServerSideRender
                            block="wpuf/post-form"
                            attributes={attributes}
                            LoadingResponsePlaceholder={() => {
                                // eslint-disable-next-line react-hooks/rules-of-hooks
                                useEffect(() => {
                                    return () => {
                                        setIsUpdating(false);
                                    };
                                }, []);
                                return null;
                            }}
                            EmptyResponsePlaceholder={() => {
                                // eslint-disable-next-line no-console
                                console.warn( '[WPUF PostForm] SSR returned empty response', { formId } );
                                return (
                                    <p style={{ padding: '12px', background: '#fff3cd', border: '1px solid #ffeeba' }}>
                                        { __( 'SSR returned an empty response. Check the PHP error log.', 'wp-user-frontend' ) }
                                    </p>
                                );
                            }}
                            ErrorResponsePlaceholder={({ response }) => {
                                // eslint-disable-next-line no-console
                                console.error( '[WPUF PostForm] SSR error response', response );
                                return (
                                    <p style={{ padding: '12px', background: '#f8d7da', border: '1px solid #f5c6cb' }}>
                                        { __( 'SSR error: ', 'wp-user-frontend' ) }
                                        { response?.errorMsg || JSON.stringify( response ) }
                                    </p>
                                );
                            }}
                        />
                    </>
                ) }
            </div>
        </div>
    );
}
