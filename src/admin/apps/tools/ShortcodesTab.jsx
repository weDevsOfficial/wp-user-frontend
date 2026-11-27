/**
 * Shortcodes tab: the reference list (`wpuf_tools_shortcodes_list`), each
 * shortcode and example with a copy button; Pro ones badged without Pro.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { createInterpolateElement, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, ErrorState, ProBadge, Skeleton } from '@wpuf/components';
import { Check, Copy } from 'lucide-react';

import { copyText } from './api';
import { store } from './store';

/**
 * @param {Object} props
 * @param {string} props.text Text to copy.
 */
function CopyButton( { text } ) {
    const [ copied, setCopied ] = useState( false );

    const copy = () => copyText( text ).then( () => {
        setCopied( true );
        setTimeout( () => setCopied( false ), 1500 );
    } );

    return (
        <Button variant="icon" size="sm" onClick={ copy } aria-label={ __( 'Copy to clipboard', 'wp-user-frontend' ) } title={ __( 'Copy to clipboard', 'wp-user-frontend' ) }>
            { copied ? <Check size={ 14 } className="text-primary" aria-hidden="true" /> : <Copy size={ 14 } aria-hidden="true" /> }
        </Button>
    );
}

const ProTag = () => <ProBadge link={ false } size="sm" />;

/**
 * @param {Object}  props
 * @param {boolean} props.isPro      Pro is active.
 * @param {string}  props.upgradeUrl Upgrade link.
 */
export default function ShortcodesTab( { isPro, upgradeUrl } ) {
    const { categories, error } = useSelect( ( select ) => ( {
        categories: select( store ).getShortcodes(),
        error: select( store ).getShortcodesError(),
    } ), [] );
    const { loadShortcodes } = useDispatch( store );

    useEffect( () => {
        loadShortcodes();
    }, [ loadShortcodes ] );

    if ( error ) {
        return <ErrorState message={ error.message } />;
    }

    if ( ! categories ) {
        return <Skeleton lines={ 8 } />;
    }

    return (
        <div>
            <p className="m-0 text-sm text-gray-500">{ __( 'Copy and paste these shortcodes into your pages or posts. Click the copy icon to copy a shortcode to your clipboard.', 'wp-user-frontend' ) }</p>

            { ! isPro && (
                <p className="m-0 mt-4 flex flex-wrap items-center gap-2 rounded-lg border border-solid border-sky-100 bg-sky-50 p-3 text-sm text-sky-900">
                    <span className="inline-flex flex-wrap items-center gap-1.5">
                        { createInterpolateElement(
                            /* translators: <badge />: the Pro badge */
                            __( 'Shortcodes marked with <badge /> require WP User Frontend Pro.', 'wp-user-frontend' ),
                            { badge: <ProTag /> }
                        ) }
                    </span>
                    <a href={ upgradeUrl } target="_blank" rel="noopener noreferrer" className="font-medium text-sky-700">{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }</a>
                </p>
            ) }

            { categories.map( ( category ) => (
                <section key={ category.title } className="mt-6 overflow-hidden rounded-[10px] border border-solid border-gray-200">
                    <header className="border-0 border-b border-solid border-gray-200 bg-gray-50 px-4 py-3">
                        <h2 className="m-0 text-sm font-semibold text-gray-900">{ category.title }</h2>
                        { category.description && <p className="m-0 mt-1 text-xs text-gray-500">{ category.description }</p> }
                    </header>
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr className="text-xs uppercase tracking-wide text-gray-500">
                                    <th className="w-1/4 px-4 py-2 font-medium">{ __( 'Shortcode', 'wp-user-frontend' ) }</th>
                                    <th className="px-4 py-2 font-medium">{ __( 'Description', 'wp-user-frontend' ) }</th>
                                    <th className="w-1/3 px-4 py-2 font-medium">{ __( 'Example', 'wp-user-frontend' ) }</th>
                                </tr>
                            </thead>
                            <tbody>
                                { ( category.shortcodes || [] ).map( ( shortcode ) => (
                                    <tr key={ shortcode.code + shortcode.example } className="border-0 border-t border-solid border-gray-100 align-middle">
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <code className="rounded bg-gray-100 px-2 py-1 text-xs text-gray-800">{ shortcode.code }</code>
                                                { shortcode.pro && <ProTag /> }
                                                <CopyButton text={ shortcode.code } />
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 font-normal text-gray-600">{ shortcode.description }</td>
                                        <td className="px-4 py-3">
                                            { shortcode.example ? (
                                                <div className="flex items-center gap-2">
                                                    <code className="rounded bg-emerald-50 px-2 py-1 text-xs text-emerald-900">{ shortcode.example }</code>
                                                    <CopyButton text={ shortcode.example } />
                                                </div>
                                            ) : (
                                                <span className="text-xs text-gray-400">{ __( 'Same as shortcode', 'wp-user-frontend' ) }</span>
                                            ) }
                                        </td>
                                    </tr>
                                ) ) }
                            </tbody>
                        </table>
                    </div>
                </section>
            ) ) }
        </div>
    );
}
