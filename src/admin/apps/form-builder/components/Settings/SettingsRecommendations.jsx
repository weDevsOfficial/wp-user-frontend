/**
 * Settings that depend on each other, checked on top of the form settings:
 * each problem says what will go wrong for visitors and offers a quick action
 * (rules in utils/settingsRecommendations.js).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button, Notice } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import { builderData } from '../../utils/boot';
import { findRecommendations } from '../../utils/settingsRecommendations';

/**
 * @param {Object}   props
 * @param {Function} props.onSection Opens a settings section.
 *
 * @return {JSX.Element|null} Recommendations.
 */
export default function SettingsRecommendations( { onSection } ) {
    const settings = useSelect( ( select ) => select( STORE_NAME ).getSettings(), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );
    const data = builderData();

    if ( 'wpuf_forms' !== data.post?.post_type ) {
        return null;
    }

    const items = findRecommendations( settings || {}, data );

    if ( ! items.length ) {
        return null;
    }

    const run = ( action ) => {
        if ( action.set ) {
            Object.entries( action.set ).forEach( ( [ key, value ] ) => updateFormSetting( key, value ) );
        }

        if ( action.section ) {
            onSection( action.section );
        }
    };

    return (
        <div className="wpuf-settings-recommendations mb-6 flex flex-col gap-3" aria-label={ __( 'Recommended changes', 'wp-user-frontend' ) }>
            { items.map( ( item ) => (
                <Notice
                    key={ item.key }
                    tone="warning"
                    title={ item.title }
                    data-recommendation={ item.key }
                    action={
                        item.action.href ? (
                            <Button size="sm" variant="secondary" href={ item.action.href } target={ item.action.external ? '_blank' : undefined } rel={ item.action.external ? 'noopener noreferrer' : undefined }>
                                { item.action.label }
                            </Button>
                        ) : (
                            <Button size="sm" variant="secondary" onClick={ () => run( item.action ) }>
                                { item.action.label }
                            </Button>
                        )
                    }
                >
                    { createInterpolateElement( item.message, { b: <strong className="font-semibold" /> } ) }
                </Notice>
            ) ) }
        </div>
    );
}
