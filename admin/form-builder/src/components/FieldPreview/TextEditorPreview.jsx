/**
 * Static TinyMCE toolbar preview — mirrors Vue text-editor component.
 * This is a visual mockup only, not a functional editor.
 */
export default function TextEditorPreview( { rich, defaultText } ) {
    const isFull = rich === 'yes';

    return (
        <div className="wpuf-text-editor">
            <div className="wp-core-ui wp-editor-wrap tmce-active">
                <div className="wp-editor-container">
                    <div className="mce-tinymce mce-container mce-panel" style={ { visibility: 'hidden', borderWidth: 1 } }>
                        <div className="mce-container-body mce-stack-layout">
                            <div className="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item">
                                <div className="mce-container-body mce-stack-layout">
                                    <div className="mce-container mce-toolbar mce-stack-layout-item">
                                        <div className="mce-container-body mce-flow-layout">
                                            <div className="mce-container mce-flow-layout-item mce-btn-group">
                                                <div>
                                                    { isFull && (
                                                        <div className="mce-widget mce-btn mce-menubtn mce-fixed-width mce-listbox mce-btn-has-text">
                                                            <button type="button"><span className="mce-txt">Paragraph</span> <i className="mce-caret" /></button>
                                                        </div>
                                                    ) }
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-bold" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-italic" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-bullist" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-numlist" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-blockquote" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-alignleft" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-aligncenter" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-alignright" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-link" /></button></div>
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-unlink" /></button></div>
                                                    { isFull && (
                                                        <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-wp_more" /></button></div>
                                                    ) }
                                                    <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-fullscreen" /></button></div>
                                                    { isFull && (
                                                        <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-wp_adv" /></button></div>
                                                    ) }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="mce-container mce-toolbar mce-stack-layout-item">
                                        <div className="mce-container-body mce-flow-layout">
                                            <div className="mce-container mce-flow-layout-item mce-btn-group">
                                                <div>
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-strikethrough" /></button></div> }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-hr" /></button></div> }
                                                    { isFull && (
                                                        <div className="mce-widget mce-btn mce-colorbutton">
                                                            <button type="button"><i className="mce-ico mce-i-forecolor" /><span className="mce-preview" /></button>
                                                            <button type="button" className="mce-open"> <i className="mce-caret" /></button>
                                                        </div>
                                                    ) }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-pastetext" /></button></div> }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-removeformat" /></button></div> }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-charmap" /></button></div> }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-outdent" /></button></div> }
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-indent" /></button></div> }
                                                    <div className="mce-widget mce-btn mce-disabled"><button type="button"><i className="mce-ico mce-i-undo" /></button></div>
                                                    <div className="mce-widget mce-btn mce-disabled"><button type="button"><i className="mce-ico mce-i-redo" /></button></div>
                                                    { isFull && <div className="mce-widget mce-btn"><button type="button"><i className="mce-ico mce-i-wp_help" /></button></div> }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="mce-edit-area mce-container mce-panel mce-stack-layout-item" style={ { borderWidth: '1px 0px 0px' } }>
                                <div style={ { width: '100%', height: 150, display: 'block' } }>{ defaultText }</div>
                            </div>
                            <div className="mce-statusbar mce-container mce-panel mce-stack-layout-item" style={ { borderWidth: '1px 0px 0px' } }>
                                <div className="mce-container-body mce-flow-layout">
                                    <div className="mce-path mce-flow-layout-item">
                                        <div className="mce-path-item" data-index="0" aria-level="0">p</div>
                                    </div>
                                    <div className="mce-flow-layout-item mce-resizehandle"><i className="mce-ico mce-i-resize" /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
