import { Alert, AlertDescription, AlertTitle } from '@wedevs/plugin-ui';
import { CircleAlert, CircleCheck, Info, TriangleAlert } from 'lucide-react';

/**
 * Colours per tone on the settings card look. Title / text go through plugin-ui
 * Alert's colour props; background and border through `--wpuf-notice-*` and a
 * pui.css rule, since the Alert's `[&]:bg-muted` / `[&]:border-border` are
 * !important and beat its own inline colours.
 */
const TONES = {
    info: { bg: '#f9fafb', border: '#e5e7eb', title: '#111827', text: '#4b5563', icon: 'var(--color-primary, #059669)', Icon: Info },
    success: { bg: '#ecfdf5', border: '#a7f3d0', title: '#111827', text: '#374151', icon: 'var(--color-primary, #059669)', Icon: CircleCheck },
    warning: { bg: '#fffbeb', border: '#fde68a', title: '#78350f', text: '#92400e', icon: '#f59e0b', Icon: TriangleAlert },
    error: { bg: '#fef2f2', border: '#fecaca', title: '#7f1d1d', text: '#991b1b', icon: '#ef4444', Icon: CircleAlert },
};

/**
 * Inline notice of the admin screens: plugin-ui Alert with the WPUF tones, on
 * the label scale (14px medium title, 14px text). Replaces the old left-bar
 * yellow / amber / green boxes.
 *
 * @since WPUF_SINCE
 *
 * @param {Object}  props
 * @param {string}  [props.tone]      info|success|warning|error (default info).
 * @param {*}       [props.title]     Title line.
 * @param {*}       [props.action]    Link or button on the right.
 * @param {boolean} [props.icon]      Show the tone icon (default true).
 * @param {string}  [props.className] Extra classes on the box.
 * @param {*}       props.children    Text.
 *
 * @return {JSX.Element} Notice.
 */
export default function Notice( { tone = 'info', title, action, icon = true, className = '', children, ...rest } ) {
    const t = TONES[ tone ] || TONES.info;
    const Icon = t.Icon;

    return (
        <Alert
            variant="default"
            titleColor={ t.title }
            descriptionColor={ t.text }
            data-wpuf-tone={ tone }
            className={ `rounded-[10px] text-sm ${ className }` }
            { ...rest }
            style={ { '--wpuf-notice-bg': t.bg, '--wpuf-notice-border': t.border, '--wpuf-notice-icon': t.icon, ...( rest.style || {} ) } }
        >
            { icon ? <Icon className="size-4" aria-hidden="true" /> : null }
            { title ? <AlertTitle className="text-sm font-medium leading-5">{ title }</AlertTitle> : null }
            { children || action ? (
                <AlertDescription className="text-sm leading-5">
                    { action ? (
                        <div className="flex w-full items-start gap-3">
                            <div className="min-w-0 flex-1">{ children }</div>
                            <div className="shrink-0">{ action }</div>
                        </div>
                    ) : children }
                </AlertDescription>
            ) : null }
        </Alert>
    );
}
