/**
 * Help page: title row, newsletter card, topics (nav + content card), help
 * cards. The open topic is in the route query (`#/help?topic=<id>`).
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, PageFooter, PageHeader, PageShell } from '@wpuf/components';
import { ExternalLink } from 'lucide-react';

import HelpCards from './HelpCards';
import HelpNewsletter from './HelpNewsletter';
import HelpTopic from './HelpTopic';
import HelpTopicNav from './HelpTopicNav';

const data = () => window.wpufHelp || {};

/**
 * @param {Object} props
 * @param {Object} [props.context] App route context.
 */
export default function HelpPage( { context } ) {
    const help = data();
    const topics = help.topics || [];
    const fromQuery = context?.getQuery?.().topic;
    const [ current, setCurrent ] = useState( topics.some( ( topic ) => topic.id === fromQuery ) ? fromQuery : topics[ 0 ]?.id );
    const topic = topics.find( ( item ) => item.id === current ) || topics[ 0 ];

    const open = ( id ) => {
        setCurrent( id );
        context?.setQuery?.( { topic: id } );
    };

    return (
        <PageShell>
            <PageHeader utm="wpuf-help" helpUrl={ help.docsUrl } helpLabel={ __( 'View all documentation', 'wp-user-frontend' ) } />

            <div className="wpuf-help mt-6 pb-10">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ __( 'Help', 'wp-user-frontend' ) }</h1>
                        <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Guides for every part of User Frontend, and where to get help.', 'wp-user-frontend' ) }</p>
                    </div>
                    <Button variant="secondary" onClick={ () => window.open( help.docsUrl, '_blank', 'noopener' ) }>
                        <ExternalLink size={ 16 } aria-hidden="true" />
                        { __( 'View all Documentations', 'wp-user-frontend' ) }
                    </Button>
                </div>

                <HelpNewsletter newsletter={ help.newsletter || {} } />

                <div className="mt-6 grid gap-6 lg:grid-cols-[240px_1fr]">
                    <HelpTopicNav topics={ topics } current={ topic?.id } onOpen={ open } />
                    { topic && <HelpTopic topic={ topic } /> }
                </div>

                <HelpCards blocks={ help.blocks || [] } />
            </div>
            <PageFooter />
        </PageShell>
    );
}
