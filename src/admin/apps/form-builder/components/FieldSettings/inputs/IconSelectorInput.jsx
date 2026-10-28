import { useState, useEffect, useMemo, useCallback, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../store';
import { TextInput } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';
import { builderData } from '../../../utils/boot';

/**
 * Icon selector input for field settings.
 * Replaces Vue field-icon_selector component.
 *
 * Features: icon grid with search, image upload tab via wp.media,
 * (the default icon on Show Icon = Yes comes from SettingInput's revealedDefault()).
 */
export default function IconSelectorInput( { optionField, field, value, onChange: setValue } ) {
    const { updateField } = useDispatch( STORE_NAME );

    // A field saved before the icon settings has no icon_position: the Icon Position
    // select shows its default ("Left of Label") but the label (canvas and form,
    // Field_Contract::print_label()) shows no icon without a stored position. Picking
    // an icon stores that default too, so what the select shows is what is saved.
    const onChange = useCallback( ( next ) => {
        setValue( next );

        if ( next && ! field.icon_position ) {
            updateField( field.id, 'icon_position', 'left_label' );
        }
    }, [ setValue, field.id, field.icon_position, updateField ] );
    const [ showIconPicker, setShowIconPicker ] = useState( false );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const [ activeTab, setActiveTab ] = useState( 'icon' );
    const wrapperRef = useRef( null );

    const icons = useMemo( () => builderData().icons || [], [] );
    const i18n = builderData().i18n || {};

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

    // The default icon on Show Icon = Yes: SettingInput (revealedDefault()), since this
    // input only mounts once Show Icon is on.

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
            <div className="flex">
                { optionField.title && (
                    <label className="mb-0!">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>

            <div className="option-fields-section relative">
                { /* Trigger button */ }
                <div
                    onClick={ ( e ) => {
                        e.stopPropagation();
                        setShowIconPicker( ! showIconPicker );
                    } }
                    className="w-full mt-1.5 min-w-full py-2! px-3! text-gray-700 font-medium shadow-xs! border border-gray-300! rounded-md! hover:text-gray-700! flex justify-between items-center text-sm! cursor-pointer"
                    role="button"
                    tabIndex={ 0 }
                    onKeyDown={ ( e ) => e.key === 'Enter' && setShowIconPicker( ! showIconPicker ) }
                >
                    <div className="flex items-center gap-2">
                        { isImageValue && (
                            <img src={ value } alt="" style={ { width: 20, height: 20, objectFit: 'cover', borderRadius: 2 } } />
                        ) }
                        { ! isImageValue && value && (
                            <i className={ `${ value } text-gray-600` } />
                        ) }
                        <span>{ selectedIconDisplay }</span>
                    </div>
                    <div className="flex items-center gap-1">
                        { value && (
                            <i
                                className="fa fa-times text-gray-500 hover:text-red-500 cursor-pointer p-1"
                                onClick={ clearIcon }
                                role="button"
                                tabIndex={ 0 }
                                onKeyDown={ ( e ) => e.key === 'Enter' && clearIcon( e ) }
                            />
                        ) }
                        <i className={ `fa ${ showIconPicker ? 'fa-angle-up' : 'fa-angle-down' } text-base` } />
                    </div>
                </div>

                { /* Picker dropdown */ }
                { showIconPicker && (
                    <div
                        onClick={ ( e ) => e.stopPropagation() }
                        className="absolute bg-white border border-gray-300 rounded-lg w-full z-50 mt-1 shadow-lg right-0"
                        style={ { maxHeight: 350 } }
                    >
                        { /* Tabs */ }
                        <div className="flex border-b border-gray-200">
                            <button
                                type="button"
                                onClick={ ( e ) => {
                                    e.stopPropagation();
                                    setActiveTab( 'icon' );
                                } }
                                className={ `flex-1 py-2 px-4 text-sm font-medium border-b-2 transition-colors ${ activeTab === 'icon' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }` }
                            >
                                <i className="fa fa-icons mr-1" />
                                { __( 'Icons', 'wp-user-frontend' ) }
                            </button>
                            <button
                                type="button"
                                onClick={ ( e ) => {
                                    e.stopPropagation();
                                    setActiveTab( 'image' );
                                } }
                                className={ `flex-1 py-2 px-4 text-sm font-medium border-b-2 transition-colors ${ activeTab === 'image' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }` }
                            >
                                <i className="fa fa-image mr-1" />
                                { __( 'Upload Image', 'wp-user-frontend' ) }
                            </button>
                        </div>

                        { /* Icon Tab */ }
                        { activeTab === 'icon' && (
                            <div>
                                <div className="p-3 border-b border-gray-200">
                                    <TextInput
                                        value={ searchTerm }
                                        onChange={ setSearchTerm }
                                        placeholder={ __( 'Search icons... (e.g., user, email, home)', 'wp-user-frontend' ) }
                                        aria-label={ __( 'Search icons', 'wp-user-frontend' ) }
                                        className="w-full"
                                    />
                                    <div className="text-xs text-gray-500 mt-1">
                                        { iconCountLabel }
                                    </div>
                                </div>

                                <div className="wpuf-icon-grid-container" style={ { maxHeight: 210, overflowY: 'auto', padding: 10 } }>
                                    { filteredIcons.length > 0 ? (
                                        <div className="wpuf-icon-grid" style={ { display: 'grid', gridTemplateColumns: 'repeat(3, minmax(0, 1fr))', gap: 8 } }>
                                            { filteredIcons.map( ( icon ) => (
                                                <button
                                                    type="button"
                                                    key={ icon.class }
                                                    onClick={ () => selectIcon( icon.class ) }
                                                    className={ `wpuf-icon-grid-item flex min-h-14 cursor-pointer flex-col items-center justify-center gap-1 rounded-md border border-solid bg-white px-1 py-2 text-center transition-colors hover:border-primary hover:text-primary ${ value === icon.class ? 'selected border-primary bg-primary/5 text-primary' : 'border-gray-200 text-gray-600' }` }
                                                    title={ `${ icon.name } - ${ icon.keywords }` }
                                                    aria-pressed={ value === icon.class }
                                                >
                                                    <i className={ `${ icon.class } text-base leading-none` } />
                                                    <div className="max-w-full break-words text-[10px] leading-tight">
                                                        { icon.name }
                                                    </div>
                                                </button>
                                            ) ) }
                                        </div>
                                    ) : (
                                        <div className="text-center py-8 text-gray-500">
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
                            <div className="p-4">
                                <div className="text-center">
                                    { isImageValue && (
                                        <div className="mb-4">
                                            <img
                                                src={ value }
                                                alt=""
                                                style={ { maxWidth: 100, maxHeight: 100, objectFit: 'cover', borderRadius: 8, border: '2px solid #e0e0e0', margin: '0 auto' } }
                                            />
                                            <div className="text-xs text-gray-500 mt-2">
                                                { __( 'Current custom image', 'wp-user-frontend' ) }
                                            </div>
                                        </div>
                                    ) }

                                    <button
                                        type="button"
                                        onClick={ openMediaUploader }
                                        className="inline-flex items-center gap-2 px-3 py-2 bg-primary text-white rounded-md text-[13px] font-medium hover:bg-primaryHover transition-colors"
                                    >
                                        <i className="fa fa-upload" />
                                        { __( 'Upload an image to use as icon', 'wp-user-frontend' ) }
                                    </button>

                                    <p className="text-xs text-gray-500 mt-3">
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
