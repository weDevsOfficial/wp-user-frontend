import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { ChevronDown, Save } from 'lucide-react';
import { STORE_NAME } from '../../store';
import useFormSave from '../../hooks/useFormSave';
import LegacySlot, { getLegacySlots } from '../../common/LegacySlot';
import { getRouteQuery, inApp, openRoute } from '../../../../app/client';
import { builderData } from '../../utils/boot';

/**
 * Builder URL of another form of the same type: its app route in the admin
 * app, else its builder page. The store's form type is the post type.
 *
 * @param {string} formType Form post type (`wpuf_forms`, `wpuf_profile`).
 * @param {number} id       Form ID.
 *
 * @return {string} URL.
 */
const formEditUrl = ( formType, id ) => {
    const profile = 'wpuf_profile' === formType || 'profile' === formType;

    // Another form opens on the same builder tab and settings section.
    const query = getRouteQuery();
    const keep = 'settings' === query.tab ? `tab=settings${ query.section ? `&section=${ encodeURIComponent( query.section ) }` : '' }` : '';

    if ( inApp() ) {
        return `#/${ profile ? 'registration' : 'post' }-forms/${ id }/edit${ keep ? `?${ keep }` : '' }`;
    }

    return `admin.php?page=wpuf-${ profile ? 'profile' : 'post' }-forms&action=edit&id=${ id }${ keep ? `&${ keep }` : '' }`;
};

