import { useState, useEffect, useMemo, useCallback, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../store';
import SettingHelpText from './SettingHelpText';

/**
 * Icon selector input for field settings.
 * Replaces Vue field-icon_selector component.
 *
 * Features: icon grid with search, image upload tab via wp.media,
 * auto-default icon when show_icon toggled on.
 */
export default function IconSelectorInput( { optionField, field, value, onChange } ) {
    const { updateField } = useDispatch( STORE_NAME );
    const [ showIconPicker, setShowIconPicker ] = useState( false );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const [ activeTab, setActiveTab ] = useState( 'icon' );
    const wrapperRef = useRef( null );

    const icons = useMemo( () => window.wpuf_form_builder?.icons || [], [] );
    const i18n = window.wpuf_form_builder?.i18n || {};

    const isImageValue = useMemo( () => {
        if ( ! value ) {
            return false;
        }
        return value.indexOf( 'http' ) === 0 || ( value.indexOf( '/' ) === 0 && value.charAt( 1 ) !== '/' );
    }, [ value ] );

    const selectedIconDisplay = useMemo( () => {
        if ( value ) {
            if ( isImageValue ) {
                return i18n.custom_image || __( 'Custom image', 'wp-user-frontend' );
            }
            const icon = icons.find( ( item ) => item.class === value );
            return icon ? icon.name : value;
        }
        return i18n.select_icon_or_upload || __( 'Select icon or upload', 'wp-user-frontend' );
    }, [ value, isImageValue, icons, i18n ] );

    const filteredIcons = useMemo( () => {
        if ( ! icons.length ) {
            return [];
        }
        if ( ! searchTerm ) {
            return icons;
        }
        const searchLower = searchTerm.toLowerCase();
        return icons.filter( ( icon ) =>
            icon.name.toLowerCase().includes( searchLower ) ||
            icon.keywords.toLowerCase().includes( searchLower )
        );
    }, [ icons, searchTerm ] );

    const iconCountLabel = useMemo( () => {
        const status = searchTerm
            ? ( i18n.icons_found || __( 'icons found', 'wp-user-frontend' ) )
            : ( i18n.icons_available || __( 'icons available', 'wp-user-frontend' ) );
        return `${ filteredIcons.length } ${ status }`;
    }, [ filteredIcons.length, searchTerm, i18n ] );

    // Auto-default icon when show_icon toggled on
    useEffect( () => {
        if ( field.show_icon === 'yes' && ( ! value || value === 'fas fa-0' ) ) {
            const defaultIcons = window.wpuf_form_builder?.defaultIcons || {};
            const fieldType = field.template || field.input_type || 'text';
            const defaultIcon = defaultIcons[ fieldType ] || 'fa-solid fa-circle';
            onChange( defaultIcon );
        }
    }, [ field.show_icon ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // Auto-switch tab based on value type
    useEffect( () => {
        if ( value ) {
            setActiveTab( isImageValue ? 'image' : 'icon' );
        }
    }, [ value, isImageValue ] );

    // Close picker on outside click
    useEffect( () => {
        if ( ! showIconPicker ) {
            return;
        }

        function handleClickOutside( e ) {
            if ( wrapperRef.current && ! wrapperRef.current.contains( e.target ) ) {
                setShowIconPicker( false );
            }
        }

        document.addEventListener( 'click', handleClickOutside );

        return () => document.removeEventListener( 'click', handleClickOutside );
    }, [ showIconPicker ] );

    const selectIcon = useCallback( ( iconClass ) => {
        onChange( iconClass );
        setShowIconPicker( false );
    }, [ onChange ] );

    const clearIcon = useCallback( ( e ) => {
        e.stopPropagation();
        onChange( '' );
        setShowIconPicker( false );
    }, [ onChange ] );

    const openMediaUploader = useCallback( ( e ) => {
        if ( e ) {
            e.stopPropagation();
        }

        if ( typeof wp === 'undefined' || ! wp.media ) {
            return;
        }

        const frame = wp.media( {
            title: i18n.select_icon_image || __( 'Select Icon Image', 'wp-user-frontend' ),
            button: { text: i18n.use_as_icon || __( 'Use as Icon', 'wp-user-frontend' ) },
            multiple: false,
            library: { type: 'image' },
        } );

        frame.on( 'select', () => {
            const attachment = frame.state().get( 'selection' ).first().toJSON();
            const url = ( attachment.sizes && attachment.sizes.thumbnail )
                ? attachment.sizes.thumbnail.url
                : attachment.url;
            onChange( url );
            setShowIconPicker( false );
        } );

        frame.open();
    }, [ onChange, i18n ] );

    return (
        <div className="panel-field-opt panel-field-opt-icon-selector" ref={ wrapperRef }>
            <div className="wpuf-flex">
                { optionField.title && (
                    <label className="!wpuf-mb-0">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>

            <div className="option-fields-section wpuf-relative">
                { /* Trigger button */ }
                <div
                    onClick={ ( e ) => {
                        e.stopPropagation();
                        setShowIconPicker( ! showIconPicker );
                    } }
                    className="wpuf-w-full wpuf-mt-4 wpuf-min-w-full !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base wpuf-cursor-pointer"
                    role="button"
                    tabIndex={ 0 }
                    onKeyDown={ ( e ) => e.key === 'Enter' && setShowIconPicker( ! showIconPicker ) }
                >
                    <div className="wpuf-flex wpuf-items-center wpuf-gap-2">
                        { isImageValue && (
                            <img src={ value } alt="" style={ { width: 20, height: 20, objectFit: 'cover', borderRadius: 2 } } />
                        ) }
                        { ! isImageValue && value && (
                            <i className={ `${ value } wpuf-text-gray-600` } />
                        ) }
                        <span>{ selectedIconDisplay }</span>
                    </div>
                    <div className="wpuf-flex wpuf-items-center wpuf-gap-1">
                        { value && (
                            <i
                                className="fa fa-times wpuf-text-gray-500 hover:wpuf-text-red-500 wpuf-cursor-pointer wpuf-p-1"
                                onClick={ clearIcon }
                                role="button"
                                tabIndex={ 0 }
                                onKeyDown={ ( e ) => e.key === 'Enter' && clearIcon( e ) }
                            />
                        ) }
                        <i className={ `fa ${ showIconPicker ? 'fa-angle-up' : 'fa-angle-down' } wpuf-text-base` } />
                    </div>
                </div>

                { /* Picker dropdown */ }
                { showIconPicker && (
                    <div
                        onClick={ ( e ) => e.stopPropagation() }
                        className="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-50 wpuf-mt-1 wpuf-shadow-lg wpuf-right-0"
                        style={ { maxHeight: 350, minWidth: 320, maxWidth: 400 } }
                    >
                        { /* Tabs */ }
                        <div className="wpuf-flex wpuf-border-b wpuf-border-gray-200">
                            <button
                                type="button"
                                onClick={ ( e ) => {
                                    e.stopPropagation();
                                    setActiveTab( 'icon' );
                                } }
                                className={ `wpuf-flex-1 wpuf-py-2 wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-border-b-2 wpuf-transition-colors ${ activeTab === 'icon' ? 'wpuf-border-blue-500 wpuf-text-blue-600' : 'wpuf-border-transparent wpuf-text-gray-500 hover:wpuf-text-gray-700' }` }
                            >
                                <i className="fa fa-icons wpuf-mr-1" />
                                { __( 'Icons', 'wp-user-frontend' ) }
                            </button>
                            <button
                                type="button"
                                onClick={ ( e ) => {
                                    e.stopPropagation();
                                    setActiveTab( 'image' );
                                } }
                                className={ `wpuf-flex-1 wpuf-py-2 wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-border-b-2 wpuf-transition-colors ${ activeTab === 'image' ? 'wpuf-border-blue-500 wpuf-text-blue-600' : 'wpuf-border-transparent wpuf-text-gray-500 hover:wpuf-text-gray-700' }` }
                            >
                                <i className="fa fa-image wpuf-mr-1" />
                                { __( 'Upload Image', 'wp-user-frontend' ) }
                            </button>
                        </div>

                        { /* Icon Tab */ }
                        { activeTab === 'icon' && (
                            <div>
                                <div className="wpuf-p-3 wpuf-border-b wpuf-border-gray-200">
                                    <input
                                        value={ searchTerm }
                                        onChange={ ( e ) => setSearchTerm( e.target.value ) }
                                        type="text"
                                        placeholder={ __( 'Search icons... (e.g., user, email, home)', 'wp-user-frontend' ) }
                                        className="wpuf-w-full !wpuf-px-4 !wpuf-py-1.5 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-text-sm wpuf-text-gray-900 placeholder:wpuf-text-gray-400 wpuf-shadow focus:!wpuf-shadow-none"
                                    />
                                    <div className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-1">
                                        { iconCountLabel }
                                    </div>
                                </div>

                                <div className="wpuf-icon-grid-container" style={ { maxHeight: 210, overflowY: 'auto', padding: 10 } }>
                                    { filteredIcons.length > 0 ? (
                                        <div className="wpuf-icon-grid" style={ { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 8 } }>
                                            { filteredIcons.map( ( icon ) => (
                                                <button
                                                    type="button"
                                                    key={ icon.class }
                                                    onClick={ () => selectIcon( icon.class ) }
                                                    className={ `wpuf-icon-grid-item${ value === icon.class ? ' selected' : '' }` }
                                                    title={ `${ icon.name } - ${ icon.keywords }` }
                                                    aria-pressed={ value === icon.class }
                                                    style={ { padding: '10px 5px', textAlign: 'center', border: '1px solid #e0e0e0', borderRadius: 4, cursor: 'pointer', transition: 'all 0.2s', minHeight: 60, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' } }
                                                >
                                                    <i className={ icon.class } style={ { fontSize: 18, marginBottom: 4, color: '#555' } } />
                                                    <div style={ { fontSize: 10, color: '#666', lineHeight: 1.2, wordBreak: 'break-word', maxWidth: '100%' } }>
                                                        { icon.name }
                                                    </div>
                                                </button>
                                            ) ) }
                                        </div>
                                    ) : (
                                        <div className="wpuf-text-center wpuf-py-8 wpuf-text-gray-500">
                                            <div style={ { fontSize: 16, marginBottom: 8 } }>
                                                { __( 'No icons found', 'wp-user-frontend' ) }
                                            </div>
                                            <div style={ { fontSize: 12 } }>
                                                { __( 'Try searching with different keywords like "user", "email", "home"', 'wp-user-frontend' ) }
                                            </div>
                                        </div>
                                    ) }
                                </div>
                            </div>
                        ) }

                        { /* Image Tab */ }
                        { activeTab === 'image' && (
                            <div className="wpuf-p-4">
                                <div className="wpuf-text-center">
                                    { isImageValue && (
                                        <div className="wpuf-mb-4">
                                            <img
                                                src={ value }
                                                alt=""
                                                style={ { maxWidth: 100, maxHeight: 100, objectFit: 'cover', borderRadius: 8, border: '2px solid #e0e0e0', margin: '0 auto' } }
                                            />
                                            <div className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-2">
                                                { __( 'Current custom image', 'wp-user-frontend' ) }
                                            </div>
                                        </div>
                                    ) }

                                    <button
                                        type="button"
                                        onClick={ openMediaUploader }
                                        className="wpuf-inline-flex wpuf-items-center wpuf-gap-2 wpuf-px-4 wpuf-py-2 wpuf-bg-blue-500 wpuf-text-white wpuf-rounded wpuf-text-sm wpuf-font-medium hover:wpuf-bg-blue-600 wpuf-transition-colors"
                                    >
                                        <i className="fa fa-upload" />
                                        { __( 'Upload an image to use as icon', 'wp-user-frontend' ) }
                                    </button>

                                    <p className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-3">
                                        { __( 'Recommended size: 32x32 pixels', 'wp-user-frontend' ) }
                                    </p>
                                </div>
                            </div>
                        ) }
                    </div>
                ) }
            </div>
        </div>
    );
}
