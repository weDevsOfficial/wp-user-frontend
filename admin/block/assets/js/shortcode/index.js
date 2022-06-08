import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('wpuf/shortcode', {
    /**
     * @see ./edit.js
     */
    edit: Edit,

    save() {
        return null
    },
});

