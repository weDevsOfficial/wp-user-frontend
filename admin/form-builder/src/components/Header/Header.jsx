import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import useFormSave from '../../hooks/useFormSave';

export default function Header( { activeTab, onTabChange } ) {
    const { post, formType } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            post: store.getPost(),
            formType: store.getFormType(),
        };
    }, [] );

    const { updateField } = useDispatch( STORE_NAME );
    const { isSaving, saveForm } = useFormSave();

    const [ isTitleEditing, setIsTitleEditing ] = useState( false );
    const [ title, setTitle ] = useState( post.post_title || '' );
    const [ isDropdownOpen, setIsDropdownOpen ] = useState( false );
    const [ copiedShortcode, setCopiedShortcode ] = useState( null );

    const data = window.wpuf_form_builder || {};
    const previewUrl = data.preview_url || '';
    const forms = data.forms || [];
    const shortcodes = data.shortcodes || [];

    const copyShortcode = useCallback( ( text, key ) => {
        if ( navigator.clipboard ) {
            navigator.clipboard.writeText( text );
        }
        setCopiedShortcode( key );
        setTimeout( () => setCopiedShortcode( null ), 2000 );
    }, [] );

    const handleTitleChange = ( e ) => {
        setTitle( e.target.value );
    };

    const handleTitleBlur = () => {
        setIsTitleEditing( false );
    };

    const handleTitleConfirm = () => {
        setIsTitleEditing( false );
    };

    const tabs = [
        { id: 'form-editor', label: __( 'Form Editor', 'wp-user-frontend' ) },
        { id: 'form-settings', label: __( 'Settings', 'wp-user-frontend' ) },
    ];

    return (
        <div className="wpuf-bg-white wpuf-p-8 wpuf-justify-between wpuf-items-center wpuf-pb-7">
            <div className="wpuf-flex wpuf-justify-between">
                <div className="wpuf-flex wpuf-items-center">
                    <img
                        src={ `${ data.asset_url || '' }/images/wpuf-icon-circle.svg` }
                        alt="WPUF Icon"
                        className="wpuf-mr-2"
                    />
                    <nav className="wpuf-flex wpuf-items-center" aria-label="Tabs">
                        <div className="wpuf-relative wpuf-flex">
                            <div className="wpuf-flex wpuf-items-center">
                                <input
                                    onClick={ () => setIsTitleEditing( ! isTitleEditing ) }
                                    value={ title }
                                    onChange={ handleTitleChange }
                                    type="text"
                                    name="post_title"
                                    className={ `wpuf-text-gray-900 wpuf-text-base wpuf-field-sizing-content focus:!wpuf-ring-primary focus:!wpuf-border-transparent focus:!wpuf-shadow-none ${ isTitleEditing ? '' : '!wpuf-border-transparent' }` }
                                />
                                { isTitleEditing && (
                                    <i
                                        onClick={ handleTitleConfirm }
                                        className="fa fa-check !wpuf-leading-none hover:wpuf-cursor-pointer wpuf-ml-1 wpuf-text-base"
                                        role="button"
                                        tabIndex={ 0 }
                                        onKeyDown={ ( e ) => e.key === 'Enter' && handleTitleConfirm() }
                                    />
                                ) }
                                { ! isTitleEditing && forms.length > 1 && (
                                    <div className="wpuf-relative wpuf-ml-1">
                                        <button
                                            type="button"
                                            className="wpuf-btn wpuf-m-1 wpuf-h-min wpuf-min-h-min wpuf-border-0 wpuf-ring-0 wpuf-shadow-none wpuf-p-0"
                                            onClick={ () => setIsDropdownOpen( ! isDropdownOpen ) }
                                            onBlur={ () => setTimeout( () => setIsDropdownOpen( false ), 150 ) }
                                        >
                                            <i className={ `!wpuf-font-bold !wpuf-text-xl !wpuf-leading-none ${ isDropdownOpen ? 'fa fa-angle-up' : 'fa fa-angle-down' }` } />
                                        </button>
                                        { isDropdownOpen && (
                                            <ul className="wpuf-absolute wpuf-z-10 wpuf-w-52 wpuf-bg-white wpuf-rounded-md wpuf-shadow wpuf-mt-1 wpuf-p-0 wpuf-list-none">
                                                { forms.map( ( form ) => (
                                                    <li key={ form.id }>
                                                        <a
                                                            className="wpuf-block wpuf-rounded-none wpuf-font-medium wpuf-text-left wpuf-px-4 wpuf-py-2 !wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100 hover:wpuf-text-gray-900 focus:wpuf-shadow-none focus:wpuf-outline-none"
                                                            href={ `admin.php?page=wpuf-${ formType }-forms&action=edit&id=${ form.id }` }
                                                        >
                                                            { form.title }
                                                        </a>
                                                    </li>
                                                ) ) }
                                            </ul>
                                        ) }
                                    </div>
                                ) }
                            </div>
                        </div>
                    </nav>

                    { /* Shortcode copy badges */ }
                    { shortcodes.map( ( sc, i ) => {
                        const scKey = sc.type || i;
                        const clipText = sc.type
                            ? `[${ sc.name } type="${ sc.type }" id="${ post.ID }"]`
                            : `[${ sc.name } id="${ post.ID }"]`;
                        const label = sc.type ? `${ sc.type.charAt( 0 ).toUpperCase() + sc.type.slice( 1 ) }: #${ post.ID }` : `#${ post.ID }`;

                        return (
                            <button
                                key={ scKey }
                                type="button"
                                className="wpuf-group wpuf-flex wpuf-items-center wpuf-px-4.5 wpuf-py-2.5 wpuf-rounded-md wpuf-border wpuf-border-gray-300 hover:wpuf-cursor-pointer wpuf-ml-6 wpuf-text-gray-700 wpuf-text-base wpuf-leading-none wpuf-shadow-sm wpuf-bg-white"
                                title={ __( 'Click to copy shortcode', 'wp-user-frontend' ) }
                                onClick={ () => copyShortcode( clipText, scKey ) }
                            >
                                { label }
                                <span className="wpuf-ml-2">
                                    { copiedShortcode === scKey ? (
                                        <svg className="wpuf-rotate-6 !wpuf-stroke-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 13l4 4L19 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" stroke="currentColor" />
                                        </svg>
                                    ) : (
                                        <svg className="group-hover:wpuf-rotate-6 group-hover:wpuf-stroke-gray-500 wpuf-stroke-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    ) }
                                </span>
                            </button>
                        );
                    } ) }
                </div>
                <div className="wpuf-flex wpuf-space-x-4">
                    { previewUrl && (
                        <a
                            href={ `${ previewUrl }?wpuf_preview=1&form_id=${ post.ID }` }
                            target="_blank"
                            rel="noopener noreferrer"
                            className="wpuf-inline-flex wpuf-items-center wpuf-gap-x-3 wpuf-rounded-md wpuf-px-4.5 wpuf-py-2.5 wpuf-text-base wpuf-text-gray-700 hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 focus:wpuf-shadow-none focus:wpuf-border-none wpuf-leading-none wpuf-shadow-sm"
                        >
                            { __( 'Preview', 'wp-user-frontend' ) }
                            <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.69947 7.26867C1.6419 7.09594 1.64184 6.90895 1.69931 6.73619C2.85628 3.2581 6.13716 0.75 10.0038 0.75C13.8687 0.75 17.1484 3.25577 18.3068 6.73134C18.3643 6.90406 18.3644 7.09106 18.3069 7.26381C17.15 10.7419 13.8691 13.25 10.0024 13.25C6.1375 13.25 2.85787 10.7442 1.69947 7.26867Z" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M12.5032 7C12.5032 8.38071 11.3839 9.5 10.0032 9.5C8.62246 9.5 7.50317 8.38071 7.50317 7C7.50317 5.61929 8.62246 4.5 10.0032 4.5C11.3839 4.5 12.5032 5.61929 12.5032 7Z" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </a>
                    ) }
                    { ! isSaving ? (
                        <button
                            onClick={ saveForm }
                            type="button"
                            className="wpuf-btn-primary wpuf-leading-none wpuf-cursor-pointer"
                        >
                            { __( 'Save', 'wp-user-frontend' ) }
                        </button>
                    ) : (
                        <button type="button" className="button button-primary button-ajax-working" disabled>
                            <span className="loader" />{ ' ' }
                            { __( 'Saving Form Data', 'wp-user-frontend' ) }
                        </button>
                    ) }
                </div>
            </div>
            <div className="wpuf-flex wpuf-items-center wpuf-mt-8">
                <div className="wpuf-flex wpuf-bg-gray-100 wpuf-w-max wpuf-rounded-lg wpuf-p-2">
                    { tabs.map( ( tab ) => (
                        <a
                            key={ tab.id }
                            onClick={ ( e ) => {
                                e.preventDefault();
                                onTabChange( tab.id );
                            } }
                            className={ `wpuf-nav-tab wpuf-py-2 wpuf-px-4 wpuf-text-base hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-rounded-md hover:wpuf-drop-shadow-sm focus:wpuf-shadow-none wpuf-mr-2 hover:wpuf-cursor-pointer ${ activeTab === tab.id ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-rounded-md wpuf-drop-shadow-sm' : 'wpuf-text-gray-500' }` }
                            href={ `#${ tab.id }` }
                            role="tab"
                            aria-selected={ activeTab === tab.id }
                        >
                            { tab.label }
                        </a>
                    ) ) }
                    <div id="wpuf-pro-tab-slot" />
                </div>
            </div>
        </div>
    );
}
