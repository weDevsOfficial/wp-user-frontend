/**
 * weform Form Block
 *
 * A block for embedding a weform Form form using Gutenberg
 */
( function( blocks, i18n, element, components ) {

    var el = element.createElement, // function to create elements
        SelectControl = components.SelectControl, // select control
        InspectorControls = blocks.InspectorControls, // sidebar controls
        Sandbox = components.Sandbox; // needed to register the block

    // register our block
    blocks.registerBlockType( 'wpuf/form', {
        title: 'WPUF ' + i18n.__( 'Form' ),
        icon: 'feedback',
        category: 'common',

        attributes: {
            formID: {
                type: 'integer',
                default: 0
            }
        },

        //implement the edit function
        edit: function( props ) {

            var focus = props.focus;
            var formID = props.attributes.formID;
            var children = [];

            if ( ! formID )
                formID = ''; // Default.

            function onFormChange( newFormID ) {
                // updates the form id on the props
                props.setAttributes( { formID: newFormID } );
            }

            // Set up the form dropdown in the side bar 'block' settings
            var inspectorControls = el( InspectorControls, {},
                el( SelectControl,
                    {
                        label: i18n.__( 'Selected Form' ),
                        value: formID,
                        options: wpufblock.forms,
                        onChange: onFormChange
                    }
                )
            );

            /**
             * Create the div container, add an overlay so the user can interact
             * with the form in Gutenberg, then render the iframe with form
             */
            if ( '' === formID ) {
                children.push(
                    el( 'div', { style : {width: '100%' } },
                    el( 'img',{ src: wpufblock.block_logo, className : 'wpuf-icon' }),
                    el( 'h3', { className : 'wpuf-title' }, 'WP User Frontend' ),
                    el( SelectControl, { value: formID, options: wpufblock.forms, onChange: onFormChange })
                ) );
            } else {
                children.push(
                    el( 'div', { className: 'wpuf-form-container' },
                        el( 'div', { className: 'wpuf-form-overlay'} ),
                        el( 'iframe', { src: wpufblock.siteUrl + '?wpuf_preview&wpuf_iframe&form_id=' + formID, height: '0', width: '500', scrolling: 'no' })
                    )
                )
            }

            return [
                children,
                !! focus && inspectorControls
            ];
        },

        //implement the handle function
        save: function( props ) {

            var formID = props.attributes.formID;
            if ( ! formID )
                return '';
            /**
             * we're essentially just adding a short code, here is where
             * it's save in the editor
             *
             * return content wrapped in DIV as raw HTML is unsupported
             */
            var returnHTML = '[wpuf_form id=' + parseInt( formID ) + ']';
            return el( 'div', null, returnHTML );
        }
    } );

} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components
);
