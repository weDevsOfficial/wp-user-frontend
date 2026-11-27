/**
 * Row / card action menu (forms list kebab, subscription card menu): a dots
 * button opening a menu; a click outside or Escape closes it, picking an
 * item runs it and closes. Items: [{ key, label, onClick, destructive?,
 * disabled?, icon? }]. Keyboard navigation comes from plugin-ui.
 */
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, cn } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';
import { Check, Copy, Eye, FilePlus, FileText, Pencil, RotateCcw, Send, Trash2, X, Zap } from 'lucide-react';

// Default item icons by key / action (FlyHR row menus); an item's own `icon` wins.
const ICONS = {
    edit: Pencil,
    'quick-edit': Zap,
    quick_edit: Zap,
    duplicate: Copy,
    add_page: FilePlus,
    copy: Copy,
    view: Eye,
    preview: Eye,
    publish: Send,
    draft: FileText,
    restore: RotateCcw,
    accept: Check,
    approve: Check,
    reject: X,
    trash: Trash2,
    delete: Trash2,
};

const itemIcon = ( item ) => {
    if ( item.icon ) {
        return item.icon;
    }

    const Icon = ICONS[ item.key || item.action ];

    return Icon ? <Icon size={ 16 } strokeWidth={ 2 } className="shrink-0" aria-hidden="true" /> : null;
};

const Dots = ( { vertical } ) => (
    <svg className="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        { vertical
            ? <><circle cx="12" cy="5" r="1.75" /><circle cx="12" cy="12" r="1.75" /><circle cx="12" cy="19" r="1.75" /></>
            : <><circle cx="5" cy="12" r="1.75" /><circle cx="12" cy="12" r="1.75" /><circle cx="19" cy="12" r="1.75" /></> }
    </svg>
);

/**
 * @param {Object}  props
 * @param {Array}   props.items      Menu items.
 * @param {string}  [props.label]    Button label for screen readers.
 * @param {boolean} [props.vertical] Vertical dots (subscription cards).
 * @param {string}  [props.align]    start|end (default end).
 * @param {*}       [props.trigger]  Element to open the menu instead of the dots
 *                                   (e.g. <Button>Update</Button>; it must forward its ref).
 */
export default function ActionMenu( { items, label, vertical = false, align = 'end', trigger, className } ) {
    return (
        <DropdownMenu>
            { trigger ? (
                <DropdownMenuTrigger render={ trigger } />
            ) : (
                <DropdownMenuTrigger
                    aria-label={ label || __( 'Actions', 'wp-user-frontend' ) }
                    className={ cn( 'inline-flex items-center justify-center rounded-md p-2 text-gray-400 bg-transparent border-0 cursor-pointer hover:bg-gray-50 hover:text-gray-600 focus-visible:ring-2 focus-visible:ring-primary/30', className ) }
                >
                    <Dots vertical={ vertical } />
                </DropdownMenuTrigger>
            ) }
            <DropdownMenuContent align={ align } className="min-w-44 p-1 bg-white rounded-lg shadow-lg ring-1 ring-black/5 border-0">
                { items.map( ( item ) => (
                    <DropdownMenuItem
                        key={ item.key || item.action || item.label }
                        disabled={ item.disabled }
                        onClick={ () => item.onClick?.( item ) }
                        className={ cn(
                            'flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm cursor-pointer',
                            item.destructive
                                ? 'text-red-600 data-highlighted:bg-red-50 data-highlighted:text-red-700'
                                : 'text-gray-700 data-highlighted:bg-gray-100 data-highlighted:text-gray-900'
                        ) }
                    >
                        { itemIcon( item ) }
                        { item.label }
                    </DropdownMenuItem>
                ) ) }
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
