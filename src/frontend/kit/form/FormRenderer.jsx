/**
 * A form from its schema, in the classic markup of Frontend_Render_Form /
 * Profile_Form: `form.wpuf-form-add.wpuf-form-<layout>[.wpuf-style|.wpuf-theme-style]
 * > ul.wpuf-form.form-label-<pos>`, the server slots (printing hooks) in
 * their classic places, guest fields, the featured checkbox, the fields
 * through the registry under conditional logic, Pro's multistep fieldsets
 * with the classic progress bar, and the submit row (`input.wpuf-submit-button`,
 * `a#wpuf-post-draft`, `span.wpuf-loading`, `div.wpuf-errors`, the
 * registration login line). The parent owns the state (useForm) and the
 * submit.
 *
 * @since WPUF_SINCE
 */
import { Fragment, useCallback, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import Field from '../components/Field';
import ServerHtml from '../components/ServerHtml';
import { cx } from '../components/primitives';
import { fieldComponent } from '../fields';
import { fieldKey } from './conditions';

/** Pro's multistep header slot prints an empty `div.wpuf-multistep-progressbar` for the classic script; React renders its own. */
const withoutProgressHost = ( html ) => String( html || '' ).replace( /<div class="wpuf-multistep-progressbar"><\/div>/g, '' );

/** The classic progress bar of a multistep form (frontend-form.js enableMultistep). */
function Progress( { type, steps, step } ) {
    const total = steps.length;

    if ( 'progressive' === type ) {
        const percent = Math.round( ( ( step + 1 ) * 100 ) / total );
        // frontend-form.js rewrites the header to "Step N of M" on every step (the legend only flashes first).
        /* translators: 1: current step, 2: total steps */
        const text = sprintf( __( 'Step %1$d of %2$d', 'wp-user-frontend' ), step + 1, total );

        return (
            <div className="wpuf-multistep-progressbar" role="progressbar" aria-valuemin={ 0 } aria-valuemax={ 100 } aria-valuenow={ percent } aria-label={ text }>
                <div className="wpuf-progressbar-header">
                    <span className="wpuf-progressbar-step-text">{ text }</span>
                    <span className="wpuf-progressbar-percent">{ percent }%</span>
                </div>
                <div className="wpuf-progressbar-track">
                    <div className="wpuf-progressbar-fill" style={ { width: `${ percent }%` } } />
                </div>
            </div>
        );
    }

    return (
        <div className="wpuf-multistep-progressbar wizard-steps">
            <div className="wpuf-step-wizard">
                { steps.map( ( item, index ) => (
                    <Fragment key={ index }>
                        <div className={ cx( 'wpuf-step-item', index === step && 'active-step', index < step && 'completed-step' ) } data-step={ index } aria-current={ index === step ? 'step' : undefined }>
                            <div className="wpuf-step-circle">{ index + 1 }</div>
                            { /* translators: %d: step number */ }
                            <div className="wpuf-step-label">{ item.title || sprintf( __( 'Step %d', 'wp-user-frontend' ), index + 1 ) }</div>
                        </div>
                        { index < steps.length - 1 && <div className={ cx( 'wpuf-step-line', index < step && 'wpuf-step-line-active' ) } data-after-step={ index } /> }
                    </Fragment>
                ) ) }
            </div>
        </div>
    );
}

export default function FormRenderer( {
    schema,
    form,
    guest,
    setGuest,
    featured,
    setFeatured,
    extras = {},
    setExtra,
    onSubmit,
    onDraft,
    onDeleteStored,
    busy,
    draftBusy,
    formError = '',
    submitAllowed = true,
    submitHidden = false,
} ) {
    const { values, setValue, errors, touch, visible, steps, step, setStep, validate } = form;
    const [ stepError, setStepError ] = useState( '' );
    // Uploads in flight, by field: the submit stays disabled meanwhile, as upload.js does.
    const [ uploading, setUploadingState ] = useState( {} );
    const setUploading = useCallback( ( name, active ) => setUploadingState( ( current ) => {
        if ( !! current[ name ] === !! active ) {
            return current;
        }

        const next = { ...current };

        if ( active ) {
            next[ name ] = true;
        } else {
            delete next[ name ];
        }

        return next;
    } ), [] );
    const uploadBusy = Object.keys( uploading ).length > 0;
    const formRef = useRef( null );
    const slots = schema.slots || {};
    const layout = schema.layout || {};
    const layoutName = layout.layout || 'layout1';
    const multistep = !! layout.multistep && steps.list.length > 0;
    const profile = 'profile' === schema.form_type;

    // `index` is set for the form's own rows (list .map), not for column cells.
    const renderField = ( field, index ) => {
        if ( ! field ) {
            return null;
        }

        if ( ! visible.has( fieldKey( field ) ) ) {
            // The classic form keeps a hidden row in place (display: none), so the
            // `li:last-of-type` spacing rules of the template sheets see the same
            // list; the row carries no input, so nothing of it posts.
            return 'number' === typeof index && field.name ? (
                <li key={ field.id || fieldKey( field ) } className={ cx( 'wpuf-el', field.row_class || field.name, field.css, field.width && `field-size-${ field.width }` ) } data-label={ field.label } style={ { display: 'none' } } aria-hidden="true" />
            ) : null;
        }

        const Component = fieldComponent( field.template );

        return (
            <Component
                key={ field.id || fieldKey( field ) }
                field={ field }
                formId={ schema.id }
                schema={ schema }
                value={ field.name ? values[ field.name ] : undefined }
                error={ field.name ? errors[ field.name ] : undefined }
                onChange={ ( value ) => field.name && setValue( field.name, value ) }
                onBlur={ () => field.name && touch( field.name ) }
                renderField={ renderField }
                onDeleteStored={ onDeleteStored }
                extras={ extras }
                setExtra={ setExtra }
                values={ values }
                setFieldValue={ setValue }
                setUploading={ setUploading }
            />
        );
    };

    const scrollToForm = ( event ) => {
        const target = event && event.currentTarget ? event.currentTarget.closest( 'form' ) : null;

        if ( target ) {
            window.scrollTo( { top: target.getBoundingClientRect().top + window.scrollY - 32, behavior: 'smooth' } );
        }
    };

    // frontend-form.js formStepCheck(): the current fieldset is checked; a failure adds the
    // "Please fix the errors" notice to that fieldset and focuses the first broken field.
    const go = ( event, direction ) => {
        event.preventDefault();

        if ( direction > 0 && Object.keys( validate( steps.list[ step ].fields ) ).length ) {
            setStepError( __( 'Please fix the errors to proceed', 'wp-user-frontend' ) );
            window.setTimeout( () => {
                const first = formRef.current && formRef.current.querySelector( 'fieldset.field-active .has-error input, fieldset.field-active .has-error select, fieldset.field-active .has-error textarea' );

                if ( first ) {
                    first.focus();
                }
            }, 0 );

            return;
        }

        setStepError( '' );
        setStep( ( current ) => Math.max( 0, Math.min( steps.list.length - 1, current + direction ) ) );
        scrollToForm( event );
    };

    const submitLabel = 'edit' === schema.mode || 'profile' === schema.mode ? layout.update_text : layout.submit_text;
    const loginUrl = 'registration' === schema.mode && schema.links ? schema.links.login : '';
    // The classic form class, computed by the server (Frontend_Render_Form / Profile_Form::profile_edit).
    const styleClass = layout.form_class || '';

    const submitRow = (
    <li className="wpuf-submit" style={ submitHidden ? { display: 'none' } : undefined }>
        { ! profile && <div className="wpuf-label">&nbsp;</div> }
        { slots.submit && <ServerHtml html={ slots.submit } slug="submit" className="wpuf-slot-inline" /> }
        <input
            type="submit"
            className={ cx( 'wpuf-submit-button', ! profile && `wpuf_submit_${ schema.id }`, busy && 'is-busy' ) }
            aria-busy={ busy ? 'true' : undefined }
            name="submit"
            value={ submitLabel }
            // Busy: dimmed in its own colour (aria-disabled, the submit handler ignores
            // repeats); the layouts' grey :disabled look stays for "cannot submit yet".
            aria-disabled={ busy ? 'true' : undefined }
            disabled={ uploadBusy || ! submitAllowed }
        />
        { layout.draft && onDraft && (
            <a
                href="#draft"
                className="btn"
                style={ { marginLeft: '0.5rem' } }
                id="wpuf-post-draft"
                aria-busy={ draftBusy ? 'true' : undefined }
                onClick={ ( event ) => {
                    event.preventDefault();

                    if ( ! draftBusy ) {
                        onDraft();
                    }
                } }
            >
                { __( 'Save Draft', 'wp-user-frontend' ) }
            </a>
        ) }
        { draftBusy && <span className="wpuf-loading" role="status" aria-label={ __( 'Saving…', 'wp-user-frontend' ) } /> }
        { busy && <span className="wpuf-loading" role="status" aria-label={ __( 'Submitting…', 'wp-user-frontend' ) } /> }
        { loginUrl && (
            <>
                <br />
                <br />
                { __( 'Already have an account?', 'wp-user-frontend' ) }{ ' ' }
                <a href={ loginUrl }>{ __( 'Login', 'wp-user-frontend' ) }</a>
            </>
        ) }
        { formError && <div className="wpuf-errors" role="alert">{ formError }</div> }
    </li>
    );

    const formMarkup = (
        <>
            { slots.before_form && <ServerHtml html={ slots.before_form } slug="before_form" /> }
            <form
                ref={ formRef }
                className={ cx( 'wpuf-form-add', `wpuf-form-${ layoutName }`, styleClass ) }
                action=""
                method="post"
                onSubmit={ ( event ) => {
                    event.preventDefault();

                    if ( ! busy ) {
                        onSubmit();
                    }
                } }
            >
                { layout.show_title && schema.title && <h2 className="wpuf-form-title">{ schema.title }</h2> }
                { layout.description && <ServerHtml html={ layout.description } className="wpuf-form-description" /> }

                <ul className={ cx( 'wpuf-form', `form-label-${ layout.label_position || 'left' }` ) }>
                    { /* The classic list opens with its init <script>, so no field row is :first-child; the template sheets rely on that. */ }
                    <li className="wpuf-form-anchor" hidden aria-hidden="true" style={ { display: 'none' } } />
                    { slots.fields_top && <ServerHtml html={ multistep ? withoutProgressHost( slots.fields_top ) : slots.fields_top } slug="fields_top" className="wpuf-slot-inline" /> }
                    { multistep && <Progress type={ layout.progress } steps={ steps.list } step={ step } /> }
                    { slots.form_top && <ServerHtml html={ slots.form_top } slug="form_top" className="wpuf-slot-inline" /> }

                    { schema.guest?.fields && (
                        <>
                            <Field field={ { name: 'guest_name', label: schema.guest.name_label, required: 'yes' } } id={ `guest_name_${ schema.id }` } error={ errors.guest_name } className="el-name" plain>
                                <input id={ `guest_name_${ schema.id }` } type="text" required data-required="yes" data-type="text" name="guest_name" value={ guest.guest_name || '' } size={ 40 } onChange={ ( event ) => setGuest( { ...guest, guest_name: event.target.value } ) } />
                            </Field>
                            <Field field={ { name: 'guest_email', label: schema.guest.email_label, required: 'yes' } } id={ `guest_email_${ schema.id }` } error={ errors.guest_email } className="el-email" plain>
                                <input id={ `guest_email_${ schema.id }` } type="email" required data-required="yes" data-type="email" name="guest_email" value={ guest.guest_email || '' } size={ 40 } onChange={ ( event ) => setGuest( { ...guest, guest_email: event.target.value } ) } />
                            </Field>
                        </>
                    ) }

                    { schema.featured?.enabled && (
                        <li className="wpuf-el field-size-large" data-label={ __( 'Is featured', 'wp-user-frontend' ) }>
                            <div className="wpuf-label">
                                <label htmlFor={ `wpuf_is_featured_${ schema.id }` }>{ __( 'Featured', 'wp-user-frontend' ) }</label>
                            </div>
                            <div className="wpuf-fields">
                                <label className="wpuf-checkbox-block">
                                    <input id={ `wpuf_is_featured_${ schema.id }` } type="checkbox" className="wpuf_is_featured" name="is_featured_item" value="1" checked={ !! featured } onChange={ ( event ) => setFeatured( event.target.checked ) } />
                                    { ' ' }
                                    <span className="wpuf-message-box">
                                        { /* translators: 1: post type, 2: remaining featured items */ }
                                        { sprintf( __( 'Mark the %1$s as featured (remaining %2$d)', 'wp-user-frontend' ), schema.featured.post_type, Math.max( 0, schema.featured.remaining - ( featured && ! schema.featured.checked ? 1 : 0 ) ) ) }
                                    </span>
                                </label>
                            </div>
                        </li>
                    ) }

                    { ( multistep ? steps.pre : schema.fields || [] ).map( renderField ) }

                    { multistep && steps.list.map( ( item, index ) => (
                        <fieldset key={ index } className={ cx( 'wpuf-multistep-fieldset', index === step && 'field-active' ) } style={ index === step ? undefined : { display: 'none' } }>
                            <legend style={ { display: 'none' } }>{ item.title }</legend>
                            <div className="multistep-button-area">
                                { index > 0 && (
                                    <button type="button" className="wpuf-multistep-prev-btn btn wpuf-multistep-nav-btn" onClick={ ( event ) => go( event, -1 ) }>
                                        { item.prev || __( 'Previous', 'wp-user-frontend' ) }
                                    </button>
                                ) }
                                { index < steps.list.length - 1 && (
                                    <button type="button" className="wpuf-multistep-next-btn btn wpuf-multistep-nav-btn" onClick={ ( event ) => go( event, 1 ) }>
                                        { item.next || __( 'Next', 'wp-user-frontend' ) }
                                    </button>
                                ) }
                            </div>
                            { item.fields.map( renderField ) }
                            { /* The classic submit row sits in the last fieldset (shown on the last step only). */ }
                            { index === steps.list.length - 1 && submitRow }
                            { index === step && stepError && <div className="wpuf-errors">{ stepError }</div> }
                        </fieldset>
                    ) ) }

                    { ! multistep && submitRow }

                    { /* Profile_Form::action_links(): always an <li> for a visitor, empty when there are no links. */ }
                    { 'registration' === schema.mode ? <ServerHtml as="li" html={ slots.action_links || '' } slug="action_links" /> : null }
                    { slots.form_bottom && <ServerHtml html={ slots.form_bottom } slug="form_bottom" className="wpuf-slot-inline" /> }
                </ul>
            </form>
            { slots.after_form && <ServerHtml html={ slots.after_form } slug="after_form" /> }
        </>
    );

    // Pro's profile forms sit in the template card (reg-template-*.css styles it per layout).
    return layout.card ? <div className={ `wpuf-reg-form-card wpuf-reg-form-card-${ layoutName }` }>{ formMarkup }</div> : formMarkup;
}
