<div class="wpuf-text-editor">

    <div v-if="is_full" class="wp-media-buttons">
        <button type="button" class="button insert-media add_media" data-editor="content">
            <span class="wp-media-buttons-icon"></span> <?php _e( 'Insert Photo', 'wpuf' ); ?>
        </button>
    </div>

    <div class="wp-core-ui wp-editor-wrap tmce-active">
        <link rel="stylesheet" :href="site_url + 'wp-includes/css/editor.css'" type="text/css" media="all">
        <link rel="stylesheet" :href="site_url + 'wp-includes/js/tinymce/skins/lightgray/skin.min.css'" type="text/css" media="all">

        <div class="wp-editor-container">
            <div class="mce-tinymce mce-container mce-panel" hidefocus="1" tabindex="-1" role="application" style="visibility: hidden; border-width: 1px;">
                <div class="mce-container-body mce-stack-layout">
                    <div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group">
                        <div class="mce-container-body mce-stack-layout">
                            <div class="mce-container mce-toolbar mce-stack-layout-item" role="toolbar">
                                <div class="mce-container-body mce-flow-layout">
                                    <div class="mce-container mce-flow-layout-item mce-btn-group" role="group">
                                        <div>
                                            <div v-if="is_full" class="mce-widget mce-btn mce-menubtn mce-fixed-width mce-listbox mce-btn-has-text" tabindex="-1" aria-labelledby="mceu_0" role="button" aria-haspopup="true"><button role="presentation" type="button" tabindex="-1"><span class="mce-txt">Paragraph</span> <i class="mce-caret"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_1" role="button" aria-label="Bold"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bold"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_2" role="button" aria-label="Italic"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-italic"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_3" role="button" aria-label="Bullet list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-bullist"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_4" role="button" aria-label="Numbered list"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-numlist"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_5" role="button" aria-label="Blockquote"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-blockquote"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_6" role="button" aria-label="Align left"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignleft"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_7" role="button" aria-label="Align center"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-aligncenter"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_8" role="button" aria-label="Align right"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-alignright"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_9" role="button" aria-label="Insert/edit link"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-link"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_10" role="button" aria-label="Remove link"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-unlink"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_11" role="button" aria-label="Insert Read More tag"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-wp_more"></i></button></div>
                                            <div class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_12" role="button" aria-label="Fullscreen"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-fullscreen"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn mce-active" tabindex="-1" aria-labelledby="mceu_13" role="button" aria-label="Toolbar Toggle" aria-pressed="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-wp_adv"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mce-container mce-toolbar mce-stack-layout-item" role="toolbar">
                                <div class="mce-container-body mce-flow-layout">
                                    <div class="mce-container mce-flow-layout-item mce-btn-group" role="group">
                                        <div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_14" role="button" aria-label="Strikethrough"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-strikethrough"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_15" role="button" aria-label="Horizontal line"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-hr"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn mce-colorbutton" role="button" tabindex="-1" aria-haspopup="true" aria-label="Text color"><button role="presentation" hidefocus="1" type="button" tabindex="-1"><i class="mce-ico mce-i-forecolor"></i><span class="mce-preview"></span></button><button type="button" class="mce-open" hidefocus="1" tabindex="-1"> <i class="mce-caret"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_17" role="button" aria-label="Paste as text"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-pastetext"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_18" role="button" aria-label="Clear formatting"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-removeformat"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_19" role="button" aria-label="Special character"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-charmap"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_20" role="button" aria-label="Decrease indent"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-outdent"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_21" role="button" aria-label="Increase indent"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-indent"></i></button></div>
                                            <div class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mceu_22" role="button" aria-label="Undo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-undo"></i></button></div>
                                            <div class="mce-widget mce-btn mce-disabled" tabindex="-1" aria-labelledby="mceu_23" role="button" aria-label="Redo" aria-disabled="true"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-redo"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn" tabindex="-1" aria-labelledby="mceu_24" role="button" aria-label="Keyboard Shortcuts"><button role="presentation" type="button" tabindex="-1"><i class="mce-ico mce-i-wp_help"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mce-edit-area mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;">
                        <iframe frameborder="0" allowtransparency="true" title="Rich Text Area. Press Control-Option-H for help." src='javascript:""' style="width: 100%; height: 150px; display: block;"></iframe>
                    </div>
                    <div class="mce-statusbar mce-container mce-panel mce-stack-layout-item" hidefocus="1" tabindex="-1" role="group" style="border-width: 1px 0px 0px;">
                        <div class="mce-container-body mce-flow-layout">
                            <div class="mce-path mce-flow-layout-item">
                                <div role="button" class="mce-path-item" data-index="0" tabindex="-1" aria-level="0">p</div>
                            </div>
                            <div class="mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
