/**
 * Form dialog on plugin-ui's Dialog in its own design (header with title,
 * corner close, body). Focus is trapped and returned, Esc and the overlay
 * close it (onClose). The title is required for screen readers; `hideTitle`
 * keeps it visually hidden. Confirms and alerts use ConfirmDialog.
 */
import { Dialog, DialogContent, DialogHeader, DialogTitle, cn } from '@wedevs/plugin-ui';

/**
 * @param {Object}   props
 * @param {boolean}  props.open        Open.
 * @param {Function} props.onClose     () => void (Esc, overlay, close button).
 * @param {*}        props.title       Dialog title.
 * @param {boolean}  [props.hideTitle] Title for screen readers only.
 * @param {string}   [props.titleClassName] Extra title classes.
 * @param {*}        [props.icon]      Shown above the title.
 * @param {string}   [props.className] Extra classes (width).
 * @param {*}        props.children    Body.
 */
export default function Modal( { open, onClose, title, hideTitle = false, titleClassName, icon, className, children } ) {
    return (
        <Dialog open={ open } onOpenChange={ ( next ) => ! next && onClose?.() }>
            <DialogContent className={ cn( 'bg-white sm:max-w-lg', className ) }>
                <DialogHeader>
                    { icon }
                    <DialogTitle className={ hideTitle ? 'sr-only' : titleClassName }>{ title }</DialogTitle>
                </DialogHeader>
                { children }
            </DialogContent>
        </Dialog>
    );
}
