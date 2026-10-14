/**
 * Uploads (image, featured image, and Pro's file / avatar / photo fields)
 * in the classic markup: `div#wpuf-<name>-<form>-upload-container >
 * div.wpuf-attachment-upload-filelist > a.button.file-selector` (or Pro's
 * `div.wpuf-file-upload-zone`) and `ul.wpuf-attachment-list.thumbnails` with
 * the rows `Upload_Ajax::attach_html()` prints. Files go to `POST /uploads`
 * (progress through XMLHttpRequest); remove calls DELETE /uploads/{id} for
 * a file added in this session and marks a stored one for
 * `delete_attachments[]`, as the classic form does. With the "image
 * caption" setting on, each row carries title / caption / description
 * (`wpuf_files_data[<id>][...]`). Value: a list of attachment ids.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useLayoutEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import Field, { FieldLabel } from '../components/Field';
import { deleteUpload, uploadFile } from '../api';

const SINGLE = [ 'featured_image', 'avatar', 'profile_photo', 'cover_photo' ];
const IMAGE_EXT = 'jpg,jpeg,jfif,gif,png,bmp,webp';

const extensionsOf = ( field ) => String( field.allowed_ext || ( 'file_upload' === field.template ? '' : IMAGE_EXT ) )
    .split( ',' )
    .map( ( ext ) => ext.trim().toLowerCase() )
    .filter( ( ext ) => ext && '*' !== ext );

const ZONE_ICON = (
    <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M5.75 14.75C2.98858 14.75 0.75 12.5114 0.75 9.75C0.75 7.30154 2.50992 5.26413 4.83376 4.83376C5.26412 2.50992 7.30154 0.75 9.75 0.75C12.1985 0.75 14.2359 2.50992 14.6662 4.83376C16.9901 5.26413 18.75 7.30154 18.75 9.75C18.75 12.5114 16.5114 14.75 13.75 14.75M6.75 9.75L9.75 6.75M9.75 6.75L12.75 9.75M9.75 6.75V18.75" stroke="#94A3B8" strokeWidth="1.5" strokeLinejoin="round" />
    </svg>
);

/** One uploaded file, as `Upload_Ajax::attach_html()` prints it. */
function AttachmentRow( { item, field, assets, captions, setCaption, onRemove } ) {
    const data = captions[ item.id ] || {};

    return (
        <li className="ui-state-default wpuf-image-wrap thumbnail">
            <div className="attachment-name">
                <img src={ item.thumb || item.url } alt={ item.title || '' } className="wpuf-attachment-image" />
            </div>
            <span className="wpuf-attach-title">{ item.title }</span>
            { captions.enabled && (
                <div className="wpuf-file-input-wrap">
                    <input type="text" value={ data.title ?? item.title ?? '' } placeholder={ __( 'Title', 'wp-user-frontend' ) } aria-label={ __( 'Title', 'wp-user-frontend' ) } onChange={ ( event ) => setCaption( item.id, 'title', event.target.value ) } />
                    <textarea value={ data.caption ?? item.caption ?? '' } placeholder={ __( 'Caption', 'wp-user-frontend' ) } aria-label={ __( 'Caption', 'wp-user-frontend' ) } onChange={ ( event ) => setCaption( item.id, 'caption', event.target.value ) } />
                    <textarea value={ data.desc ?? item.description ?? '' } placeholder={ __( 'Description', 'wp-user-frontend' ) } aria-label={ __( 'Description', 'wp-user-frontend' ) } onChange={ ( event ) => setCaption( item.id, 'desc', event.target.value ) } />
                </div>
            ) }
            <input type="hidden" name={ `wpuf_files[${ field.name }][]` } value={ item.id } />
            <div className="caption">
                <a
                    href="#remove"
                    className="attachment-delete"
                    data-attach-id={ item.id }
                    /* translators: %s: file title */
                    aria-label={ sprintf( __( 'Remove %s', 'wp-user-frontend' ), item.title || '' ) }
                    onClick={ ( event ) => {
                        event.preventDefault();
                        onRemove( item );
                    } }
                >
                    { ' ' }<img src={ `${ assets }/images/del-img.png` } alt="" />
                </a>
                <span className="wpuf-drag-file"> <img src={ `${ assets }/images/move-img.png` } alt="" /></span>
            </div>
        </li>
    );
}

