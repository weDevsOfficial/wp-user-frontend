/**
 * Free Edit profile (first name, last name, email, optional password
 * change) and Change password, over `PUT /account/profile` and
 * `PUT /account/password`: the same rules and messages as the classic AJAX
 * actions, inline per-field errors from the server's `data.field`.
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, Notice, api, icons } from '@wpuf/frontend-kit';

const { Eye, EyeOff } = icons;

function Input( { id, label, type = 'text', value, onChange, error, required = false, autoComplete } ) {
    const [ shown, setShown ] = useState( false );
    const isPassword = 'password' === type;

    return (
        <div className={ 'wpuf-form-group' + ( error ? ' has-error' : '' ) }>
            <label htmlFor={ id } className="wpuf-form-label">
                { label }
                { required && <span className="required"> *</span> }
            </label>
            <div className="wpuf-form-control">
                <input
                    id={ id }
                    className="wpuf-form-input"
                    type={ isPassword && shown ? 'text' : type }
                    value={ value }
                    required={ required }
                    autoComplete={ autoComplete }
                    aria-invalid={ error ? 'true' : undefined }
                    aria-describedby={ error ? id + '_error' : undefined }
                    onChange={ ( event ) => onChange( event.target.value ) }
                />
                { isPassword && (
                    <button type="button" className="wpuf-form-eye" aria-label={ shown ? __( 'Hide password', 'wp-user-frontend' ) : __( 'Show password', 'wp-user-frontend' ) } onClick={ () => setShown( ( s ) => ! s ) }>
                        { shown ? <EyeOff size={ 16 } aria-hidden="true" /> : <Eye size={ 16 } aria-hidden="true" /> }
                    </button>
                ) }
            </div>
            { error && <span id={ id + '_error' } className="wpuf-error-msg" role="alert">{ error }</span> }
        </div>
    );
}

/** WordPress' strength levels (password-strength-meter): class and `pwsL10n` key. */
const LEVELS = {
    0: [ 'short', 'short' ],
    1: [ 'short', 'short' ],
    2: [ 'bad', 'bad' ],
    3: [ 'good', 'good' ],
    4: [ 'strong', 'strong' ],
    5: [ 'mismatch', 'mismatch' ],
};

/**
 * The classic Change Password strength indicator: WordPress' meter
 * (`wp.passwordStrength`, zxcvbn) and its labels, as a bar and a word.
 * Nothing when the meter is not on the page or no password is typed.
 *
 * @param {Object} props
 * @param {string} props.pass1 New password.
 * @param {string} props.pass2 Confirmation.
 */
function PasswordStrength( { pass1, pass2 } ) {
    const meter = window.wp && window.wp.passwordStrength;

    if ( ! pass1 || ! meter || 'function' !== typeof meter.meter ) {
        return null;
    }

    const disallowed = 'function' === typeof meter.userInputDisallowedList ? meter.userInputDisallowedList() : [];
    const score = meter.meter( pass1, disallowed, pass2 );
    const [ level, key ] = LEVELS[ score ] || LEVELS[ 0 ];
    const labels = window.pwsL10n || {};
    const words = {
        short: __( 'Very weak', 'wp-user-frontend' ),
        bad: __( 'Weak', 'wp-user-frontend' ),
        good: __( 'Medium', 'wp-user-frontend' ),
        strong: __( 'Strong', 'wp-user-frontend' ),
        mismatch: __( 'Mismatch', 'wp-user-frontend' ),
    };

    return (
        <div className={ `wpuf-password-strength is-${ level }` } aria-live="polite">
            <span className="wpuf-password-strength__bar" aria-hidden="true"><span /></span>
            <span className="wpuf-password-strength__label">{ labels[ key ] || words[ key ] }</span>
        </div>
    );
}

