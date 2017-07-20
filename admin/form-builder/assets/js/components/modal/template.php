<div>
    <div :class="['wpuf-form-template-modal', show ? 'show' : 'hide' ]">

        <span class="screen-reader-text"><?php _e( 'Modal window. Press escape to close.',  'wpuf'  ); ?></span>
        <a href="#" class="close" v-on:click.prevent="closeModal()">× <span class="screen-reader-text"><?php _e( 'Close modal window',  'wpuf'  ); ?></span></a>

        <header class="modal-header">
            <slot name="header"></slot>
        </header>

        <div :class="['content-container', this.$slots.footer ? 'modal-footer' : 'no-footer']">
            <div class="content">
                <slot name="body"></slot>
            </div>
        </div>

        <footer v-if="this.$slots.footer">
            <slot name="footer"></slot>
        </footer>
    </div>
    <div :class="['wpuf-form-template-modal-backdrop', show ? 'show' : 'hide' ]"></div>
</div>
