/**
 * Static preview of one generated field (develop's per-type preview in
 * FormSuccessStage.vue). Nothing is editable here; text comes from the AI
 * response and is rendered as text.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

import { getColumnCount, getWPUFFieldType, normalizeOptions, previewKind } from '../lib/fields';
import { CaretIcon } from './icons';

const BOX = 'rounded-md border border-[#E3E5E8] bg-white px-3 py-2 text-sm leading-5';
const SELECT = 'wpuf-form-select w-full cursor-pointer rounded-md border border-[#E3E5E8] bg-white px-3 py-2 pr-10 text-sm leading-5 text-gray-700';

const PLACEHOLDERS = {
    text_field: 'Enter text...',
    text: 'Enter text...',
    email_address: 'Enter email address...',
    email: 'Enter email address...',
    website_url: 'Enter website URL...',
    url: 'Enter website URL...',
    textarea_field: 'Enter your message...',
    textarea: 'Enter your message...',
    dropdown_field: 'Select an option',
    select: 'Select an option',
    multiple_select: 'Select multiple options',
    radio_field: 'Select one option',
    radio: 'Select one option',
    checkbox_field: 'Select options',
    checkbox: 'Select options',
    image_upload: 'Upload image files',
    file: 'Upload files',
    featured_image: 'Upload featured image',
    custom_hidden_field: 'Hidden field value',
    user_login: 'Enter username...',
    user_email: 'Enter email address...',
    user_url: 'Enter website URL...',
    first_name: 'Enter first name...',
    last_name: 'Enter last name...',
    nickname: 'Enter nickname...',
    display_name: 'Enter display name...',
    user_bio: 'Enter biography...',
    biography: 'Enter biography...',
    password: 'Enter password...',
    avatar: 'Upload profile picture',
    profile_photo: 'Upload profile photo',
    facebook_url: 'Enter Facebook URL...',
    twitter_url: 'Enter Twitter/X URL...',
    instagram_url: 'Enter Instagram URL...',
    linkedin_url: 'Enter LinkedIn URL...',
    address_field: 'Enter full address...',
    country_list_field: 'Select country',
    date_field: 'Select date',
    time_field: 'Select time',
    phone_field: 'Enter phone number',
    numeric_text_field: 'Enter number',
    file_upload: 'Upload files (Pro)',
    google_map: 'Click to set location',
    embed: 'Embed content will appear here',
    qr_code: 'QR code will be generated',
    ratings: 'Rate from 1 to 5 stars',
    linear_scale: 'Select from 1 to 10',
    checkbox_grid: 'Select checkboxes in grid',
    multiple_choice_grid: 'Select radio options in grid',
    repeat_field: 'Repeatable field group',
    really_simple_captcha: 'Enter captcha code',
    math_captcha: 'Solve math problem',
    shortcode: 'Shortcode output',
    action_hook: 'Custom hook execution',
    toc: 'Accept terms and conditions',
    post_title: 'Enter post title',
    post_content: 'Enter post content',
    post_excerpt: 'Enter post excerpt',
    post_tags: 'Enter tags (comma separated)',
    taxonomy: 'Select categories',
    section_break: 'Section break',
    column_field: 'Column layout',
    step_start: 'Multi-step form section',
    custom_html: 'Custom HTML content',
    date: 'Select date',
    time: 'Select time',
    datetime: 'Select date and time',
};

/**
 * Develop's placeholder text for a field type.
 *
 * @param {string} type Type.
 *
 * @return {string} Placeholder.
 */
function fieldPlaceholder( type ) {
    return PLACEHOLDERS[ type ] || 'Enter value...';
}

/**
 * Price of an option.
 *
 * @param {Object} field Field.
 * @param {string} value Option value.
 *
 * @return {string} Price.
 */
const priceOf = ( field, value ) => ( field.prices && field.prices[ value ] ? field.prices[ value ] : '0' );

/**
 * Radio / checkbox option rows.
 *
 * @param {Object}  props
 * @param {Object}  props.field    Field.
 * @param {string}  props.type     radio|checkbox
 * @param {boolean} [props.priced] Show prices (pricing fields).
 */
