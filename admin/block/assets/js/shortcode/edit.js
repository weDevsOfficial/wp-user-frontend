import {__} from '@wordpress/i18n'
import {
    PanelBody,
    RangeControl,
    ToggleControl,
    PanelRow
} from "@wordpress/components";

import {InspectorControls, useBlockProps} from "@wordpress/block-editor";
import ServerSideRender from '@wordpress/server-side-render'
import Select from 'react-select';

export default function Edit({attributes, setAttributes}) {
    const {
        selectedShortCode,
        post_type,
        form_id,
        featured_image,
        category,
        meta,
        excerpt,
        payment_column,
        id,
        per_page,
        roles_exclude,
        roles_include
    } = attributes
    let sc_opts = [];

    function capitalize_label(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, str => str.toUpperCase())
    }

    function capitalize_first_word(str) {
        return str.replace(/\w+/, str.split(' ')[0].toUpperCase());
    }

    sc_opts.unshift(add_default('shortcode'))

    wpuf_shortcods.forEach(function (sc) {
        sc_opts.push({'label': capitalize_first_word(capitalize_label(sc.code)), 'value': sc.code})
    });

    function setAttribute(att, value) {
        setAttributes({[att]: value});
    }

    function add_default(att) {
        return {'value': null, 'label': 'Select ' + capitalize_label(att), 'disabled': true}
    }

    const select_style = {
        container: style => ({
            ...style,
            width: "100%",
        })
    }

    function render_atts_control(atts_data) {
        const atts = wpuf_shortcods.filter(sc => sc.code === selectedShortCode)[0].atts;
        let controls = [];

        Object.keys(atts).forEach(att => {
            let type = atts[att];

            if ('SELECT' === type || 'SELECT2' === type) {
                if (JSON.stringify(atts_data[att][0]) !== JSON.stringify(add_default(att))) {
                    atts_data[att].unshift(add_default(att));
                }
            }

            switch (type) {
                case 'SELECT':
                    controls.push(<PanelRow key={selectedShortCode + '_' + att}> <Select
                        defaultValue={atts_data[att][0]}
                        name={att}
                        value={attributes[att]}
                        options={atts_data[att]}
                        onChange={(opt) => {
                            setAttribute(att, opt)
                        }}
                        styles={select_style}
                        placeholder={atts_data[att][0].label}
                    /></PanelRow>)
                    break;
                case 'SELECT2':
                    controls.push(<PanelRow key={selectedShortCode + '_' + att}> <Select
                        isMulti
                        defaultValue={atts_data[att][0]}
                        name={att}
                        value={attributes[att]}
                        options={atts_data[att]}
                        onChange={(opt) => {
                            setAttribute(att, opt)
                        }}
                        styles={select_style}
                        placeholder={atts_data[att][0].label}
                    /></PanelRow>)
                    break;
                case 'SWITCHER':
                    controls.push(<PanelRow key={selectedShortCode + '_' + att}><ToggleControl
                        label={__(capitalize_label(att))}
                        checked={attributes[att]}
                        onChange={(value) => {
                            setAttribute(att, value)
                        }}
                    /></PanelRow>)
                    break;
                case 'NUMBER':
                    controls.push(
                        <PanelRow key={selectedShortCode + '_' + att}>
                            <RangeControl
                                label={'Select ' + capitalize_label(att)}
                                value={attributes[att]}
                                onChange={(value) => setAttribute(att, value)}
                                min={1}
                                step={1}
                            />
                        </PanelRow>)
                    break
            }
        })
        return controls;
    }

    return (
        <>
            <div {...useBlockProps()}>
                <ServerSideRender
                    block="wpuf/shortcode"
                    attributes={attributes}
                />
                <InspectorControls>
                    <PanelBody>
                        <PanelRow>
                            <Select
                                defaultValue={sc_opts[0]}
                                label="Select Shortcode"
                                name={selectedShortCode}
                                value={sc_opts.filter(function (option) {
                                    return option.value === attributes['selectedShortCode'];
                                })}
                                options={sc_opts}
                                onChange={(opt) => {
                                    setAttributes({selectedShortCode: opt.value})
                                }}
                                styles={select_style}
                            />
                        </PanelRow>
                        {selectedShortCode && render_atts_control(wpuf_shortcode_atts)}
                    </PanelBody>
                </InspectorControls>
            </div>
        </>
    )
}
