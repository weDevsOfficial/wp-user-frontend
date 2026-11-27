/**
 * develop's save button: "Update" / "Save" with a menu offering Publish and
 * Save as Draft (develop opened it on hover; here it opens on click and from
 * the keyboard). Busy while a save runs. A choice that would save nothing
 * (no changes, same status) is disabled.
 */
import { __ } from '@wordpress/i18n';
import { ChevronDown, Save } from 'lucide-react';
import { ActionMenu, Button } from '@wpuf/components';

const UpdateButton = ( {
	buttonText = __( 'Update', 'wp-user-frontend' ),
	isUpdating = false,
	onPublish,
	onSaveDraft,
	publishDisabled = false,
	draftDisabled = false,
} ) => (
	<ActionMenu
		label={ buttonText }
		items={ [
			{ key: 'publish', label: __( 'Publish', 'wp-user-frontend' ), onClick: onPublish, disabled: isUpdating || publishDisabled },
			{ key: 'draft', label: __( 'Save as Draft', 'wp-user-frontend' ), onClick: onSaveDraft, disabled: isUpdating || draftDisabled },
		] }
		trigger={
			<Button
				size="lg"
				busy={ isUpdating }
				disabled={ ! isUpdating && publishDisabled && draftDisabled }
				title={ ! isUpdating && publishDisabled && draftDisabled ? __( 'No changes to save', 'wp-user-frontend' ) : undefined }
			>
				{ ! isUpdating && <Save size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> }
				{ buttonText }
				<ChevronDown size={ 16 } strokeWidth={ 2 } className="-me-1 opacity-80" aria-hidden="true" />
			</Button>
		}
	/>
);

export default UpdateButton;