function ChoiceList( { field, type, priced = false } ) {
    const options = normalizeOptions( field.options );

    if ( ! options.length ) {
        return (
            <div className="text-sm leading-5 text-gray-400">
                { priced ? __( 'No pricing options configured', 'wp-user-frontend' ) : __( 'No options configured', 'wp-user-frontend' ) }
            </div>
        );
    }

    return options.map( ( option ) => (
        <div key={ option.value } className={ priced ? 'flex items-center justify-between rounded-md border border-[#E3E5E8] bg-white px-3 py-2' : 'flex items-center gap-2' }>
            <span className="flex items-center gap-2">
                <input type={ type } name={ `field_${ field.id }` } value={ option.value } disabled className="text-primary" />
                <span className="text-sm leading-5 text-gray-700">{ String( option.label ) }</span>
            </span>
            { priced && <span className="text-sm font-medium text-primary">{ priceOf( field, option.value ) }</span> }
        </div>
    ) );
}

/**
 * The preview body of a field kind.
 *
 * @param {Object} props
 * @param {Object} props.field Field.
 */
function PreviewBody( { field } ) {
    const kind = previewKind( field );
    const type = getWPUFFieldType( field );
    const options = normalizeOptions( field.options );
    const boxed = field.required ? 'rounded-md border p-3' : '';

    switch ( kind ) {
        case 'text':
            return <div className={ BOX }><span className="text-gray-400">{ field.placeholder || field.help || 'Enter text' }</span></div>;

        case 'select':
            return (
                <div className="relative">
                    <select className={ SELECT } aria-label={ field.label }>
                        <option value="">{ field.first || field.placeholder || __( 'Select an option', 'wp-user-frontend' ) }</option>
                        { options.map( ( option ) => <option key={ option.value } value={ option.value }>{ String( option.label ) }</option> ) }
                    </select>
                    <CaretIcon />
                </div>
            );

        case 'radio':
            return <div className={ `flex flex-col gap-2 ${ boxed }` }><ChoiceList field={ field } type="radio" /></div>;

        case 'checkbox':
            return <div className={ `flex flex-col gap-2 ${ boxed }` }><ChoiceList field={ field } type="checkbox" /></div>;

        case 'pricing_radio':
            return <div className="flex flex-col gap-2"><ChoiceList field={ field } type="radio" priced /></div>;

        case 'pricing_checkbox':
            return <div className="flex flex-col gap-2"><ChoiceList field={ field } type="checkbox" priced /></div>;

        case 'pricing_dropdown':
            return (
                <div className="relative">
                    <select disabled className={ SELECT } aria-label={ field.label }>
                        <option value="">{ field.first || __( '- Select -', 'wp-user-frontend' ) }</option>
                        { options.length
                            ? options.map( ( option ) => <option key={ option.value } value={ option.value }>{ `${ option.label } - ${ priceOf( field, option.value ) }` }</option> )
                            : <option disabled>{ __( 'No pricing options configured', 'wp-user-frontend' ) }</option> }
                    </select>
                    <CaretIcon />
                </div>
            );

        case 'pricing_multiselect':
            return (
                <select multiple disabled className="wpuf-form-multiselect min-h-24 w-full rounded-md border border-[#E3E5E8] bg-white px-3 py-2 text-sm leading-5" aria-label={ field.label }>
                    { options.length
                        ? options.map( ( option ) => <option key={ option.value } value={ option.value } className="px-3 py-2">{ `${ option.label } - ${ priceOf( field, option.value ) }` }</option> )
                        : <option disabled>{ __( 'No pricing options configured', 'wp-user-frontend' ) }</option> }
                </select>
            );

        case 'cart_total':
            return (
                <div className="rounded-md border-2 border-emerald-200 bg-emerald-50 p-4">
                    <div className="flex items-center justify-between">
                        <span className="text-base font-semibold text-gray-800">{ field.label || __( 'Total', 'wp-user-frontend' ) }</span>
                        <span className="text-2xl font-bold text-primary">0.00</span>
                    </div>
                </div>
            );

        case 'toc':
            return (
                <div className="rounded-md border border-[#E3E5E8] bg-white p-4">
                    { field.toc_text && <div className="mb-3 text-sm leading-5 text-gray-600">{ field.toc_text }</div> }
                    <div className="flex items-start gap-2">
                        <input type="checkbox" disabled className="mt-1 text-primary" />
                        <span className="text-sm font-medium leading-5 text-gray-700">{ field.description || 'I agree to the terms and conditions' }</span>
                    </div>
                </div>
            );

        case 'file':
            return (
                <div className="flex flex-col items-center gap-2 rounded-md border-2 border-dashed border-[#E3E5E8] bg-white p-5 text-center text-sm leading-5 text-gray-500">
                    <svg className="size-8 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 4V16M12 4L8 8M12 4L16 8M4 17V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V17" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    <div>
                        <strong>{ field.button_label || __( 'Select Image', 'wp-user-frontend' ) }</strong>
                        <div className="text-gray-400">{ 'image_upload' === type ? 'Upload image files' : 'Drop files here or click to upload' }</div>
                    </div>
                </div>
            );

        case 'textarea':
            return <div className={ `${ BOX } relative min-h-[100px]` }><span className="text-gray-400">{ field.placeholder || field.help || __( 'Enter your text here...', 'wp-user-frontend' ) }</span></div>;

        case 'multiselect':
            return (
                <select multiple className="wpuf-form-multiselect min-h-24 w-full rounded-md border border-[#E3E5E8] bg-white px-3 py-2 text-sm leading-5 text-gray-700" aria-label={ field.label }>
                    { options.length
                        ? options.map( ( option ) => <option key={ option.value } value={ option.value } className="px-3 py-2">{ String( option.label ) }</option> )
                        : <option disabled>{ field.placeholder || fieldPlaceholder( type ) }</option> }
                </select>
            );

        case 'date':
            return (
                <div className={ `${ BOX } flex items-center justify-between` }>
                    <span className="text-gray-400">{ field.placeholder || 'Select date' }</span>
                    <svg className="size-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            );

        case 'rating':
            return (
                <div className={ `${ BOX } flex items-center gap-2` }>
                    { 'ratings' === field.type
                        ? [ 1, 2, 3, 4, 5 ].map( ( star ) => <span key={ star } className="text-lg text-gray-300">★</span> )
                        : (
                            <>
                                <span className="text-gray-400">1</span>
                                <div className="h-2 flex-1 rounded-md bg-gray-200" />
                                <span className="text-gray-400">10</span>
                            </>
                        ) }
                </div>
            );

        case 'grid': {
            const input = 'checkbox_grid' === field.type ? 'checkbox' : 'radio';

            return (
                <div className="rounded-md border border-[#E3E5E8] bg-white p-4 text-sm leading-5">
                    <div className="mb-3 flex items-center gap-4">
                        <div className="flex-1" />
                        { [ 1, 2, 3 ].map( ( column ) => (
                            <div key={ column } className="text-xs text-gray-500">{ __( 'Option', 'wp-user-frontend' ) } { column }</div>
                        ) ) }
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex-1 text-gray-600">{ __( 'Row 1', 'wp-user-frontend' ) }</div>
                        { [ 1, 2, 3 ].map( ( column ) => <input key={ column } type={ input } disabled className="text-primary" /> ) }
                    </div>
                </div>
            );
        }

        case 'special':
            return (
                <div className="rounded-md border border-[#E3E5E8] bg-white p-5 text-center">
                    <div className="mb-2 text-gray-400">
                        <svg className="mx-auto size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            { 'google_map' === field.type && (
                                <>
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </>
                            ) }
                            { 'address_field' === field.type && (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            ) }
                            { ! [ 'google_map', 'address_field' ].includes( field.type ) && (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            ) }
                        </svg>
                    </div>
                    <div className="text-xs text-gray-500">{ field.placeholder || fieldPlaceholder( field.type ) }</div>
                </div>
            );

        case 'captcha':
            return (
                <div className="rounded-md border border-[#E3E5E8] bg-white p-4 text-center">
                    <div className="mb-3 rounded-md border border-gray-200 bg-white p-3 font-mono text-base">{ 'math_captcha' === field.type ? '3 + 5 = ?' : 'CAPTCHA' }</div>
                    <input type="text" placeholder={ __( 'Enter code', 'wp-user-frontend' ) } disabled className="w-full rounded-md border border-[#E3E5E8] px-3 py-2 text-center text-sm" />
                </div>
            );

        case 'taxonomy':
            return (
                <div className={ `${ BOX } flex cursor-pointer items-center justify-between ${ field.required ? 'border-primary/40' : '' }` }>
                    <span className="text-gray-400">{ field.placeholder || '- Select -' }</span>
                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M13.25 0.875001L7 7.125L0.75 0.875001" stroke="#4B5563" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                </div>
            );

        case 'post':
            return (
                <div className={ `rounded-md border border-primary/20 bg-primary/5 p-3 ${ field.required ? 'border-primary/40' : '' }` }>
                    <div className="mb-2 flex items-center gap-2">
                        <svg className="size-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span className="font-medium text-gray-900">{ field.label }</span>
                    </div>
                    <div className="text-sm leading-5 text-primary">{ field.placeholder || fieldPlaceholder( field.type ) }</div>
                </div>
            );

        case 'column':
            return (
                <div className="rounded-md border-2 border-dashed border-purple-300 bg-purple-50 p-4">
                    <div className="mb-3 text-center font-medium text-purple-600">{ field.label }</div>
                    <div className="flex gap-2">
                        { Array.from( { length: getColumnCount( field ) }, ( _, index ) => (
                            <div key={ index } className="flex-1 rounded border border-purple-300 bg-white p-3 text-center text-sm text-purple-500">
                                { __( 'Column', 'wp-user-frontend' ) } { index + 1 }
                            </div>
                        ) ) }
                    </div>
                </div>
            );

        case 'layout':
            return (
                <div className="rounded-md border-2 border-dashed border-purple-300 bg-purple-50 p-4 text-center">
                    <div className="font-medium text-purple-600">{ field.label }</div>
                </div>
            );

        case 'custom': {
            const isShortcode = 'shortcode' === field.type || 'shortcode' === field.template || 'shortcode' === field.input_type;

            return (
                <div className="rounded-md border border-yellow-300 bg-yellow-50 p-4">
                    <div className="mb-2 flex items-center gap-2">
                        <svg className="size-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span className="font-medium text-yellow-800">{ field.label }</span>
                    </div>
                    <div className="text-sm leading-5 text-yellow-600">
                        { isShortcode ? field.shortcode || fieldPlaceholder( field.type ) : field.placeholder || fieldPlaceholder( field.type ) }
                    </div>
                </div>
            );
        }

        default:
            return <div className={ `${ BOX } flex items-center gap-2` }><span className="text-gray-500">{ field.placeholder || __( 'Custom field', 'wp-user-frontend' ) }</span></div>;
    }
}

/**
 * @param {Object} props
 * @param {Object} props.field Preview field.
 */
export default function PreviewField( { field } ) {
    const required = 'yes' === field.required || true === field.required;

    return (
        <div className="wpuf-form-field flex flex-col gap-2" data-field-template={ field.template || field.type }>
            <span className="flex items-center gap-1 text-sm font-medium leading-5 text-gray-900">
                { field.label }
                { required && <span className="font-bold text-red-500" aria-hidden="true">*</span> }
            </span>
            { field.help_text && <p className="m-0 mb-1 text-sm leading-5 text-gray-500">{ field.help_text }</p> }
            <PreviewBody field={ field } />
            { field.default && (
                <div className="flex items-center gap-1 text-sm leading-5 text-primary">
                    <svg className="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    { __( 'Default:', 'wp-user-frontend' ) } { String( field.default ) }
                </div>
            ) }
        </div>
    );
}
