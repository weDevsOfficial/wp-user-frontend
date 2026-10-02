import { __ } from '@wordpress/i18n';

export default function EmptyState() {
    const data = window.wpuf_form_builder || {};
    const assetUrl = data.asset_url || '';

    return (
        <div className="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-[80vh]">
            <img src={ `${ assetUrl }/images/form-blank-state.svg` } alt="" />
            <h2 className="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8 wpuf-mb-2">
                { __( 'Add fields and build your desired form', 'wp-user-frontend' ) }
            </h2>
            <p className="wpuf-text-sm wpuf-text-gray-500">
                { __( 'Add the necessary field and build your form.', 'wp-user-frontend' ) }
            </p>
        </div>
    );
}
