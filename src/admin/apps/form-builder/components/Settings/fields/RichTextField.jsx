import { RichEditor } from '@wpuf/components';
import SettingLabel from './SettingLabel';

// Buttons and TinyMCE settings of develop's wpuf_print_rich_text() wp_editor()
// (teeny; its bullist / numlist / link / fullscreen buttons never rendered: no plugin).
const DEVELOP_TEENY_TOOLBAR = 'bold,italic,underline,blockquote,strikethrough,alignleft,aligncenter,alignright,undo,redo';
const DEVELOP_TINYMCE = { autoresize_min_height: 100, wp_autoresize_on: true, plugins: 'wpautoresize' };

/**
 * Rich text settings field (develop type="rich-text": registration e-mail
 * bodies printed by wpuf_print_rich_text() with wp_editor, teeny, 10 rows).
 * On the shared RichEditor (TipTap; WpEditor for content it cannot keep 1:1, 2026-10-14; WpEditor since 4.5a): Visual / Code tabs and Add Media as develop,
 * an edit stores the TinyMCE HTML as develop's builder save did (paragraphs
 * as <p>, PAR0034). Shows `field.default` while nothing is stored; only an
 * edit writes a value.
 */
export default function RichTextField( { field, name, value, onChange } ) {
    const editorKey = String( name ).replace( /\W+/g, '_' );

    return (
        <>
            <SettingLabel field={ field } htmlFor={ `wpuf-editor-${ editorKey }` } />
            <RichEditor
                id={ editorKey }
                value={ value !== undefined && value !== null ? value : ( field.default || '' ) }
                onChange={ ( content ) => onChange( name, content ) }
                toolbar={ DEVELOP_TEENY_TOOLBAR }
                tinymce={ DEVELOP_TINYMCE }
                rows={ 10 }
                label={ field.label }
            />
        </>
    );
}
