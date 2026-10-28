// DESCRIPTION: The retired `wpuf/form` block, kept only so posts saved with it
// open cleanly: it saves the same markup (a div with the form shortcode, so
// the front end is unchanged) and turns itself into `wpuf/post-form`.

import { createBlock, registerBlockType } from '@wordpress/blocks';
import { useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const toPostForm = ( attributes ) => createBlock( 'wpuf/post-form', { formId: attributes.formID || 0 } );

registerBlockType( 'wpuf/form', {
    apiVersion: 3,
    title: __( 'WPUF Forms (Legacy)', 'wp-user-frontend' ),
    icon: 'feedback',
    category: 'wpuf',
    supports: { inserter: false, html: false },
    attributes: {
        formID: { type: 'integer', default: 0 },
        formName: { type: 'string', default: '' },
    },
    transforms: {
        to: [ { type: 'block', blocks: [ 'wpuf/post-form' ], transform: toPostForm } ],
    },
    edit: function LegacyForm( { clientId, attributes } ) {
        const { replaceBlocks } = useDispatch( 'core/block-editor' );

        useEffect( () => {
            replaceBlocks( clientId, toPostForm( attributes ) );
        }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

        return null;
    },
    // The markup the old block saved: the front end renders the shortcode.
    save: ( { attributes } ) => ( attributes.formID ? <div>{ `[wpuf_form id=${ parseInt( attributes.formID, 10 ) }]` }</div> : null ),
} );
