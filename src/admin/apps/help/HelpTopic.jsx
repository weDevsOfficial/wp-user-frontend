/**
 * One topic: title, body (the shared partial's HTML, kses'd on the server),
 * the docs button and the related articles.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wpuf/components';
import { ExternalLink, FileText } from 'lucide-react';

/**
 * @param {Object} props
 * @param {Object} props.topic { id, title, body, button: { label, url }, articles: [{ title, url }] }.
 */
export default function HelpTopic( { topic } ) {
    return (
        <article className="rounded-[10px] border border-solid border-gray-200 bg-white p-6 shadow-sm" data-topic={ topic.id } aria-labelledby={ `wpuf-help-${ topic.id }` }>
            <h2 id={ `wpuf-help-${ topic.id }` } className="m-0 mb-3 text-base font-semibold text-gray-900">{ topic.title }</h2>

            { /* Plugin's own translated text from views/help/<id>.php, through wp_kses_post. */ }
            <div className="wpuf-help-body text-sm leading-6 text-gray-700" dangerouslySetInnerHTML={ { __html: topic.body } } />

            { topic.button?.url && (
                <div className="mt-6">
                    <Button onClick={ () => window.open( topic.button.url, '_blank', 'noopener' ) }>
                        { topic.button.label }
                        <ExternalLink size={ 16 } aria-hidden="true" />
                    </Button>
                </div>
            ) }

            { topic.articles?.length > 0 && (
                <div className="mt-8 border-0 border-t border-solid border-gray-200 pt-6">
                    <h3 className="m-0 mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">{ __( 'Related Articles:', 'wp-user-frontend' ) }</h3>
                    <ul className="m-0 grid list-none gap-2 p-0 sm:grid-cols-2">
                        { topic.articles.map( ( article ) => (
                            <li key={ article.url } className="m-0">
                                <a href={ article.url } target="_blank" rel="noopener noreferrer" className="group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary">
                                    <FileText size={ 16 } aria-hidden="true" className="shrink-0 text-gray-400 group-hover:text-primary" />
                                    { article.title }
                                </a>
                            </li>
                        ) ) }
                    </ul>
                </div>
            ) }
        </article>
    );
}
