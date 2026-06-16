// DESCRIPTION: Block registration entry point for the subscription packs block.
// Registers the block with WordPress using block.json metadata.

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import Save from './save';
import '../../../css/blocks/subscription-packs/editor.css';

registerBlockType( metadata.name, {
    edit: Edit,
    save: Save,
} );
