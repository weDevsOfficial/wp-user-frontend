/**
 * Change the profile photo and the cover photo from the account header (Pro,
 * `profile.photos_editable`): hover or focus the avatar or the cover band,
 * pick an image, it uploads to `wpuf/v1/account/avatar|cover` with progress;
 * Remove goes back to Gravatar / initials or the plain band. Without Pro the
 * header only shows the photos.
 *
 * @since WPUF_SINCE
 */
import { useDispatch } from '@wordpress/data';
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, alertDialog, api, confirmDialog, icons } from '@wpuf/frontend-kit';
import { store } from './store';

const { Camera, ImageUp, Trash2 } = icons;

const ROUTES = { avatar: '/account/avatar', cover: '/account/cover' };

/**
 * One photo's upload / remove state and actions.
 *
 * @param {string} kind avatar|cover
 * @return {Object} { input, pick, onFile, remove, busy, progress }
 */
function usePhoto( kind ) {
    const input = useRef( null );
    const [ busy, setBusy ] = useState( false );
    const [ progress, setProgress ] = useState( 0 );
    const { setProfile } = useDispatch( store );

    const fail = ( error ) => alertDialog( { html: ( error && error.message ) || __( 'The photo could not be saved.', 'wp-user-frontend' ) } );

    const onFile = async ( event ) => {
        const file = event.target.files && event.target.files[ 0 ];

        event.target.value = '';

        if ( ! file ) {
            return;
        }

        setBusy( true );
        setProgress( 0 );

        try {
            const response = await api.uploadFile( ROUTES[ kind ], file, {}, setProgress );

            setProfile( response.profile );
        } catch ( error ) {
            fail( error );
        } finally {
            setBusy( false );
        }
    };

    const remove = async () => {
        const ok = await confirmDialog( {
            title: 'cover' === kind ? __( 'Remove the cover photo?', 'wp-user-frontend' ) : __( 'Remove the profile photo?', 'wp-user-frontend' ),
            confirmText: __( 'Remove', 'wp-user-frontend' ),
        } );

        if ( ! ok ) {
            return;
        }

        setBusy( true );

        try {
            const response = await ( 'cover' === kind ? api.deleteCover() : api.deleteAvatar() );

            setProfile( response.profile );
        } catch ( error ) {
            fail( error );
        } finally {
            setBusy( false );
        }
    };

    return { input, pick: () => input.current && input.current.click(), onFile, remove, busy, progress };
}

/**
 * @param {Object} props
 * @param {string} props.kind     avatar|cover
 * @param {Object} props.photo    usePhoto() result
 * @param {string} props.label    Accessible name of the file input
 */
function FileInput( { kind, photo, label } ) {
    return (
        <input
            ref={ photo.input }
            type="file"
            accept="image/png,image/jpeg,image/gif,image/webp"
            className="wpuf-sr-only"
            tabIndex={ -1 }
            aria-label={ label }
            data-wpuf-photo-input={ kind }
            onChange={ photo.onFile }
        />
    );
}

/**
 * The avatar with its hover / focus overlay.
 *
 * @param {Object}  props
 * @param {Object}  props.person   Profile
 * @param {*}       props.children The avatar
 */
export function AvatarPhoto( { person, children } ) {
    const photo = usePhoto( 'avatar' );

    if ( ! person.photos_editable ) {
        return children;
    }

    return (
        <div className={ 'wpuf-profile-photo' + ( photo.busy ? ' is-busy' : '' ) }>
            { children }
            <button type="button" className="wpuf-profile-photo__change" onClick={ photo.pick } disabled={ photo.busy } aria-label={ __( 'Change profile photo', 'wp-user-frontend' ) }>
                <Camera size={ 20 } aria-hidden="true" />
                <span className="wpuf-profile-photo__text">{ photo.busy ? `${ Math.round( photo.progress * 100 ) }%` : __( 'Change', 'wp-user-frontend' ) }</span>
            </button>
            { person.avatar?.custom && ! photo.busy && (
                <button type="button" className="wpuf-profile-photo__remove" onClick={ photo.remove } aria-label={ __( 'Remove profile photo', 'wp-user-frontend' ) } title={ __( 'Remove profile photo', 'wp-user-frontend' ) }>
                    <Trash2 size={ 14 } aria-hidden="true" />
                </button>
            ) }
            <FileInput kind="avatar" photo={ photo } label={ __( 'Profile photo', 'wp-user-frontend' ) } />
        </div>
    );
}

/**
 * The cover band's change / remove buttons (top right, shown on hover / focus).
 *
 * @param {Object} props
 * @param {Object} props.person Profile
 */
export function CoverPhotoActions( { person } ) {
    const photo = usePhoto( 'cover' );

    if ( ! person.photos_editable ) {
        return null;
    }

    return (
        <div className={ 'wpuf-profile-cover__actions' + ( photo.busy ? ' is-busy' : '' ) }>
            <Button variant="secondary" size="sm" onClick={ photo.pick } disabled={ photo.busy }>
                <ImageUp size={ 14 } aria-hidden="true" />
                { photo.busy ? `${ Math.round( photo.progress * 100 ) }%` : ( person.cover?.url ? __( 'Change cover', 'wp-user-frontend' ) : __( 'Add cover', 'wp-user-frontend' ) ) }
            </Button>
            { person.cover?.url && ! photo.busy && (
                <Button variant="secondary" size="sm" onClick={ photo.remove } aria-label={ __( 'Remove cover photo', 'wp-user-frontend' ) } title={ __( 'Remove cover photo', 'wp-user-frontend' ) }>
                    <Trash2 size={ 14 } aria-hidden="true" />
                </Button>
            ) }
            <FileInput kind="cover" photo={ photo } label={ __( 'Cover photo', 'wp-user-frontend' ) } />
        </div>
    );
}
