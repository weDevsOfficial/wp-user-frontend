/**
 * Newsletter card: the same weMail embed form as the classic page (posted
 * straight to weMail), opened in a new tab so the admin stays on Help.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextInput, notify } from '@wpuf/components';
import { Mail } from 'lucide-react';

const EMAIL = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

/**
 * @param {Object} props
 * @param {Object} props.newsletter { action, tag, firstName, email }.
 */
export default function HelpNewsletter( { newsletter } ) {
    const form = useRef( null );
    const [ firstName, setFirstName ] = useState( newsletter.firstName || '' );
    const [ email, setEmail ] = useState( newsletter.email || '' );

    const submit = ( event ) => {
        event.preventDefault();

        if ( ! firstName.trim() ) {
            notify( __( 'Please enter your first name', 'wp-user-frontend' ), 'error' );

            return;
        }

        if ( ! EMAIL.test( email ) ) {
            notify( __( 'Please enter a valid email address', 'wp-user-frontend' ), 'error' );

            return;
        }

        form.current.submit();
    };

    if ( ! newsletter.action ) {
        return null;
    }

    return (
        <section className="wpuf-help-newsletter mt-6 flex flex-wrap items-end justify-between gap-6 rounded-[10px] border border-solid border-emerald-100 bg-emerald-50/60 p-6">
            <div className="flex max-w-md items-start gap-4">
                <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-primary shadow-sm">
                    <Mail size={ 20 } aria-hidden="true" />
                </span>
                <div>
                <h2 className="m-0 text-base font-semibold text-gray-900">{ __( 'Subscribe to Our Newsletter', 'wp-user-frontend' ) }</h2>
                <p className="m-0 mt-1 text-sm text-gray-600">{ __( 'Regular tips, offers and news updates.', 'wp-user-frontend' ) }</p>
                </div>
            </div>
            <form ref={ form } method="post" action={ newsletter.action } target="_blank" onSubmit={ submit } className="flex flex-wrap items-end gap-3">
                <label className="block">
                    <span className="mb-1 block text-xs font-medium text-gray-700">{ __( 'First Name', 'wp-user-frontend' ) } <span className="text-red-500" aria-hidden="true">*</span></span>
                    <TextInput name="first_name" value={ firstName } onChange={ setFirstName } placeholder={ __( 'Enter first name', 'wp-user-frontend' ) } required className="w-48 bg-white" />
                </label>
                <label className="block">
                    <span className="mb-1 block text-xs font-medium text-gray-700">{ __( 'Email', 'wp-user-frontend' ) } <span className="text-red-500" aria-hidden="true">*</span></span>
                    <TextInput type="email" name="email" value={ email } onChange={ setEmail } placeholder={ __( 'Enter email', 'wp-user-frontend' ) } required className="w-64 bg-white" />
                </label>
                <input type="hidden" name="tag" value={ newsletter.tag } />
                <Button type="submit">{ __( 'Subscribe', 'wp-user-frontend' ) }</Button>
            </form>
        </section>
    );
}
