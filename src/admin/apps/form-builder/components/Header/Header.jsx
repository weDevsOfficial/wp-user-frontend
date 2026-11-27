import { useState, useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, Input, cn } from '@wedevs/plugin-ui';
import { Check, ChevronDown, Eye, Save, Search } from 'lucide-react';
import { Button } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import useFormSave from '../../hooks/useFormSave';
import LegacySlot, { getLegacySlots } from '../../common/LegacySlot';
import { getRouteQuery, inApp, openRoute } from '../../../../app/client';
import { builderData } from '../../utils/boot';
import { addQueryArgs } from '@wordpress/url';
import { FormUsage } from './FormUsage';

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

/**
 * The builder's form switcher: plugin-ui's DropdownMenu with the forms of the
 * same type, the open one checked; a search box once the list is long.
 *
 * @param {Object} props
 * @param {Array}  props.forms    [{ id, title }].
 * @param {number} props.current  Open form id.
 * @param {string} props.formType Form post type.
 */
function FormSwitcher( { forms, current, formType } ) {
    const [ search, setSearch ] = useState( '' );
    const shown = useMemo( () => {
        const term = search.trim().toLowerCase();

        return term ? forms.filter( ( form ) => String( form.title || '' ).toLowerCase().includes( term ) ) : forms;
    }, [ forms, search ] );

    return (
        <DropdownMenu onOpenChange={ ( open ) => ! open && setSearch( '' ) }>
            <DropdownMenuTrigger
                aria-label={ __( 'Switch form', 'wp-user-frontend' ) }
                className="inline-flex size-8 shrink-0 items-center justify-center rounded-md border-0 bg-transparent p-0 text-gray-500 cursor-pointer hover:bg-gray-100 hover:text-gray-700 focus-visible:ring-2 focus-visible:ring-primary/30 data-popup-open:bg-gray-100 [&[data-popup-open]>svg]:rotate-180"
            >
                <ChevronDown className="size-4 transition-transform" aria-hidden="true" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-72 max-w-[calc(100vw-2rem)] p-1 bg-white rounded-lg shadow-lg ring-1 ring-black/5 border-0">
                { forms.length > 8 && (
                    <div
                        className="relative mb-1 p-1"
                        // Typing goes to the box, not the menu's type-ahead; Escape / Tab / arrows still reach the menu.
                        onKeyDown={ ( event ) => ! [ 'Escape', 'Tab', 'ArrowDown', 'ArrowUp' ].includes( event.key ) && event.stopPropagation() }
                    >
                        <Search className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <Input
                            type="search"
                            value={ search }
                            onChange={ ( event ) => setSearch( event.target.value ) }
                            placeholder={ __( 'Search forms…', 'wp-user-frontend' ) }
                            aria-label={ __( 'Search forms', 'wp-user-frontend' ) }
                            className="h-8 pl-8 text-sm"
                        />
                    </div>
                ) }
                <div className="max-h-80 overflow-y-auto">
                    { shown.map( ( form ) => {
                        const active = Number( form.id ) === Number( current );

                        return (
                            <DropdownMenuItem
                                key={ form.id }
                                onClick={ () => {
                                    if ( ! active ) {
                                        window.location.assign( formEditUrl( formType, form.id ) );
                                    }
                                } }
                                className={ cn( 'flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm cursor-pointer data-highlighted:bg-gray-100 data-highlighted:text-gray-900', active ? 'font-medium text-gray-900' : 'text-gray-700' ) }
                            >
                                <span className="min-w-0 flex-1 truncate">{ form.title || __( '(no title)', 'wp-user-frontend' ) }</span>
                                <span className="shrink-0 text-xs text-gray-400">#{ form.id }</span>
                                { active && <Check className="size-4 shrink-0 text-primary" aria-hidden="true" /> }
                            </DropdownMenuItem>
                        );
                    } ) }
                    { ! shown.length && <p className="m-0 px-2 py-3 text-center text-sm text-gray-500">{ __( 'No forms found', 'wp-user-frontend' ) }</p> }
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

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
                    { /* The form name (plugin-ui Input, edited in place; saved with the form) and the form switcher. */ }
                    <div className="flex min-w-0 items-center gap-0.5">
                        <div className="relative flex min-w-0 items-center">
                            <Input
                                value={ title }
                                onChange={ handleTitleChange }
                                onFocus={ () => setIsTitleEditing( true ) }
                                onBlur={ handleTitleBlur }
                                onKeyDown={ ( event ) => 'Enter' === event.key && event.currentTarget.blur() }
                                type="text"
                                name="post_title"
                                aria-label={ __( 'Form name', 'wp-user-frontend' ) }
                                className={ cn(
                                    'h-8 min-w-24 max-w-[22rem] wpuf-field-sizing-content px-2 text-sm font-medium text-gray-900 shadow-none',
                                    isTitleEditing ? 'pr-8' : 'border-transparent bg-transparent hover:border-gray-200'
                                ) }
                            />
                            { isTitleEditing && (
                                <button
                                    type="button"
                                    onMouseDown={ ( event ) => event.preventDefault() }
                                    onClick={ ( event ) => {
                                        handleTitleConfirm();
                                        event.currentTarget.previousElementSibling?.blur();
                                    } }
                                    aria-label={ __( 'Done', 'wp-user-frontend' ) }
                                    className="absolute right-1 inline-flex size-6 items-center justify-center rounded border-0 bg-transparent p-0 text-gray-500 cursor-pointer hover:bg-gray-100 hover:text-primary"
                                >
                                    <Check className="size-4" aria-hidden="true" />
                                </button>
                            ) }
                        </div>
                        { ! isTitleEditing && forms.length > 1 && <FormSwitcher forms={ forms } current={ post.ID } formType={ formType } /> }
                    </div>

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
                    { /* Where the form is shown, or a new page with its shortcode. */ }
                    { post.ID ? (
                        <FormUsage
                            post={ post }
                            shortcode={ shortcodes[ 0 ]
                                ? ( shortcodes[ 0 ].type ? `[${ shortcodes[ 0 ].name } type="${ shortcodes[ 0 ].type }" id="${ post.ID }"]` : `[${ shortcodes[ 0 ].name } id="${ post.ID }"]` )
                                : `[wpuf_form id="${ post.ID }"]` }
                        />
                    ) : null }
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
                        <Button
                            variant="secondary"
                            size="lg"
                            // addQueryArgs: with plain permalinks the page URL already has `?page_id=`.
                            href={ addQueryArgs( previewUrl, { wpuf_preview: 1, form_id: post.ID } ) }
                            target="_blank"
                            rel="noopener noreferrer"
                            title={ isDirty ? __( 'Preview shows the last saved version. Save first to see your changes.', 'wp-user-frontend' ) : undefined }
                        >
                            { __( 'Preview', 'wp-user-frontend' ) }
                            <Eye size={ 16 } strokeWidth={ 1.5 } className="text-gray-500" aria-hidden="true" />
                        </Button>
                    ) }
                    <Button
                        size="lg"
                        onClick={ saveForm }
                        busy={ isSaving }
                        disabled={ ! isDirty }
                        title={ isDirty || isSaving ? undefined : __( 'No changes to save', 'wp-user-frontend' ) }
                    >
                        { ! isSaving && <Save size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> }
                        { isSaving ? __( 'Saving Form Data', 'wp-user-frontend' ) : __( 'Save', 'wp-user-frontend' ) }
                    </Button>
                </div>
            </div>
        </div>
    );
}
