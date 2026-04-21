/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/blocks/post-form/edit.js"
/*!*****************************************!*\
  !*** ./src/js/blocks/post-form/edit.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/server-side-render */ "@wordpress/server-side-render");
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./templates */ "./src/js/blocks/post-form/templates.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__);
// DESCRIPTION: Editor component for the post form block in wp-user-frontend.
// Content settings in default InspectorControls, style controls in InspectorControls group="styles".










const FONT_SIZE_UNITS = [{
  value: 'px',
  label: 'px',
  default: 14
}, {
  value: 'em',
  label: 'em',
  default: 1
}, {
  value: 'rem',
  label: 'rem',
  default: 1
}];
const SPACING_UNITS = [{
  value: 'px',
  label: 'px',
  default: 8
}, {
  value: 'em',
  label: 'em',
  default: 0.5
}, {
  value: 'rem',
  label: 'rem',
  default: 0.5
}, {
  value: '%',
  label: '%',
  default: 5
}];
const BORDER_WIDTH_UNITS = [{
  value: 'px',
  label: 'px',
  default: 1
}, {
  value: 'em',
  label: 'em',
  default: 0.1
}, {
  value: 'rem',
  label: 'rem',
  default: 0.1
}];
const BORDER_RADIUS_UNITS = [{
  value: 'px',
  label: 'px',
  default: 4
}, {
  value: 'em',
  label: 'em',
  default: 0.25
}, {
  value: 'rem',
  label: 'rem',
  default: 0.25
}, {
  value: '%',
  label: '%',
  default: 5
}];

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
  const [fontFamilies] = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useSettings)('typography.fontFamilies');
  if (!fontFamilies) {
    return [];
  }
  return [...(fontFamilies?.default || []), ...(fontFamilies?.theme || []), ...(fontFamilies?.custom || [])];
}

/**
 * Get the fontFace array for a given font-family value from theme settings.
 *
 * @param {string} fontFamily         The selected font-family CSS value
 * @param {Array}  mergedFontFamilies Pre-merged flat array from useMergedFontFamilies
 *
 * @returns {Array} Array of font face objects for the selected family
 */
