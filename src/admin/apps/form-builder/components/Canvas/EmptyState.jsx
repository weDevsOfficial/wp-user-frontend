import { __ } from '@wordpress/i18n';
import { builderData } from '../../utils/boot';

export default function EmptyState() {
    const data = builderData();
    const assetUrl = data.asset_url || '';

    return (
        <div className="flex flex-col items-center justify-center h-[80vh]">
            <img src={ `${ assetUrl }/images/form-blank-state.svg` } alt="" />
            <h2 className="text-lg text-gray-800 mt-8 mb-2">
                { __( 'Add fields and build your desired form', 'wp-user-frontend' ) }
            </h2>
            <p className="text-sm text-gray-500">
                { __( 'Add the necessary field and build your form.', 'wp-user-frontend' ) }
            </p>
        </div>
    );
}
