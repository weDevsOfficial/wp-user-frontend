/**
 * DESCRIPTION: UpdateButton component with Publish/Draft dropdown
 * DESCRIPTION: Renders a button with dropdown options for saving subscription
 */
import { __ } from '@wordpress/i18n';

const UpdateButton = ( {
	buttonText = __( 'Update', 'wp-user-frontend' ),
	isUpdating = false,
	onPublish,
	onSaveDraft,
} ) => {
	return (
		<div className="wpuf-relative">
			<button
				type="button"
				disabled={ isUpdating }
				className={ `wpuf-peer wpuf-inline-flex wpuf-justify-between wpuf-items-center wpuf-cursor-pointer wpuf-bg-primary hover:wpuf-bg-primaryHover wpuf-text-white wpuf-font-medium wpuf-text-base wpuf-py-2 wpuf-px-5 wpuf-rounded-md min-w-[122px] ${
					isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
				}` }
				onClick={ onPublish }
			>
				{ buttonText }
				<svg
					className="wpuf-rotate-180 wpuf-w-3 wpuf-h-3 shrink-0 wpuf-ml-4"
					data-accordion-icon=""
					aria-hidden="true"
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 10 6"
				>
					<path
						stroke="currentColor"
						strokeLinecap="round"
						strokeLinejoin="round"
						strokeWidth="2"
						d="M9 5 5 1 1 5"
					/>
				</svg>
			</button>
			<div className="wpuf-hidden hover:wpuf-block peer-hover:wpuf-block wpuf-cursor-pointer wpuf-w-44 wpuf-z-40 wpuf-bg-white wpuf-border border-[#DBDBDB] wpuf-absolute wpuf-z-10 wpuf-shadow wpuf-right-0 wpuf-rounded-md after:content-[''] before:content-[''] after:wpuf-absolute before:wpuf-absolute after:w-[13px] before:w-[70%] before:-right-[1px] after:h-[13px] before:wpuf-h-3 before:wpuf-mt-3 after:top-[-7px] before:wpuf--top-6 after:right-[1.4rem] after:z-[-1] after:wpuf-bg-white after:wpuf-border after:border-[#DBDBDB] after:!rotate-45 after:wpuf-border-r-0 after:wpuf-border-b-0">
				<button
					type="button"
					onClick={ onPublish }
					className={ `wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-primaryHover hover:wpuf-text-white wpuf-rounded-t-md ${
						isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
					}` }
					disabled={ isUpdating }
				>
					{ __( 'Publish', 'wp-user-frontend' ) }
				</button>
				<button
					type="button"
					onClick={ onSaveDraft }
					className={ `wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-primaryHover hover:wpuf-text-white wpuf-rounded-b-md ${
						isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
					}` }
					disabled={ isUpdating }
				>
					{ __( 'Save as Draft', 'wp-user-frontend' ) }
				</button>
			</div>
		</div>
	);
};

export default UpdateButton;