export default function UploadField( { field, value, onChange, formId, error, schema, onDeleteStored, extras = {}, setExtra, setUploading } ) {
    const id = `${ field.name }_${ formId }`;
    const unique = `${ field.name }-${ formId }`;
    const ids = Array.isArray( value ) ? value.map( String ) : [];
    const [ items, setItems ] = useState( () => ( field.attachments || [] ).map( ( item ) => ( { ...item, id: String( item.id ), stored: true } ) ) );
    const [ progress, setProgress ] = useState( {} );
    const [ message, setMessage ] = useState( '' );
    const input = useRef( null );
    const container = useRef( null );

    const zone = 'file_upload' === field.template;
    const max = SINGLE.includes( field.template ) ? 1 : Number( field.count || 0 ) || 1;
    const extensions = extensionsOf( field );
    const maxSize = ( Number( field.max_size || 0 ) || 1024 ) * 1024;
    const assets = schema?.assets || '';
    const filesData = extras.wpuf_files_data || {};
    const captions = { ...filesData, enabled: !! schema?.image_caption };

    const setCaption = ( attachId, key, text ) => {
        setExtra && setExtra( 'wpuf_files_data', { ...filesData, [ attachId ]: { ...( filesData[ attachId ] || {} ), [ key ]: text } } );
    };

    const add = async ( files ) => {
        setMessage( '' );
        let current = [ ...ids ];

        for ( const file of Array.from( files ) ) {
            if ( current.length + Object.keys( progress ).length >= max ) {
                /* translators: %d: number of files */
                setMessage( sprintf( __( 'You have uploaded the maximum number of files (%d).', 'wp-user-frontend' ), max ) );
                break;
            }

            const ext = ( file.name.split( '.' ).pop() || '' ).toLowerCase();

            if ( extensions.length && ! extensions.includes( ext ) ) {
                setMessage( __( 'File extension not allowed.', 'wp-user-frontend' ) );
                continue;
            }

            if ( file.size > maxSize ) {
                /* translators: %s: size in kB */
                setMessage( sprintf( __( 'File size is too big. The limit is %s kB.', 'wp-user-frontend' ), Math.round( maxSize / 1024 ) ) );
                continue;
            }

            const key = `${ file.name }:${ file.size }:${ Date.now() }`;

            setProgress( ( state ) => ( { ...state, [ key ]: 0 } ) );

            try {
                const result = await uploadFile( '/uploads', file, { form_id: formId, type: field.name, wpuf_nonce: schema?.nonces?.upload || '' }, ( ratio ) => setProgress( ( state ) => ( { ...state, [ key ]: ratio } ) ) );
                const attachId = String( result.attach_id );

                setItems( ( list ) => [ ...list, { id: attachId, url: result.url, thumb: result.thumb, title: result.title, stored: false } ] );
                current = [ ...current, attachId ];
                onChange( current );
            } catch ( e ) {
                setMessage( e.message );
            } finally {
                setProgress( ( state ) => {
                    const next = { ...state };
                    delete next[ key ];

                    return next;
                } );
            }
        }

        if ( input.current ) {
            input.current.value = '';
        }
    };

    const remove = async ( item ) => {
        if ( item.stored ) {
            onDeleteStored && onDeleteStored( item.id );
        } else {
            try {
                await deleteUpload( item.id, schema?.nonces?.delete || '' );
            } catch ( e ) {
                // The attachment stays orphaned, as when the classic delete request fails.
            }
        }

        setItems( ( list ) => list.filter( ( entry ) => entry.id !== item.id ) );
        onChange( ids.filter( ( entry ) => entry !== item.id ) );
    };

    const full = ids.length >= max;
    const inFlight = Object.keys( progress ).length > 0;

    // The form keeps its submit disabled while a file uploads (upload.js added/uploaded).
    useEffect( () => {
        if ( setUploading ) {
            setUploading( field.name, inFlight );
        }
    }, [ inFlight, field.name, setUploading ] );

    useEffect( () => () => setUploading && setUploading( field.name, false ), [ field.name, setUploading ] );

    // upload.js showHide(): the template CSS forces `display: flex !important`, so the prompt is hidden the same way.
    useLayoutEffect( () => {
        if ( ! container.current ) {
            return;
        }

        container.current.querySelectorAll( '.file-selector, .wpuf-file-upload-zone' ).forEach( ( element ) => {
            if ( full ) {
                element.style.setProperty( 'display', 'none', 'important' );
            } else {
                element.style.removeProperty( 'display' );
            }
        } );
    }, [ full ] );
    const pick = ( event ) => {
        event.preventDefault();

        if ( ! full && input.current ) {
            input.current.click();
        }
    };
    const dropProps = {
        onDragOver: ( event ) => event.preventDefault(),
        onDrop: ( event ) => {
            event.preventDefault();
            add( event.dataTransfer.files );
        },
    };
    const sizeText = maxSize >= 1024 * 1024 ? `${ Math.round( ( maxSize / ( 1024 * 1024 ) ) * 10 ) / 10 }MB` : `${ Math.round( maxSize / 1024 ) }KB`;

    return (
        <Field field={ field } id={ id } error={ error || message }>
            <div id={ `wpuf-${ unique }-upload-container` } style={ { position: 'relative' } } ref={ container }>
                <div className="wpuf-file-warning" />
                <div className="wpuf-attachment-upload-filelist" data-type="file" { ...dropProps }>
                    <input
                        ref={ input }
                        id={ id }
                        type="file"
                        className="wpuf-file-input"
                        accept={ extensions.length ? extensions.map( ( ext ) => `.${ ext }` ).join( ',' ) : undefined }
                        multiple={ max > 1 }
                        tabIndex={ -1 }
                        onChange={ ( event ) => add( event.target.files ) }
                    />
                    { zone ? (
                        <div className="wpuf-file-upload-zone">
                            <div className="wpuf-upload-content">
                                <div className="wpuf-upload-icon">{ ZONE_ICON }</div>
                                <div className="wpuf-upload-title">
                                    <FieldLabel field={ field } id={ id } />
                                </div>
                                <div className="wpuf-upload-instructions">
                                    { field.instructions || sprintf(
                                        /* translators: 1: allowed file types, 2: maximum file size */
                                        __( 'Only %1$s files and max size of %2$s each', 'wp-user-frontend' ),
                                        extensions.length ? extensions.map( ( ext ) => ext.toUpperCase() ).join( ', ' ) : __( 'All file types', 'wp-user-frontend' ),
                                        sizeText
                                    ) }
                                </div>
                            </div>
                            <a id={ `wpuf-${ unique }-pickfiles` } className={ `wpuf-upload-trigger wpuf_${ field.name }_${ formId }` } href="#upload" aria-label={ field.label } onClick={ pick } />
                        </div>
                    ) : (
                        <a
                            id={ `wpuf-${ unique }-pickfiles` }
                            className={ `button file-selector wpuf_${ field.name }_${ formId }` }
                            href="#upload"
                            role="button"
                            aria-disabled={ full ? 'true' : undefined }
                            style={ { position: 'relative', zIndex: 1 } }
                            onClick={ pick }
                        >
                            { field.button_label || __( 'Select Image', 'wp-user-frontend' ) }
                        </a>
                    ) }
                    <ul className="wpuf-attachment-list thumbnails">
                        { items.map( ( item ) => (
                            <AttachmentRow key={ item.id } item={ item } field={ field } assets={ assets } captions={ captions } setCaption={ setCaption } onRemove={ remove } />
                        ) ) }
                        { Object.entries( progress ).map( ( [ key, ratio ] ) => (
                            <li key={ key } className="ui-state-default wpuf-image-wrap thumbnail wpuf-uploading" aria-busy="true">
                                <span className="wpuf-upload-percent">{ Math.round( ratio * 100 ) }%</span>
                                <span className="wpuf-upload-progress" style={ { width: `${ Math.round( ratio * 100 ) }%` } } />
                            </li>
                        ) ) }
                    </ul>
                </div>
            </div>
        </Field>
    );
}
