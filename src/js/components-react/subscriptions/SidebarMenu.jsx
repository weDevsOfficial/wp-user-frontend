import { __ } from '@wordpress/i18n';

export default function SidebarMenu({ onCheckIsDirty }) {
    return (
        <div className="wpuf-sidebar-menu">
            <p>{__('Sidebar Menu Placeholder', 'wp-user-frontend')}</p>
        </div>
    );
}