export default function Header( { activeTab, onTabChange } ) {
    const { post, formType } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            post: store.getPost(),
            formType: store.getFormType(),
        };
    }, [] );

    const { updateField, markDirty } = useDispatch( STORE_NAME );
    const { isSaving, saveForm } = useFormSave();
    const isDirty = useSelect( ( select ) => select( STORE_NAME ).getIsDirty(), [] );

    const [ isTitleEditing, setIsTitleEditing ] = useState( false );
    const [ title, setTitle ] = useState( post.post_title || '' );
    const [ isDropdownOpen, setIsDropdownOpen ] = useState( false );
    const [ copiedShortcode, setCopiedShortcode ] = useState( null );

    const data = builderData();
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
        // The title is saved from the form element, not the store: a rename
        // alone must still enable Save and the leave prompt.
        markDirty();
    };

    const handleTitleBlur = () => {
        setIsTitleEditing( false );
    };

    const handleTitleConfirm = () => {
        setIsTitleEditing( false );
    };

    const isPostForm = 'wpuf_forms' === formType || 'post' === formType;
    const submissionsPath = `/post-forms/${ post.ID }/submissions`;
    const submissionsUrl = `admin.php?page=wp-user-frontend#${ submissionsPath }`;

    const tabs = [
        { id: 'form-editor', label: __( 'Form Editor', 'wp-user-frontend' ) },
        { id: 'form-settings', label: __( 'Settings', 'wp-user-frontend' ) },
    ];

    return (
        <div className="bg-white rounded-lg ring-1 ring-gray-200 mx-4 mt-4 px-4 py-2.5">
            <div className="flex items-center justify-between gap-4">
                <div className="flex min-w-0 flex-1 items-center">
                    <img
                        src={ `${ data.asset_url || '' }/images/wpuf-icon-circle.svg` }
                        alt={ __( 'WPUF Icon', 'wp-user-frontend' ) }
                        className="mr-1.5 size-7 shrink-0"
                    />
                    <nav className="flex items-center" aria-label={ __( 'Tabs', 'wp-user-frontend' ) }>
                        <div className="relative flex">
                            <div className="flex items-center">
                                <input
                                    onClick={ () => setIsTitleEditing( ! isTitleEditing ) }
                                    value={ title }
                                    onChange={ handleTitleChange }
                                    type="text"
                                    name="post_title"
                                    className={ `text-gray-900 text-sm! font-medium min-h-8! py-1! px-2! wpuf-field-sizing-content focus:ring-primary! focus:border-transparent! focus:shadow-none! ${ isTitleEditing ? '' : 'border-transparent!' }` }
                                />
                                { isTitleEditing && (
                                    <i
                                        onClick={ handleTitleConfirm }
                                        className="fa fa-check leading-none! hover:cursor-pointer ml-1 text-sm text-gray-500"
                                        role="button"
                                        tabIndex={ 0 }
                                        onKeyDown={ ( e ) => e.key === 'Enter' && handleTitleConfirm() }
                                    />
                                ) }
                                { ! isTitleEditing && forms.length > 1 && (
                                    <div className="relative ml-1">
                                        <button
                                            type="button"
                                            className="wpuf-btn m-1 h-min min-h-min border-0 ring-0 shadow-none p-0"
                                            onClick={ () => setIsDropdownOpen( ! isDropdownOpen ) }
                                            onBlur={ () => setTimeout( () => setIsDropdownOpen( false ), 150 ) }
                                        >
                                            <ChevronDown className={ `size-4 text-gray-500 transition-transform ${ isDropdownOpen ? 'rotate-180' : '' }` } aria-hidden="true" />
                                        </button>
                                        { isDropdownOpen && (
                                            <ul className="absolute z-10 w-52 bg-white rounded-md shadow-sm mt-1 p-0 list-none">
                                                { forms.map( ( form ) => (
                                                    <li key={ form.id }>
                                                        <a
                                                            className="block rounded-none font-medium text-left px-4 py-2 text-sm! text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:shadow-none focus:outline-hidden"
                                                            href={ formEditUrl( formType, form.id ) }
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
                                className="group flex shrink-0 items-center px-2.5 py-1.5 rounded-md border border-gray-200 hover:border-gray-300 hover:cursor-pointer ml-3 text-gray-600 text-xs font-medium leading-none bg-gray-50"
                                title={ __( 'Click to copy shortcode', 'wp-user-frontend' ) }
                                onClick={ () => copyShortcode( clipText, scKey ) }
                            >
                                { label }
                                <span className="ml-1.5">
                                    { copiedShortcode === scKey ? (
                                        <svg className="rotate-6 stroke-primary!" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 13l4 4L19 7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" stroke="currentColor" />
                                        </svg>
                                    ) : (
                                        <svg className="group-hover:rotate-6 group-hover:stroke-gray-500 stroke-gray-400" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    ) }
                                </span>
                            </button>
                        );
                    } ) }
                </div>
                <div className="flex shrink-0 items-center">
                    <div className="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                        { tabs.map( ( tab ) => (
                            <a
                                key={ tab.id }
                                onClick={ ( e ) => {
                                    e.preventDefault();
                                    onTabChange( tab.id );
                                } }
                                className={ `wpuf-nav-tab px-3 py-1.5 text-sm font-medium leading-5 rounded-md hover:text-gray-900 focus:shadow-none hover:cursor-pointer wpuf-transition-all ${ activeTab === tab.id ? 'wpuf-nav-tab-active bg-white text-gray-900 shadow-xs' : 'text-gray-500' }` }
                                href={ `#${ tab.id }` }
                                role="tab"
                                aria-selected={ activeTab === tab.id }
                            >
                                { tab.label }
                            </a>
                        ) ) }
                        { /* Post forms: the posts the form created, on the list's Submissions page. */ }
                        { isPostForm && post.ID ? (
                            <a
                                onClick={ ( e ) => {
                                    e.preventDefault();
                                    openRoute( submissionsPath, submissionsUrl );
                                } }
                                className="wpuf-nav-tab px-3 py-1.5 text-sm font-medium leading-5 rounded-md text-gray-500 hover:text-gray-900 focus:shadow-none hover:cursor-pointer wpuf-transition-all"
                                href={ submissionsUrl }
                                role="tab"
                                aria-selected={ false }
                            >
                                { __( 'Submissions', 'wp-user-frontend' ) }
                            </a>
                        ) : null }
                        <div id="wpuf-pro-tab-slot" />
                        { /* Tabs other plugins printed on wpuf-form-builder-tabs-{type}. */ }
                        <LegacySlot id="tab-nav" html={ getLegacySlots().tabs.nav } className="flex items-center" />
                    </div>
                </div>
                <div className="flex min-w-0 flex-1 items-center justify-end gap-2">
                    { previewUrl && (
                        <a
                            href={ `${ previewUrl }?wpuf_preview=1&form_id=${ post.ID }` }
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex h-10 items-center gap-x-2 rounded-md px-5 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 ring-1 ring-inset ring-gray-300 focus:shadow-none focus:border-none leading-none shadow-xs"
                        >
                            { __( 'Preview', 'wp-user-frontend' ) }
                            <svg width="16" height="11" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.69947 7.26867C1.6419 7.09594 1.64184 6.90895 1.69931 6.73619C2.85628 3.2581 6.13716 0.75 10.0038 0.75C13.8687 0.75 17.1484 3.25577 18.3068 6.73134C18.3643 6.90406 18.3644 7.09106 18.3069 7.26381C17.15 10.7419 13.8691 13.25 10.0024 13.25C6.1375 13.25 2.85787 10.7442 1.69947 7.26867Z" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M12.5032 7C12.5032 8.38071 11.3839 9.5 10.0032 9.5C8.62246 9.5 7.50317 8.38071 7.50317 7C7.50317 5.61929 8.62246 4.5 10.0032 4.5C11.3839 4.5 12.5032 5.61929 12.5032 7Z" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </a>
                    ) }
                    { ! isSaving ? (
                        <button
                            onClick={ saveForm }
                            type="button"
                            disabled={ ! isDirty }
                            title={ isDirty ? undefined : __( 'No changes to save', 'wp-user-frontend' ) }
                            className="wpuf-btn-primary inline-flex h-10 items-center gap-2 leading-5 cursor-pointer px-5! py-0! text-sm! font-medium! shadow-sm disabled:cursor-not-allowed! disabled:opacity-50!"
                        >
                            <Save size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
                            { __( 'Save', 'wp-user-frontend' ) }
                        </button>
                    ) : (
                        <button type="button" className="wpuf-btn-primary inline-flex h-10 items-center gap-2 leading-5 px-5! py-0! text-sm! font-medium! shadow-sm opacity-75 button-ajax-working" disabled>
                            <span className="loader" />{ ' ' }
                            { __( 'Saving Form Data', 'wp-user-frontend' ) }
                        </button>
                    ) }
                </div>
            </div>
        </div>
    );
}