export function EditProfile( { profile, onSaved } ) {
    const [ values, setValues ] = useState( {
        first_name: profile.first_name || '',
        last_name: profile.last_name || '',
        email: profile.email || '',
        current_password: '',
        pass1: '',
        pass2: '',
    } );
    const [ errors, setErrors ] = useState( {} );
    const [ busy, setBusy ] = useState( false );
    const [ notice, setNotice ] = useState( null );

    const set = ( key ) => ( value ) => {
        setValues( ( current ) => ( { ...current, [ key ]: value } ) );
        setErrors( ( current ) => ( current[ key ] ? { ...current, [ key ]: '' } : current ) );
    };

    const submit = async ( event ) => {
        event.preventDefault();
        setNotice( null );
        setBusy( true );

        try {
            const result = await api.updateProfile( values );

            setNotice( { kind: 'success', text: __( 'Profile updated successfully!', 'wp-user-frontend' ) } );
            setValues( ( current ) => ( { ...current, current_password: '', pass1: '', pass2: '' } ) );
            onSaved && onSaved( result.profile );
        } catch ( e ) {
            if ( e.data?.field ) {
                setErrors( { [ e.data.field ]: e.message } );
            } else {
                setNotice( { kind: 'error', text: e.message } );
            }
        } finally {
            setBusy( false );
        }
    };

    return (
        <div className="wpuf-edit-profile-container">
            <header className="wpuf-section-head">
                <h2 className="wpuf-section-head__title">{ __( 'Account details', 'wp-user-frontend' ) }</h2>
                <p className="wpuf-section-head__lead">{ __( 'Update your personal information and change your password here.', 'wp-user-frontend' ) }</p>
            </header>
            { notice && <Notice kind={ notice.kind } onClose={ () => setNotice( null ) }>{ notice.text }</Notice> }
            <Card>
                <form className="wpuf-form wpuf-edit-profile-form wpuf-update-profile-form" onSubmit={ submit } noValidate>
                    <div className="wpuf-form-grid">
                        <Input id="first_name" label={ __( 'First Name', 'wp-user-frontend' ) } value={ values.first_name } onChange={ set( 'first_name' ) } error={ errors.first_name } required autoComplete="given-name" />
                        <Input id="last_name" label={ __( 'Last Name', 'wp-user-frontend' ) } value={ values.last_name } onChange={ set( 'last_name' ) } error={ errors.last_name } required autoComplete="family-name" />
                    </div>
                    <Input id="email" type="email" label={ __( 'Email Address', 'wp-user-frontend' ) } value={ values.email } onChange={ set( 'email' ) } error={ errors.email } required autoComplete="email" />

                    <fieldset className="wpuf-form-fieldset">
                        <legend>{ __( 'Change password', 'wp-user-frontend' ) }</legend>
                        <p className="wpuf-muted">{ __( 'Leave these empty to keep your current password.', 'wp-user-frontend' ) }</p>
                        <Input id="current_password" type="password" label={ __( 'Current Password', 'wp-user-frontend' ) } value={ values.current_password } onChange={ set( 'current_password' ) } error={ errors.current_password } autoComplete="current-password" />
                        <div className="wpuf-form-grid">
                            <Input id="pass1" type="password" label={ __( 'New Password', 'wp-user-frontend' ) } value={ values.pass1 } onChange={ set( 'pass1' ) } error={ errors.pass1 } autoComplete="new-password" />
                            <Input id="pass2" type="password" label={ __( 'Confirm New Password', 'wp-user-frontend' ) } value={ values.pass2 } onChange={ set( 'pass2' ) } error={ errors.pass2 } autoComplete="new-password" />
                        </div>
                        <PasswordStrength pass1={ values.pass1 } pass2={ values.pass2 } />
                    </fieldset>

                    <div className="wpuf-form-actions">
                        <Button type="submit" busy={ busy }>{ __( 'Update Profile', 'wp-user-frontend' ) }</Button>
                    </div>
                </form>
            </Card>
        </div>
    );
}

export function ChangePassword() {
    const [ values, setValues ] = useState( { current_password: '', pass1: '', pass2: '' } );
    const [ errors, setErrors ] = useState( {} );
    const [ busy, setBusy ] = useState( false );
    const [ notice, setNotice ] = useState( null );

    const set = ( key ) => ( value ) => {
        setValues( ( current ) => ( { ...current, [ key ]: value } ) );
        setErrors( ( current ) => ( current[ key ] ? { ...current, [ key ]: '' } : current ) );
    };

    const submit = async ( event ) => {
        event.preventDefault();
        setNotice( null );
        setBusy( true );

        try {
            const result = await api.updatePassword( values );

            setNotice( { kind: 'success', text: result.message || __( 'Password updated successfully!', 'wp-user-frontend' ) } );
            setValues( { current_password: '', pass1: '', pass2: '' } );
        } catch ( e ) {
            if ( e.data?.field ) {
                setErrors( { [ e.data.field ]: e.message } );
            } else {
                setNotice( { kind: 'error', text: e.message } );
            }
        } finally {
            setBusy( false );
        }
    };

    return (
        <div className="wpuf-change-password-container">
            <header className="wpuf-section-head">
                <h2 className="wpuf-section-head__title">{ __( 'Change Password', 'wp-user-frontend' ) }</h2>
                <p className="wpuf-section-head__lead">{ __( 'Choose a strong password you do not use anywhere else.', 'wp-user-frontend' ) }</p>
            </header>
            { notice && <Notice kind={ notice.kind } onClose={ () => setNotice( null ) }>{ notice.text }</Notice> }
            <Card>
                <form className="wpuf-form wpuf-change-password-form" onSubmit={ submit } noValidate>
                    <Input id="cp_current_password" type="password" label={ __( 'Current Password', 'wp-user-frontend' ) } value={ values.current_password } onChange={ set( 'current_password' ) } error={ errors.current_password } required autoComplete="current-password" />
                    <div className="wpuf-form-grid">
                        <Input id="cp_pass1" type="password" label={ __( 'New Password', 'wp-user-frontend' ) } value={ values.pass1 } onChange={ set( 'pass1' ) } error={ errors.pass1 } required autoComplete="new-password" />
                        <Input id="cp_pass2" type="password" label={ __( 'Confirm New Password', 'wp-user-frontend' ) } value={ values.pass2 } onChange={ set( 'pass2' ) } error={ errors.pass2 } required autoComplete="new-password" />
                    </div>
                    <PasswordStrength pass1={ values.pass1 } pass2={ values.pass2 } />
                    <div className="wpuf-form-actions">
                        <Button type="submit" busy={ busy }>{ __( 'Update Password', 'wp-user-frontend' ) }</Button>
                    </div>
                </form>
            </Card>
        </div>
    );
}
