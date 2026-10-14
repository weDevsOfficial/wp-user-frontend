/**
 * Static preview of a rich text field on the builder canvas: the toolbar of
 * the editor the React form shows (TipTap, src/frontend/kit/fields/editor),
 * full (`rich` = yes: format menu, lists, alignment, link, read more,
 * fullscreen, more) or teeny. A visual mock-up only, not a functional editor.
 */
import { AlignCenter, AlignLeft, AlignRight, Bold, ChevronDown, Ellipsis, Italic, Link, List, ListOrdered, Maximize2, Quote, Redo2, SeparatorHorizontal, Strikethrough, Underline, Undo2 } from 'lucide-react';

const FULL = [ Bold, Italic, List, ListOrdered, Quote, AlignLeft, AlignCenter, AlignRight, Link, SeparatorHorizontal, Maximize2, Ellipsis ];
const TEENY = [ Bold, Italic, Underline, Quote, Strikethrough, List, ListOrdered, AlignLeft, AlignCenter, AlignRight, Undo2, Redo2, Link, Maximize2 ];

export default function TextEditorPreview( { rich, defaultText } ) {
    const isFull = 'yes' === rich;
    const icons = isFull ? FULL : TEENY;

    return (
        <div className="wpuf-text-editor w-full overflow-hidden rounded-lg border border-gray-300 bg-white" aria-hidden="true">
            <div className="flex flex-wrap items-center gap-0.5 border-b border-gray-200 bg-gray-50 p-1.5">
                { isFull && (
                    <span className="mr-1 inline-flex h-8 items-center gap-1.5 rounded-md border border-gray-300 bg-white px-2.5 text-[13px] font-medium text-gray-700">
                        Paragraph
                        <ChevronDown className="size-3.5 text-gray-500" />
                    </span>
                ) }
                { icons.map( ( Icon, index ) => (
                    <span key={ index } className={ `inline-flex size-8 items-center justify-center rounded-md ${ Undo2 === Icon || Redo2 === Icon ? 'text-gray-300' : 'text-gray-600' }` }>
                        <Icon className="size-4" strokeWidth={ 2 } />
                    </span>
                ) ) }
            </div>
            <div className="min-h-[150px] whitespace-pre-wrap px-3 py-2.5 text-sm leading-6 text-gray-700">{ defaultText }</div>
        </div>
    );
}
