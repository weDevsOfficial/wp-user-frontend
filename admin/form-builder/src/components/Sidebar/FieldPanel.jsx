import { useState, useRef, useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { createField, isFieldSingleInstance, containsField } from '../../utils/fieldUtils';
import { warn } from '../../utils/globalHelpers';
import { filterPanelSections } from '../../extensions/hooks';
import FieldSearch from './FieldSearch';
import FieldGroup from './FieldGroup';
import FieldItem from './FieldItem';

const isProfilePage = window.location.search.includes( 'page=wpuf-profile-forms' );

export default function FieldPanel() {
    const { panelSections, fieldSettings, formFields, indexToInsert } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            panelSections: store.getPanelSections(),
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
            indexToInsert: store.getIndexToInsert(),
        };
    }, [] );

    const { addField, setIndexToInsert, openFieldSettings } = useDispatch( STORE_NAME );

    const [ searchTerm, setSearchTerm ] = useState( '' );

    const data = window.wpuf_form_builder || {};
    const singleObjects = data.wpuf_single_objects || [];

    // Filter sections by search term
    const filteredSections = useMemo( () => {
        let sections = filterPanelSections( panelSections );

        if ( ! searchTerm ) {
            return sections;
        }

        const term = searchTerm.toLowerCase();
        const matchedFields = Object.keys( fieldSettings ).filter( ( key ) =>
            fieldSettings[ key ].title && fieldSettings[ key ].title.toLowerCase().includes( term )
        );

        return sections.map( ( section ) => ( {
            ...section,
            fields: section.fields.filter( ( field ) => matchedFields.includes( field ) ),
        } ) );
    }, [ panelSections, fieldSettings, searchTerm ] );

    const showCustomFieldTooltipRef = useRef( true );

    const showCustomFieldTooltip = useCallback( ( field ) => {
        if ( typeof window.Swal === 'undefined' ) {
            return;
        }

        const adminScript = window.wpuf_admin_script || {};
        const adminAssetUrl = adminScript.asset_url || data.asset_url || '';
        const imageOne = adminAssetUrl + '/images/custom-fields/settings.png';
        const imageTwo = adminAssetUrl + '/images/custom-fields/advance.png';
        const settingsUrl = ( window.ajaxurl || '' ).replace( 'admin-ajax.php', '' ) + 'admin.php?page=wpuf-settings#wpuf_frontend_posting';
        const fieldId = field.id;

        const html = '<div class="wpuf-custom-field-instruction">' +
            '<div class="step-one">' +
            sprintf(
                '<p class="wpuf-text-base">%s <a href="%s" target="_blank" class="wpuf-text-primary wpuf-font-bold">%s</a>%s"</p>',
                __( 'Navigate through', 'wp-user-frontend' ),
                settingsUrl,
                __( 'WP-admin > WPUF > Settings > Frontend Posting', 'wp-user-frontend' ),
                __( '- there you have to check the checkbox: "Show custom field data in the post content area', 'wp-user-frontend' )
            ) +
            '<img src="' + imageOne + '" alt="settings" class="wpuf-rounded-md">' +
            '</div>' +
            '<div class="step-two">' +
            sprintf(
                '<p class="wpuf-text-base">%s<button type="button" class="wpuf-text-primary wpuf-swal-action-link wpuf-font-bold" data-action="open-advanced-options" data-field-id="%s">%s</button>%s<button type="button" class="wpuf-text-primary wpuf-swal-action-link wpuf-font-bold" data-action="open-advanced-options" data-field-id="%s">%s</button>%s</p>',
                __( 'Edit the custom field inside the post form and on the right side you will see ', 'wp-user-frontend' ),
                fieldId,
                __( '"Advanced Options".', 'wp-user-frontend' ),
                __( ' Expand that, scroll down and you will see ', 'wp-user-frontend' ),
                fieldId,
                __( '"Show data on post"', 'wp-user-frontend' ),
                __( ' - set this yes.', 'wp-user-frontend' )
            ) +
            '<img src="' + imageTwo + '" alt="custom field data" class="wpuf-rounded-md">' +
            '</div>' +
            '</div>';

        window.Swal.fire( {
            title: __( 'Do you want to show custom field data inside your post ?', 'wp-user-frontend' ),
            html,
            imageUrl: data.is_pro_active ? data.lock_icon : data.free_icon,
            showCancelButton: true,
            confirmButtonText: __( "Don't show again", 'wp-user-frontend' ),
            cancelButtonText: __( 'Okay', 'wp-user-frontend' ),
            customClass: {
                confirmButton: '!wpuf-bg-white !wpuf-text-black !wpuf-border !wpuf-border-solid !wpuf-border-gray-300 focus:!wpuf-shadow-none',
                cancelButton: '!wpuf-text-white',
            },
            cancelButtonColor: '#059669',
            didOpen: ( modal ) => {
                const buttons = modal.querySelectorAll( 'button.wpuf-swal-action-link[data-action="open-advanced-options"]' );

                buttons.forEach( ( btn ) => {
                    btn.addEventListener( 'click', ( e ) => {
                        e.preventDefault();
                        const fId = btn.getAttribute( 'data-field-id' );

                        window.Swal.close();

                        setTimeout( () => {
                            openFieldSettings( parseInt( fId, 10 ) );

                            setTimeout( () => {
                                const container = document.querySelector( 'div.wpuf-form-builder-field-options' );

                                if ( ! container ) {
                                    return;
                                }

                                const sections = container.querySelectorAll( '.option-fields-section' );
                                let targetSection = null;
                                let targetH3 = null;

                                sections.forEach( ( section ) => {
                                    const h3 = section.querySelector( 'h3' );

                                    if ( ! h3 ) {
                                        return;
                                    }

                                    const clone = h3.cloneNode( true );
                                    const icons = clone.querySelectorAll( 'i' );
                                    icons.forEach( ( i ) => i.remove() );
                                    const text = clone.textContent.trim().toLowerCase().replace( /\.$/, '' );

                                    if ( text === 'advanced options' ) {
                                        targetH3 = h3;
                                        targetSection = section;
                                    }
                                } );

                                if ( targetH3 ) {
                                    const contentDiv = targetSection.querySelector( '.option-field-section-fields' );

                                    if ( contentDiv && ! contentDiv.offsetParent ) {
                                        targetH3.click();
                                    }

                                    setTimeout( () => {
                                        targetSection.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
                                    }, 350 );
                                }
                            }, 650 );
                        }, 250 );
                    } );
                } );
            },
        } ).then( ( result ) => {
            if ( result.isConfirmed ) {
                showCustomFieldTooltipRef.current = false;
            }
        } );
    }, [ data, openFieldSettings ] );

    const handleAddField = useCallback( ( template ) => {
        // Single-instance check
        if ( isFieldSingleInstance( template, singleObjects ) && containsField( formFields, template ) ) {
            if ( typeof window.Swal !== 'undefined' ) {
                window.Swal.fire( {
                    title: '<span class="wpuf-text-primary">Oops...</span>',
                    html: '<p class="wpuf-text-gray-500 wpuf-text-xl wpuf-m-0 wpuf-p-0">' + __( 'You already have this field in the form', 'wp-user-frontend' ) + '</p>',
                    imageUrl: ( data.asset_url || '' ) + '/images/oops.svg',
                    showCloseButton: true,
                    padding: '1rem',
                    width: '35rem',
                    customClass: {
                        confirmButton: '!wpuf-flex focus:!wpuf-shadow-none !wpuf-bg-primary',
                        closeButton: 'wpuf-absolute',
                    },
                } );
            }
            return;
        }

        const field = createField( template, fieldSettings, formFields );

        if ( ! field ) {
            return;
        }

        const insertAt = indexToInsert === 0 ? formFields.length : indexToInsert;
        addField( field, insertAt );
        setIndexToInsert( 0 );

        // Show custom field tooltip for meta fields (not on profile forms page)
        if ( ! isProfilePage && field.is_meta === 'yes' && showCustomFieldTooltipRef.current ) {
            showCustomFieldTooltip( field );
        }
    }, [ formFields, fieldSettings, indexToInsert, singleObjects, addField, setIndexToInsert, showCustomFieldTooltip ] );

    const handleProAlert = useCallback( ( template ) => {
        const config = fieldSettings[ template ];
        const title = config ? config.title : template;

        if ( typeof window.Swal === 'undefined' ) {
            return;
        }

        const i18n = data.i18n || {};
        const proFieldMsg = i18n.pro_field_message || {};
        const fieldMsg = proFieldMsg[ template ];

        if ( fieldMsg ) {
            let iconHtml = '';

            if ( fieldMsg.asset_type === 'image' ) {
                iconHtml = `<img src="${ fieldMsg.asset_url }" alt="${ template }" loading="lazy" onload="this.closest('div').classList.add('wpuf-is-loaded')">`;
            } else if ( fieldMsg.asset_type === 'video' ) {
                iconHtml = `<iframe onload="this.closest('div').classList.add('wpuf-is-loaded')" class="wpuf-w-full" src="${ fieldMsg.asset_url }" title="${ template }" frameborder="0" allowfullscreen></iframe>`;
            }

            const html = `<div class="wpuf-flex wpuf-text-left">
                <div class="wpuf-w-1/2">
                    <img src="${ data.lock_icon || '' }" alt="">
                    <h2 class="wpuf-text-black"><span class="wpuf-text-primary">${ title } </span>${ i18n.is_a_pro_feature || '' }</h2>
                    <p>${ i18n.pro_feature_msg || '' }</p>
                </div>
                <div class="wpuf-w-1/2">
                    <div class="wpuf-icon-container wpuf-flex wpuf-justify-center wpuf-items-center">
                        ${ iconHtml }
                        <div class="wpuf-shimmer"></div>
                    </div>
                </div>
            </div>`;

            window.Swal.fire( {
                html,
                showCloseButton: true,
                customClass: {
                    confirmButton: '!wpuf-flex focus:!wpuf-shadow-none',
                    closeButton: 'wpuf-absolute',
                },
                width: '50rem',
                padding: '1.5rem',
                confirmButtonColor: '#059669',
                confirmButtonText: i18n.upgrade_to_pro || 'Upgrade to PRO',
            } ).then( ( result ) => {
                if ( result.isConfirmed ) {
                    window.open( data.pro_link || '', '_blank' );
                }
            } );
        } else {
            window.Swal.fire( {
                html: i18n.pro_feature_msg || '',
                showCloseButton: true,
                customClass: {
                    confirmButton: '!wpuf-flex focus:!wpuf-shadow-none',
                    closeButton: 'wpuf-absolute',
                },
                width: '40rem',
                padding: '2rem 3rem',
                title: '<span class="wpuf-text-primary">' + title + '</span> ' + ( i18n.is_a_pro_feature || '' ),
                imageUrl: data.lock_icon || '',
                confirmButtonColor: '#059669',
                confirmButtonText: i18n.upgrade_to_pro || 'Upgrade to PRO',
            } ).then( ( result ) => {
                if ( result.isConfirmed ) {
                    window.open( data.pro_link || '', '_blank' );
                }
            } );
        }
    }, [ fieldSettings ] );

    const handleValidationAlert = useCallback( ( template ) => {
        const config = fieldSettings[ template ];

        if ( ! config || ! config.validator || ! config.validator.msg ) {
            return;
        }

        const validator = config.validator;
        const i18n = data.i18n || {};

        warn( {
            title: validator.msg_title || '',
            color: validator.color || '#059669',
            html: validator.msg,
            showCancelButton: true,
            imageUrl: validator.icon || '',
            confirmButtonText: validator.cta || '',
            cancelButtonText: i18n.ok || __( 'OK', 'wp-user-frontend' ),
            showCloseButton: true,
            width: '40rem',
            padding: '2rem 3rem',
            customClass: {
                confirmButton: '!wpuf-bg-white !wpuf-text-gray-700 focus:!wpuf-shadow-none !wpuf-p-0 hover:!wpuf-bg-none',
                closeButton: 'wpuf-absolute wpuf-top-4 wpuf-right-4',
                cancelButton: '!wpuf-bg-primary !wpuf-text-white',
            },
        } );
    }, [ fieldSettings ] );

    return (
        <div>
            <FieldSearch onSearch={ setSearchTerm } />
            <div className="wpuf-form-builder-form-fields wpuf-mt-4">
                { filteredSections.map( ( section, index ) => (
                    <FieldGroup key={ section.id } section={ section } index={ index }>
                        { section.fields.map( ( template ) => (
                            <FieldItem
                                key={ template }
                                template={ template }
                                onAdd={ handleAddField }
                                onProAlert={ handleProAlert }
                                onValidationAlert={ handleValidationAlert }
                            />
                        ) ) }
                    </FieldGroup>
                ) ) }
            </div>
        </div>
    );
}
