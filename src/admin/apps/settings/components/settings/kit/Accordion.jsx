import { Accordion as SharedAccordion } from '@wpuf/components';
import ProBadge from './ProBadge';

/**
 * Collapsible settings group (Figma Email tab: divider row, optional 40x40
 * icon, title + description, chevron) on the shared Accordion (4.6a).
 */
export default function Accordion( { title, desc, icon = null, isPro = false, defaultOpen = false, children } ) {
    return (
        <SharedAccordion
            title={ title }
            description={ desc }
            icon={ icon }
            badge={ isPro ? <ProBadge /> : null }
            defaultOpen={ defaultOpen }
        >
            { children }
        </SharedAccordion>
    );
}