function getFontFamilyFaces(fontFamily, mergedFontFamilies) {
  if (!fontFamily || !mergedFontFamilies?.length) {
    return [];
  }
  const match = mergedFontFamilies.find(f => f.fontFamily === fontFamily);
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
function TemplateCard({
  template,
  isActive,
  onSelect
}) {
  const {
    preview,
    attributes: tplAttrs
  } = template;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("button", {
    type: "button",
    className: `wpuf-pf-template-card${isActive ? ' is-active' : ''}`,
    onClick: () => onSelect(template),
    "aria-pressed": isActive,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("span", {
      className: "wpuf-pf-template-card__badge",
      children: "\u2713"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
      className: "wpuf-pf-template-preview",
      style: {
        background: preview.bg
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
        className: "wpuf-pf-template-preview__row",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-pf-template-preview__label",
          style: {
            background: preview.text
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-pf-template-preview__input",
          style: {
            background: tplAttrs.fieldBackgroundColor || preview.bg,
            borderColor: preview.border,
            borderWidth: tplAttrs.fieldBorder?.width || '1px',
            borderRadius: tplAttrs.fieldBorderRadius
          }
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
        className: "wpuf-pf-template-preview__row",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-pf-template-preview__label",
          style: {
            background: preview.text
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-pf-template-preview__input",
          style: {
            background: tplAttrs.fieldBackgroundColor || preview.bg,
            borderColor: preview.border,
            borderWidth: tplAttrs.fieldBorder?.width || '1px',
            borderRadius: tplAttrs.fieldBorderRadius
          }
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
        className: "wpuf-pf-template-preview__button",
        style: {
          background: preview.button,
          borderRadius: tplAttrs.buttonBorderRadius
        }
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
      className: "wpuf-pf-template-card__info",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("p", {
        className: "wpuf-pf-template-card__label",
        children: template.label
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("p", {
        className: "wpuf-pf-template-card__desc",
        children: template.description
      })]
    })]
  });
}
function Edit({
  attributes,
  setAttributes
}) {
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
    uploadButtonPaddingH
  } = attributes;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps)();
  const {
    createNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_4__.store);
  const mergedFontFamilies = useMergedFontFamilies();
  const labelFontFaces = getFontFamilyFaces(labelFontFamily, mergedFontFamilies);
  const fieldFontFaces = getFontFamilyFaces(fieldFontFamily, mergedFontFamilies);
  const helpTextFontFaces = getFontFamilyFaces(helpTextFontFamily, mergedFontFamilies);
  const buttonFontFaces = getFontFamilyFaces(buttonFontFamily, mergedFontFamilies);
  const uploadButtonFontFaces = getFontFamilyFaces(uploadButtonFontFamily, mergedFontFamilies);

  // Assign blockId once on first insert
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    if (!blockId) {
      setAttributes({
        blockId: generateBlockId()
      });
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // SSR loading overlay state
  const [isUpdating, setIsUpdating] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const debounceTimerRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useRef)(null);
  const isFirstRender = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useRef)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
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
  const prevAttrsRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useRef)(null);
  const handleTemplateSelect = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useCallback)(template => {
    if (template.id === activeTemplate) {
      return;
    }

    // Save current state for undo
    const snapshot = {
      ...attributes
    };
    prevAttrsRef.current = snapshot;

    // Apply immediately
    setAttributes(template.attributes);

    // Show snackbar with undo
    createNotice('info', template.label + ' ' + (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('template applied.', 'wp-user-frontend'), {
      type: 'snackbar',
      isDismissible: true,
      actions: [{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Undo', 'wp-user-frontend'),
        onClick: () => {
          if (prevAttrsRef.current) {
            setAttributes(prevAttrsRef.current);
            prevAttrsRef.current = null;
          }
        }
      }]
    });
  }, [activeTemplate, attributes, setAttributes, createNotice]);
  const editorData = window.wpufPostForm || {};
  const forms = editorData.forms || [];

  // eslint-disable-next-line no-console
  console.log('[WPUF PostForm] editor data', {
    formId,
    forms,
    selectedForm: forms.find(f => f.id === formId)
  });

  // Build SelectControl options: "Select a form" placeholder + all post forms
  const formOptions = [{
    value: '0',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('— Select a post form —', 'wp-user-frontend')
  }, ...forms.map(f => ({
    value: String(f.id),
    label: f.title
  }))];
  const handleFormChange = val => {
    setAttributes({
      formId: parseInt(val, 10)
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
    ...blockProps,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Form Settings', 'wp-user-frontend'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Post Form', 'wp-user-frontend'),
          value: String(formId || 0),
          options: formOptions,
          onChange: handleFormChange,
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Select the post form to display.', 'wp-user-frontend')
        })
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.InspectorControls, {
      group: "styles",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Templates', 'wp-user-frontend'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-pf-templates",
          children: _templates__WEBPACK_IMPORTED_MODULE_7__.TEMPLATES.map(tpl => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(TemplateCard, {
            template: tpl,
            isActive: activeTemplate === tpl.id,
            onSelect: handleTemplateSelect
          }, tpl.id))
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Label Styles', 'wp-user-frontend'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontFamilyControl, {
          __next40pxDefaultSize: true,
          fontFamilies: mergedFontFamilies,
          value: labelFontFamily,
          onChange: val => setAttributes({
            labelFontFamily: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontAppearanceControl, {
          __next40pxDefaultSize: true,
          value: {
            fontStyle: labelFontStyle,
            fontWeight: labelFontWeight
          },
          onChange: ({
            fontStyle,
            fontWeight
          }) => setAttributes({
            labelFontStyle: fontStyle,
            labelFontWeight: fontWeight
          }),
          fontFamilyFaces: labelFontFaces,
          hasFontStyles: true,
          hasFontWeights: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalToggleGroupControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Label Position', 'wp-user-frontend'),
          value: labelPosition,
          className: "wpuf-mt-2",
          onChange: val => setAttributes({
            labelPosition: val
          }),
          isBlock: true,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalToggleGroupControlOption, {
            value: "above",
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Above', 'wp-user-frontend')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalToggleGroupControlOption, {
            value: "left",
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Left', 'wp-user-frontend')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalToggleGroupControlOption, {
            value: "right",
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Right', 'wp-user-frontend')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalToggleGroupControlOption, {
            value: "hidden",
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Hidden', 'wp-user-frontend')
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Font Size', 'wp-user-frontend'),
          value: labelFontSize,
          onChange: val => setAttributes({
            labelFontSize: val
          }),
          units: FONT_SIZE_UNITS
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Field Styles', 'wp-user-frontend'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontFamilyControl, {
          __next40pxDefaultSize: true,
          fontFamilies: mergedFontFamilies,
          value: fieldFontFamily,
          onChange: val => setAttributes({
            fieldFontFamily: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontAppearanceControl, {
          __next40pxDefaultSize: true,
          value: {
            fontStyle: fieldFontStyle,
            fontWeight: fieldFontWeight
          },
          onChange: ({
            fontStyle,
            fontWeight
          }) => setAttributes({
            fieldFontStyle: fontStyle,
            fieldFontWeight: fontWeight
          }),
          fontFamilyFaces: fieldFontFaces,
          hasFontStyles: true,
          hasFontWeights: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Font Size', 'wp-user-frontend'),
          value: fieldFontSize,
          onChange: val => setAttributes({
            fieldFontSize: val
          }),
          units: FONT_SIZE_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.BorderBoxControl, {
          __next40pxDefaultSize: true,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border', 'wp-user-frontend'),
          value: fieldBorder,
          onChange: val => setAttributes({
            fieldBorder: val || {}
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border Radius', 'wp-user-frontend'),
          value: fieldBorderRadius,
          onChange: val => setAttributes({
            fieldBorderRadius: val
          }),
          units: BORDER_RADIUS_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Vertical', 'wp-user-frontend'),
          value: fieldPaddingV,
          onChange: val => setAttributes({
            fieldPaddingV: val
          }),
          units: SPACING_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Horizontal', 'wp-user-frontend'),
          value: fieldPaddingH,
          onChange: val => setAttributes({
            fieldPaddingH: val
          }),
          units: SPACING_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Margin Bottom', 'wp-user-frontend'),
          value: fieldMarginBottom,
          onChange: val => setAttributes({
            fieldMarginBottom: val
          }),
          units: SPACING_UNITS
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Help Text Styles', 'wp-user-frontend'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontFamilyControl, {
          __next40pxDefaultSize: true,
          fontFamilies: mergedFontFamilies,
          value: helpTextFontFamily,
          onChange: val => setAttributes({
            helpTextFontFamily: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontAppearanceControl, {
          __next40pxDefaultSize: true,
          value: {
            fontStyle: helpTextFontStyle,
            fontWeight: helpTextFontWeight
          },
          onChange: ({
            fontStyle,
            fontWeight
          }) => setAttributes({
            helpTextFontStyle: fontStyle,
            helpTextFontWeight: fontWeight
          }),
          fontFamilyFaces: helpTextFontFaces,
          hasFontStyles: true,
          hasFontWeights: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Font Size', 'wp-user-frontend'),
          value: helpTextFontSize,
          onChange: val => setAttributes({
            helpTextFontSize: val
          }),
          units: FONT_SIZE_UNITS
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Button Styles', 'wp-user-frontend'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontFamilyControl, {
          __next40pxDefaultSize: true,
          fontFamilies: mergedFontFamilies,
          value: buttonFontFamily,
          onChange: val => setAttributes({
            buttonFontFamily: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontAppearanceControl, {
          __next40pxDefaultSize: true,
          value: {
            fontStyle: buttonFontStyle,
            fontWeight: buttonFontWeight
          },
          onChange: ({
            fontStyle,
            fontWeight
          }) => setAttributes({
            buttonFontStyle: fontStyle,
            buttonFontWeight: fontWeight
          }),
          fontFamilyFaces: buttonFontFaces,
          hasFontStyles: true,
          hasFontWeights: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Font Size', 'wp-user-frontend'),
          value: buttonFontSize,
          onChange: val => setAttributes({
            buttonFontSize: val
          }),
          units: FONT_SIZE_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border Width', 'wp-user-frontend'),
          value: buttonBorderWidth,
          onChange: val => setAttributes({
            buttonBorderWidth: val
          }),
          units: BORDER_WIDTH_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border Radius', 'wp-user-frontend'),
          value: buttonBorderRadius,
          onChange: val => setAttributes({
            buttonBorderRadius: val
          }),
          units: BORDER_RADIUS_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Vertical', 'wp-user-frontend'),
          value: buttonPaddingV,
          onChange: val => setAttributes({
            buttonPaddingV: val
          }),
          units: SPACING_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Horizontal', 'wp-user-frontend'),
          value: buttonPaddingH,
          onChange: val => setAttributes({
            buttonPaddingH: val
          }),
          units: SPACING_UNITS
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Upload Button Styles', 'wp-user-frontend'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontFamilyControl, {
          __next40pxDefaultSize: true,
          fontFamilies: mergedFontFamilies,
          value: uploadButtonFontFamily,
          onChange: val => setAttributes({
            uploadButtonFontFamily: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.__experimentalFontAppearanceControl, {
          __next40pxDefaultSize: true,
          value: {
            fontStyle: uploadButtonFontStyle,
            fontWeight: uploadButtonFontWeight
          },
          onChange: ({
            fontStyle,
            fontWeight
          }) => setAttributes({
            uploadButtonFontStyle: fontStyle,
            uploadButtonFontWeight: fontWeight
          }),
          fontFamilyFaces: uploadButtonFontFaces,
          hasFontStyles: true,
          hasFontWeights: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Font Size', 'wp-user-frontend'),
          value: uploadButtonFontSize,
          onChange: val => setAttributes({
            uploadButtonFontSize: val
          }),
          units: FONT_SIZE_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border Width', 'wp-user-frontend'),
          value: uploadButtonBorderWidth,
          onChange: val => setAttributes({
            uploadButtonBorderWidth: val
          }),
          units: BORDER_WIDTH_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Border Radius', 'wp-user-frontend'),
          value: uploadButtonBorderRadius,
          onChange: val => setAttributes({
            uploadButtonBorderRadius: val
          }),
          units: BORDER_RADIUS_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Vertical', 'wp-user-frontend'),
          value: uploadButtonPaddingV,
          onChange: val => setAttributes({
            uploadButtonPaddingV: val
          }),
          units: SPACING_UNITS
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalUnitControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Padding Horizontal', 'wp-user-frontend'),
          value: uploadButtonPaddingH,
          onChange: val => setAttributes({
            uploadButtonPaddingH: val
          }),
          units: SPACING_UNITS
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.PanelColorSettings, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Colors', 'wp-user-frontend'),
        initialOpen: false,
        colorSettings: [{
          value: labelColor,
          onChange: val => setAttributes({
            labelColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Label', 'wp-user-frontend')
        }, {
          value: helpTextColor,
          onChange: val => setAttributes({
            helpTextColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Help Text', 'wp-user-frontend')
        }, {
          value: fieldFocusBorderColor,
          onChange: val => setAttributes({
            fieldFocusBorderColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Field Focus Border', 'wp-user-frontend')
        }, {
          value: fieldBackgroundColor,
          onChange: val => setAttributes({
            fieldBackgroundColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Field Background', 'wp-user-frontend')
        }, {
          value: fieldTextColor,
          onChange: val => setAttributes({
            fieldTextColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Field Text', 'wp-user-frontend')
        }, {
          value: fieldPlaceholderColor,
          onChange: val => setAttributes({
            fieldPlaceholderColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Placeholder', 'wp-user-frontend')
        }, {
          value: buttonBackgroundColor,
          onChange: val => setAttributes({
            buttonBackgroundColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Button Background', 'wp-user-frontend')
        }, {
          value: buttonTextColor,
          onChange: val => setAttributes({
            buttonTextColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Button Text', 'wp-user-frontend')
        }, {
          value: uploadButtonBackgroundColor,
          onChange: val => setAttributes({
            uploadButtonBackgroundColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Upload Button Background', 'wp-user-frontend')
        }, {
          value: uploadButtonTextColor,
          onChange: val => setAttributes({
            uploadButtonTextColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Upload Button Text', 'wp-user-frontend')
        }, {
          value: uploadButtonBorderColor,
          onChange: val => setAttributes({
            uploadButtonBorderColor: val || ''
          }),
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Upload Button Border', 'wp-user-frontend')
        }]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
      style: {
        position: 'relative'
      },
      children: [isUpdating && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("div", {
        className: "wpuf-ssr-loading-overlay",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("div", {
          className: "wpuf-ssr-loading"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("p", {
          style: {
            marginTop: '12px',
            fontSize: '13px'
          },
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Updating preview…', 'wp-user-frontend')
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)((_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default()), {
        block: "wpuf/post-form",
        attributes: attributes,
        LoadingResponsePlaceholder: () => {
          // eslint-disable-next-line react-hooks/rules-of-hooks
          (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
            return () => {
              setIsUpdating(false);
            };
          }, []);
          return null;
        },
        EmptyResponsePlaceholder: () => {
          // eslint-disable-next-line no-console
          console.warn('[WPUF PostForm] SSR returned empty response', {
            formId
          });
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("p", {
            style: {
              padding: '12px',
              background: '#fff3cd',
              border: '1px solid #ffeeba'
            },
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('SSR returned an empty response. Check the PHP error log.', 'wp-user-frontend')
          });
        },
        ErrorResponsePlaceholder: ({
          response
        }) => {
          // eslint-disable-next-line no-console
          console.error('[WPUF PostForm] SSR error response', response);
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)("p", {
            style: {
              padding: '12px',
              background: '#f8d7da',
              border: '1px solid #f5c6cb'
            },
            children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('SSR error: ', 'wp-user-frontend'), response?.errorMsg || JSON.stringify(response)]
          });
        }
      })]
    })]
  });
}

/***/ },

/***/ "./src/js/blocks/post-form/save.js"
/*!*****************************************!*\
  !*** ./src/js/blocks/post-form/save.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Save)
/* harmony export */ });
// DESCRIPTION: Save component for the post form block.
// Returns null — this is a dynamic block rendered server-side.

function Save() {
  return null;
}

/***/ },

/***/ "./src/js/blocks/post-form/templates.js"
/*!**********************************************!*\
  !*** ./src/js/blocks/post-form/templates.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TEMPLATES: () => (/* binding */ TEMPLATES)
/* harmony export */ });
// DESCRIPTION: Predefined visual template presets for the post form block.
// Each entry maps block attribute names to preset values.

const TEMPLATES = [{
  id: 'default',
  label: 'Default',
  description: 'Clean, inherits theme styles',
  preview: {
    bg: '#ffffff',
    border: '#dddddd',
    button: '#0073aa',
    text: '#333333'
  },
  attributes: {
    activeTemplate: 'default',
    style: {
      color: {}
    },
    labelFontSize: '14px',
    labelColor: '#333333',
    labelFontWeight: 'normal',
    labelFontFamily: '',
    labelFontStyle: '',
    helpTextColor: '#767676',
    helpTextFontFamily: '',
    helpTextFontSize: '12px',
    helpTextFontStyle: '',
    helpTextFontWeight: '',
    fieldBorder: {
      color: '#dddddd',
      style: 'solid',
      width: '1px'
    },
    fieldBorderRadius: '4px',
    fieldPaddingV: '8px',
    fieldPaddingH: '12px',
    fieldBackgroundColor: '#ffffff',
    fieldTextColor: '#333333',
    fieldPlaceholderColor: '#aaaaaa',
    fieldFontFamily: '',
    fieldFontSize: '14px',
    fieldFontStyle: '',
    fieldFontWeight: '',
    fieldMarginBottom: '16px',
    fieldFocusBorderColor: '#0073aa',
    buttonBackgroundColor: '#0073aa',
    buttonTextColor: '#ffffff',
    buttonBorderColor: '',
    buttonBorderWidth: '0px',
    buttonBorderRadius: '4px',
    buttonFontSize: '14px',
    buttonFontFamily: '',
    buttonFontStyle: '',
    buttonFontWeight: '',
    buttonPaddingV: '10px',
    buttonPaddingH: '20px',
    uploadButtonBackgroundColor: '#f0f0f0',
    uploadButtonTextColor: '#333333',
    uploadButtonBorderColor: '#dddddd',
    uploadButtonBorderWidth: '1px',
    uploadButtonBorderRadius: '4px',
    uploadButtonFontSize: '13px',
    uploadButtonFontFamily: '',
    uploadButtonFontStyle: '',
    uploadButtonFontWeight: '',
    uploadButtonPaddingV: '8px',
    uploadButtonPaddingH: '16px',
    errorMessageColor: '#cc0000',
    successMessageColor: '#00a32a'
  }
}, {
  id: 'dark',
  label: 'Dark',
  description: 'Dark background, light text',
  preview: {
    bg: '#1e1e2e',
    border: '#3d3d5c',
    button: '#7c3aed',
    text: '#f0f0f0'
  },
  attributes: {
    activeTemplate: 'dark',
    style: {
      color: {
        background: '#1e1e2e'
      }
    },
    labelFontSize: '14px',
    labelColor: '#e0e0e0',
    labelFontWeight: 'normal',
    labelFontFamily: '',
    labelFontStyle: '',
    helpTextColor: '#a0a0b0',
    helpTextFontFamily: '',
    helpTextFontSize: '12px',
    helpTextFontStyle: '',
    helpTextFontWeight: '',
    fieldBorder: {
      color: '#3d3d5c',
      style: 'solid',
      width: '1px'
    },
    fieldBorderRadius: '6px',
    fieldPaddingV: '10px',
    fieldPaddingH: '14px',
    fieldBackgroundColor: '#2a2a3e',
    fieldTextColor: '#f0f0f0',
    fieldPlaceholderColor: '#6b6b8a',
    fieldFontFamily: '',
    fieldFontSize: '14px',
    fieldFontStyle: '',
    fieldFontWeight: '',
    fieldMarginBottom: '18px',
    fieldFocusBorderColor: '#7c3aed',
    buttonBackgroundColor: '#7c3aed',
    buttonTextColor: '#ffffff',
    buttonBorderColor: '',
    buttonBorderWidth: '0px',
    buttonBorderRadius: '6px',
    buttonFontSize: '14px',
    buttonFontFamily: '',
    buttonFontStyle: '',
    buttonFontWeight: '',
    buttonPaddingV: '12px',
    buttonPaddingH: '24px',
    uploadButtonBackgroundColor: '#2a2a3e',
    uploadButtonTextColor: '#e0e0e0',
    uploadButtonBorderColor: '#3d3d5c',
    uploadButtonBorderWidth: '1px',
    uploadButtonBorderRadius: '6px',
    uploadButtonFontSize: '13px',
    uploadButtonFontFamily: '',
    uploadButtonFontStyle: '',
    uploadButtonFontWeight: '',
    uploadButtonPaddingV: '8px',
    uploadButtonPaddingH: '16px',
    errorMessageColor: '#f87171',
    successMessageColor: '#34d399'
  }
}, {
  id: 'colorful',
  label: 'Colorful',
  description: 'Vibrant purple accent',
  preview: {
    bg: '#fdf4ff',
    border: '#c084fc',
    button: '#9333ea',
    text: '#4a044e'
  },
  attributes: {
    activeTemplate: 'colorful',
    style: {
      color: {}
    },
    labelFontSize: '14px',
    labelColor: '#7e22ce',
    labelFontWeight: '600',
    labelFontFamily: '',
    labelFontStyle: '',
    helpTextColor: '#a855f7',
    helpTextFontFamily: '',
    helpTextFontSize: '12px',
    helpTextFontStyle: '',
    helpTextFontWeight: '',
    fieldBorder: {
      color: '#c084fc',
      style: 'solid',
      width: '2px'
    },
    fieldBorderRadius: '8px',
    fieldPaddingV: '10px',
    fieldPaddingH: '14px',
    fieldBackgroundColor: '#fdf4ff',
    fieldTextColor: '#4a044e',
    fieldPlaceholderColor: '#d8b4fe',
    fieldFontFamily: '',
    fieldFontSize: '14px',
    fieldFontStyle: '',
    fieldFontWeight: '',
    fieldMarginBottom: '20px',
    fieldFocusBorderColor: '#7e22ce',
    buttonBackgroundColor: '#9333ea',
    buttonTextColor: '#ffffff',
    buttonBorderColor: '',
    buttonBorderWidth: '0px',
    buttonBorderRadius: '8px',
    buttonFontSize: '15px',
    buttonFontFamily: '',
    buttonFontStyle: '',
    buttonFontWeight: '',
    buttonPaddingV: '12px',
    buttonPaddingH: '28px',
    uploadButtonBackgroundColor: '#f3e8ff',
    uploadButtonTextColor: '#7e22ce',
    uploadButtonBorderColor: '#c084fc',
    uploadButtonBorderWidth: '2px',
    uploadButtonBorderRadius: '8px',
    uploadButtonFontSize: '13px',
    uploadButtonFontFamily: '',
    uploadButtonFontStyle: '',
    uploadButtonFontWeight: '',
    uploadButtonPaddingV: '8px',
    uploadButtonPaddingH: '16px',
    errorMessageColor: '#be123c',
    successMessageColor: '#16a34a'
  }
}, {
  id: 'minimal',
  label: 'Minimal',
  description: 'Borderless fields, understated',
  preview: {
    bg: '#ffffff',
    border: '#e5e5e5',
    button: '#111111',
    text: '#111111'
  },
  attributes: {
    activeTemplate: 'minimal',
    style: {
      color: {}
    },
    labelFontSize: '13px',
    labelColor: '#555555',
    labelFontWeight: 'normal',
    labelFontFamily: '',
    labelFontStyle: '',
    helpTextColor: '#999999',
    helpTextFontFamily: '',
    helpTextFontSize: '11px',
    helpTextFontStyle: '',
    helpTextFontWeight: '',
    fieldBorder: {
      color: '#dddddd',
      style: 'solid',
      width: '1px'
    },
    fieldBorderRadius: '0px',
    fieldPaddingV: '8px',
    fieldPaddingH: '4px',
    fieldBackgroundColor: '#ffffff',
    fieldTextColor: '#111111',
    fieldPlaceholderColor: '#cccccc',
    fieldFontFamily: '',
    fieldFontSize: '13px',
    fieldFontStyle: '',
    fieldFontWeight: '',
    fieldMarginBottom: '20px',
    fieldFocusBorderColor: '#111111',
    buttonBackgroundColor: '#111111',
    buttonTextColor: '#ffffff',
    buttonBorderColor: '',
    buttonBorderWidth: '0px',
    buttonBorderRadius: '2px',
    buttonFontSize: '13px',
    buttonFontFamily: '',
    buttonFontStyle: '',
    buttonFontWeight: '',
    buttonPaddingV: '10px',
    buttonPaddingH: '24px',
    uploadButtonBackgroundColor: '#ffffff',
    uploadButtonTextColor: '#555555',
    uploadButtonBorderColor: '#e5e5e5',
    uploadButtonBorderWidth: '0px',
    uploadButtonBorderRadius: '0px',
    uploadButtonFontSize: '13px',
    uploadButtonFontFamily: '',
    uploadButtonFontStyle: '',
    uploadButtonFontWeight: '',
    uploadButtonPaddingV: '8px',
    uploadButtonPaddingH: '4px',
    errorMessageColor: '#cc0000',
    successMessageColor: '#008000'
  }
}, {
  id: 'bold',
  label: 'Bold',
  description: 'Strong borders, high contrast',
  preview: {
    bg: '#ffffff',
    border: '#000000',
    button: '#f59e0b',
    text: '#000000'
  },
  attributes: {
    activeTemplate: 'bold',
    style: {
      color: {}
    },
    labelFontSize: '15px',
    labelColor: '#000000',
    labelFontWeight: '700',
    labelFontFamily: '',
    labelFontStyle: '',
    helpTextColor: '#555555',
    helpTextFontFamily: '',
    helpTextFontSize: '13px',
    helpTextFontStyle: '',
    helpTextFontWeight: '',
    fieldBorder: {
      color: '#000000',
      style: 'solid',
      width: '2px'
    },
    fieldBorderRadius: '0px',
    fieldPaddingV: '10px',
    fieldPaddingH: '14px',
    fieldBackgroundColor: '#ffffff',
    fieldTextColor: '#000000',
    fieldPlaceholderColor: '#999999',
    fieldFontFamily: '',
    fieldFontSize: '15px',
    fieldFontStyle: '',
    fieldFontWeight: '',
    fieldMarginBottom: '20px',
    fieldFocusBorderColor: '#f59e0b',
    buttonBackgroundColor: '#f59e0b',
    buttonTextColor: '#000000',
    buttonBorderColor: '#000000',
    buttonBorderWidth: '2px',
    buttonBorderRadius: '0px',
    buttonFontSize: '15px',
    buttonFontFamily: '',
    buttonFontStyle: '',
    buttonFontWeight: '',
    buttonPaddingV: '12px',
    buttonPaddingH: '28px',
    uploadButtonBackgroundColor: '#ffffff',
    uploadButtonTextColor: '#000000',
    uploadButtonBorderColor: '#000000',
    uploadButtonBorderWidth: '2px',
    uploadButtonBorderRadius: '0px',
    uploadButtonFontSize: '13px',
    uploadButtonFontFamily: '',
    uploadButtonFontStyle: '',
    uploadButtonFontWeight: '',
    uploadButtonPaddingV: '8px',
    uploadButtonPaddingH: '16px',
    errorMessageColor: '#cc0000',
    successMessageColor: '#166534'
  }
}];

/***/ },

/***/ "./src/js/blocks/post-form/editor.css"
/*!********************************************!*\
  !*** ./src/js/blocks/post-form/editor.css ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ },

/***/ "@wordpress/block-editor"
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
(module) {

module.exports = window["wp"]["blockEditor"];

/***/ },

/***/ "@wordpress/blocks"
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["blocks"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "@wordpress/notices"
/*!*********************************!*\
  !*** external ["wp","notices"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["notices"];

/***/ },

/***/ "@wordpress/server-side-render"
/*!******************************************!*\
  !*** external ["wp","serverSideRender"] ***!
  \******************************************/
(module) {

module.exports = window["wp"]["serverSideRender"];

/***/ },

/***/ "./src/js/blocks/post-form/block.json"
/*!********************************************!*\
  !*** ./src/js/blocks/post-form/block.json ***!
  \********************************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"wpuf/post-form","version":"1.0.0","title":"Post Form","category":"wpuf","description":"Embed a WPUF post form with customisable styling.","textdomain":"wp-user-frontend","icon":"feedback","keywords":["post","submission","form","wpuf"],"attributes":{"blockId":{"type":"string","default":""},"formId":{"type":"number","default":0},"activeTemplate":{"type":"string","default":"default"},"labelPosition":{"type":"string","default":"above"},"labelFontSize":{"type":"string","default":"14px"},"labelColor":{"type":"string","default":""},"labelFontWeight":{"type":"string","default":"normal"},"labelFontFamily":{"type":"string","default":""},"labelFontStyle":{"type":"string","default":""},"helpTextColor":{"type":"string","default":""},"helpTextFontFamily":{"type":"string","default":""},"helpTextFontSize":{"type":"string","default":"12px"},"helpTextFontStyle":{"type":"string","default":""},"helpTextFontWeight":{"type":"string","default":""},"fieldBorder":{"type":"object","default":{"color":"#dddddd","style":"solid","width":"1px"}},"fieldBorderRadius":{"type":"string","default":"4px"},"fieldPaddingV":{"type":"string","default":"8px"},"fieldPaddingH":{"type":"string","default":"12px"},"fieldBackgroundColor":{"type":"string","default":""},"fieldTextColor":{"type":"string","default":""},"fieldPlaceholderColor":{"type":"string","default":""},"fieldFontFamily":{"type":"string","default":""},"fieldFontSize":{"type":"string","default":"14px"},"fieldFontStyle":{"type":"string","default":""},"fieldFontWeight":{"type":"string","default":""},"fieldMarginBottom":{"type":"string","default":"16px"},"fieldFocusBorderColor":{"type":"string","default":""},"buttonBackgroundColor":{"type":"string","default":""},"buttonTextColor":{"type":"string","default":""},"buttonFontFamily":{"type":"string","default":""},"buttonFontStyle":{"type":"string","default":""},"buttonFontWeight":{"type":"string","default":""},"buttonBorderWidth":{"type":"string","default":"0px"},"buttonBorderRadius":{"type":"string","default":"4px"},"buttonFontSize":{"type":"string","default":"14px"},"buttonPaddingV":{"type":"string","default":"10px"},"buttonPaddingH":{"type":"string","default":"20px"},"uploadButtonBackgroundColor":{"type":"string","default":""},"uploadButtonTextColor":{"type":"string","default":""},"uploadButtonFontFamily":{"type":"string","default":""},"uploadButtonFontStyle":{"type":"string","default":""},"uploadButtonFontWeight":{"type":"string","default":""},"uploadButtonBorderColor":{"type":"string","default":"#dddddd"},"uploadButtonBorderWidth":{"type":"string","default":"1px"},"uploadButtonBorderRadius":{"type":"string","default":"4px"},"uploadButtonFontSize":{"type":"string","default":"13px"},"uploadButtonPaddingV":{"type":"string","default":"8px"},"uploadButtonPaddingH":{"type":"string","default":"16px"},"msButtonBackgroundColor":{"type":"string","default":"#1e1e1e"},"msButtonTextColor":{"type":"string","default":"#ffffff"},"msButtonFontFamily":{"type":"string","default":""},"msButtonFontStyle":{"type":"string","default":""},"msButtonFontWeight":{"type":"string","default":""},"msButtonBorderColor":{"type":"string","default":""},"msButtonBorderWidth":{"type":"string","default":"0px"},"msButtonBorderRadius":{"type":"string","default":"4px"},"msButtonFontSize":{"type":"string","default":"14px"},"msButtonPaddingV":{"type":"string","default":"10px"},"msButtonPaddingH":{"type":"string","default":"20px"},"msActiveBgColor":{"type":"string","default":""},"msActiveTextColor":{"type":"string","default":""},"msInactiveBgColor":{"type":"string","default":""}},"supports":{"color":{"background":true,"text":false,"gradients":false},"spacing":{"margin":true,"padding":true},"align":["wide","full"],"html":false},"editorScript":"wpuf-post-form-editor","editorStyle":"wpuf-post-form-editor-style"}');

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!******************************************!*\
  !*** ./src/js/blocks/post-form/index.js ***!
  \******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.json */ "./src/js/blocks/post-form/block.json");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/js/blocks/post-form/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/js/blocks/post-form/save.js");
/* harmony import */ var _editor_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./editor.css */ "./src/js/blocks/post-form/editor.css");
// DESCRIPTION: Block registration entry point for the post form block.
// Registers the block with WordPress using block.json metadata.






(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_1__.name, {
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});
})();

/******/ })()
;
//# sourceMappingURL=post-form.js.map