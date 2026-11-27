import { Notice } from '@wpuf/components';

/** Dot colours of a legend row by tone (inline: portal-safe, no palette class needed). */
const TONES = {
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
};

/**
 * Structured help box of a setting (`field.info`): the shared Notice (info
 * tone). Rows are strings or `{ text, legend: [ { tone, label, text } ] }`,
 * rendered as text. The classic builder keeps the field's `long_help` HTML.
 *
 * @since WPUF_SINCE
 *
 * @param {Object} props
 * @param {Object} props.info { title, items }.
 *
 * @return {JSX.Element|null} Help box.
 */
export default function SettingInfo( { info } ) {
    const items = Array.isArray( info?.items ) ? info.items : [];

    if ( ! info?.title && ! items.length ) {
        return null;
    }

    return (
        <Notice tone="info" className="mt-4" title={ info.title || null }>
            { items.length ? (
                <ul className="m-0 list-none space-y-1 p-0">
                    { items.map( ( item, index ) => {
                        const row = 'string' === typeof item ? { text: item } : ( item || {} );

                        return (
                            <li key={ index } className="m-0">
                                { row.text }
                                { Array.isArray( row.legend ) && row.legend.length ? (
                                    <ul className="m-0 mt-1 list-none space-y-1 p-0">
                                        { row.legend.map( ( entry, i ) => (
                                            <li key={ i } className="m-0 flex items-center gap-2">
                                                <span
                                                    aria-hidden="true"
                                                    className="inline-block size-2 shrink-0 rounded-full"
                                                    style={ { background: TONES[ entry.tone ] || TONES.success } }
                                                />
                                                <span><strong className="font-semibold">{ entry.label }</strong>{ entry.text ? ` - ${ entry.text }` : '' }</span>
                                            </li>
                                        ) ) }
                                    </ul>
                                ) : null }
                            </li>
                        );
                    } ) }
                </ul>
            ) : null }
        </Notice>
    );
}
