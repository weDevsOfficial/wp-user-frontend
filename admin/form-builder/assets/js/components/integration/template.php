<div class="wpuf-integrations-wrap">

    <template v-if="hasIntegrations">
        <div :class="['wpuf-integration', isAvailable(integration.id) ? '' : 'collapsed']" v-for="integration in integrations">
            <div class="wpuf-integration-header">
                <div class="wpuf-integration-header-toggle">
                    <span :class="['wpuf-toggle-switch', 'big', isActive(integration.id) ? 'checked' : '']" v-on:click="toggleState(integration.id, $event.target)"></span>
                </div>
                <div class="wpuf-integration-header-label">
                    <img class="icon" :src="integration.icon" :alt="integration.title">
                    {{ integration.title }} <span class="label-premium" v-if="!isAvailable(integration.id)"><?php _e( 'Premium Feature', 'best-contact-form' ); ?></span>
                </div>

                <div class="wpuf-integration-header-actions">
                    <button type="button" class="toggle-area" v-on:click="showHide($event.target)">
                        <span class="screen-reader-text"><?php _e( 'Toggle panel', 'best-contact-form' ); ?></span>
                        <span class="toggle-indicator"></span>
                    </button>
                </div>
            </div>

            <div class="wpuf-integration-settings">

                <div v-if="isAvailable(integration.id)">
                    <component :is="'wpuf-integration-' + integration.id" :id="integration.id"></component>
                </div>
                <div v-else>
                    <?php _e( 'This feature is available on the premium version only.', 'best-contact-form' ); ?>
                    <a class="button" :href="pro_link" target="_blank"><?php _e( 'Upgrade to Pro', 'best-contact-form' ); ?></a>
                </div>

            </div>
        </div>
    </template>

    <div v-else>
        <?php _e( 'No integration found.', 'best-contact-form' ); ?>
    </div>
</div>