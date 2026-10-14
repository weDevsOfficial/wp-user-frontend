/**
 * One post form (create or edit): state through the kit's useForm, submit
 * through the REST routes, the classic after-submit behaviour (message in
 * place of the form, or redirect), drafts, the login-required answer, the
 * exit warning while a form has unsaved changes.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import useDokanShopUrl from './useDokanShopUrl';
import { applyFilters, doAction } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';
import { FormRenderer, ServerHtml, afterSubmit, alertDialog, api, buildPayload, loginDialog, mergeFormInputs, submitAllowed, useForm } from '@wpuf/frontend-kit';

export default function PostForm( { schema: boot, root } ) {
    const schema = applyFilters( 'wpuf.frontend.form.schema', boot );
    const form = useForm( schema );

    // Dokan vendor registration: shop name => shop URL slug and its availability check (frontend-form.js).
    useDokanShopUrl( schema, form.setValue, root );
    const [ guest, setGuest ] = useState( { guest_name: '', guest_email: '' } );
    const [ featured, setFeatured ] = useState( !! schema.featured?.checked );
    const [ deleted, setDeleted ] = useState( [] );
    const [ busy, setBusy ] = useState( false );
    const [ draftBusy, setDraftBusy ] = useState( false );
    const [ formError, setFormError ] = useState( '' );
    const [ extraValues, setExtraValues ] = useState( {} );
    const [ done, setDone ] = useState( '' );
    const [ draftId, setDraftId ] = useState( 0 );
    const [ saved, setSaved ] = useState( '' );
    const dirty = useRef( false );
    const initial = useRef( null );

    // Like the classic warningOnExit(): only a real change arms the exit warning (a focus or blur does not).
    useEffect( () => {
        const snapshot = JSON.stringify( form.values );

        if ( null === initial.current ) {
            initial.current = snapshot;

            return;
        }

        dirty.current = snapshot !== initial.current;
    }, [ form.values ] );

    // The exit warning of the classic form.
    useEffect( () => {
        const warn = ( event ) => {
            if ( dirty.current && ! busy && ! done ) {
                event.preventDefault();
                event.returnValue = '';
            }
        };

        window.addEventListener( 'beforeunload', warn );

        return () => window.removeEventListener( 'beforeunload', warn );
    }, [ busy, done ] );

    useEffect( () => {
        doAction( 'wpuf.frontend.form.mounted', schema, root );

        // The classic "form loaded" events: scripts of server-rendered fields
        // (pricing, custom multiselect, repeat, social login) initialise on them.
        const timer = window.setTimeout( () => {
            if ( window.jQuery ) {
                window.jQuery( document ).trigger( 'wpuf_form_loaded', [ root ] ).trigger( 'wpuf-form-loaded', [ root ] );
            }
        }, 0 );

        return () => window.clearTimeout( timer );
    }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

    const setExtra = ( key, value ) => setExtraValues( ( current ) => ( { ...current, [ key ]: value } ) );

    const extras = () => {
        const out = { ...guest, ...extraValues };

        if ( schema.featured?.enabled ) {
            out.is_featured_item = featured ? '1' : '';
        }

        if ( deleted.length ) {
            out.delete_attachments = deleted;
        }

        form.all.forEach( ( field ) => {
            if ( 'recaptcha' === field.template && form.values[ field.name ] ) {
                out[ 'g-recaptcha-response' ] = form.values[ field.name ];
            }

            if ( 'cloudflare_turnstile' === field.template && form.values[ field.name ] ) {
                out[ 'cf-turnstile-response' ] = form.values[ field.name ];
            }
        } );

        return out;
    };

    const payload = ( withDraft = false ) => {
        // Inputs printed by hooks and server-rendered fields post too, as the classic serialize() posted them.
        const body = mergeFormInputs( buildPayload( schema, form.values, form.visible, form.all, { ...( schema.extra_fields || {} ), ...extras() } ), root.querySelector( 'form.wpuf-form-add' ) );

        if ( withDraft && draftId ) {
            body.post_id = draftId;
        }

        return body;
    };

    // The classic failed-submit popup (SweetAlert warning with the server message).
    const showError = ( e ) => {
        if ( e.data && e.data.errors && 'object' === typeof e.data.errors ) {
            form.setErrors( ( current ) => ( { ...current, ...e.data.errors } ) );
        }

        if ( window.grecaptcha && 'function' === typeof window.grecaptcha.reset && root.querySelector( '.g-recaptcha' ) ) {
            window.grecaptcha.reset();
        }

        alertDialog( { html: e.message || __( 'Something went wrong', 'wp-user-frontend' ) } );
    };

    const onSubmit = async () => {
        setFormError( '' );

        // Like the classic form: the fields on screen are checked (the hidden steps are the server's).
        const errors = form.validate( form.onScreen( !! schema.layout?.multistep ) );

        if ( schema.guest?.fields ) {
            if ( ! guest.guest_name.trim() ) {
                /* translators: %s: field label */
                errors.guest_name = sprintf( __( '%s is required', 'wp-user-frontend' ), schema.guest.name_label );
            }

            if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( guest.guest_email ) ) {
                /* translators: %s: field label */
                errors.guest_email = sprintf( __( '%s is not valid', 'wp-user-frontend' ), schema.guest.email_label );
            }

            form.setErrors( ( current ) => ( { ...current, ...errors } ) );
        }

        if ( Object.keys( errors ).length ) {
            setFormError( __( 'Please fix the errors to proceed', 'wp-user-frontend' ) );
            window.setTimeout( () => {
                const first = root.querySelector( '.has-error input, .has-error select, .has-error textarea, .has-error [tabindex="0"]' );

                if ( first ) {
                    first.focus();
                }
            }, 0 );

            return;
        }

        if ( ! applyFilters( 'wpuf.frontend.form.beforeSubmit', true, schema, form.values ) ) {
            return;
        }

        setBusy( true );

        try {
            const body = payload( true );
            let response;

            if ( 'profile' === schema.form_type ) {
                // Pro registration / profile forms (wpuf/v1/registration/*).
                response = 'profile' === schema.mode
                    ? await api.updateRegistration( schema.id, body )
                    : await api.submitRegistration( schema.id, body );
            } else {
                response = body.post_id && ( 'edit' === schema.mode || draftId )
                    ? await api.updateForm( schema.id, body.post_id, body )
                    : await api.submitForm( schema.id, body );
            }

            dirty.current = false;
            afterSubmit( response, schema, ( message ) => {
                setDone( message );
                window.setTimeout( () => root.scrollIntoView( { behavior: 'smooth', block: 'start' } ), 0 );
            } );
        } catch ( e ) {
            if ( e.data && 'login' === e.data.type && e.data.redirect_to ) {
                if ( await loginDialog( { text: e.message } ) ) {
                    window.location.assign( e.data.redirect_to );
                }
            } else {
                showError( e );
            }
        } finally {
            setBusy( false );
        }
    };

    const onDraft = async () => {
        setDraftBusy( true );

        try {
            const response = await api.saveDraft( schema.id, payload( true ) );

            setDraftId( response.post_id );
            setDone( '' );
            doAction( 'wpuf.frontend.form.draftSaved', response, schema );
            setSaved( response.message || __( 'Post Saved', 'wp-user-frontend' ) );
        } catch ( e ) {
            showError( e );
        } finally {
            setDraftBusy( false );
        }
    };

    useEffect( () => {
        if ( ! saved ) {
            return undefined;
        }

        const timer = window.setTimeout( () => setSaved( '' ), 2500 );

        return () => window.clearTimeout( timer );
    }, [ saved ] );

    // Like the classic field: a visible v2 reCAPTCHA keeps the submit disabled until it is solved.
    const captchaSolved = ! schema.needs?.recaptcha_key || form.all.every( ( field ) => 'recaptcha' !== field.template || 'invisible_recaptcha' === field.recaptcha_type || ! form.visible.has( field.name ) || !! form.values[ field.name ] );

    // The classic success box in place of the form (`div.wpuf-success`).
    if ( done ) {
        return <ServerHtml html={ done } className="wpuf-success" slug="success" />;
    }

    if ( ! schema.state?.open ) {
        return <ServerHtml html={ schema.state?.message || '' } className="wpuf-message" />;
    }

    return (
        <>
            { saved && <span className="wpuf-draft-saved" role="status">{ saved }</span> }
            <FormRenderer
                schema={ schema }
                form={ form }
                guest={ guest }
                setGuest={ setGuest }
                featured={ featured }
                setFeatured={ setFeatured }
                onSubmit={ onSubmit }
                onDraft={ onDraft }
                onDeleteStored={ ( id ) => setDeleted( ( current ) => [ ...current, id ] ) }
                extras={ extraValues }
                setExtra={ setExtra }
                formError={ formError }
                busy={ busy }
                draftBusy={ draftBusy }
                submitAllowed={ submitAllowed( schema.settings, form.values ) && captchaSolved }
            />
        </>
    );
}
