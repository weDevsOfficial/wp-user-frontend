<?php
/**
 * The onboarding wizard's own state: picked features, progress, completion, plugin errors, the activation redirect
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

/**
 * Every option and transient the wizard keeps for itself, behind named methods.
 *
 * @since WPUF_SINCE
 */
class State {

    /**
     * Option: the steps done and the last step seen.
     *
     * @since WPUF_SINCE
     */
    const PROGRESS_OPTION = 'wpuf_onboarding_progress';

    /**
     * Option: the features the admin picked (null until answered).
     *
     * @since WPUF_SINCE
     */
    const FEATURES_OPTION = 'wpuf_onboarding_features';

    /**
     * Option: plugin install errors of the last run.
     *
     * @since WPUF_SINCE
     */
    const PLUGIN_ERRORS_OPTION = 'wpuf_onboarding_plugin_errors';

    /**
     * Option: the wizard was completed.
     *
     * @since WPUF_SINCE
     */
    const COMPLETED_OPTION = 'wpuf_onboarding_completed';

    /**
     * Option the legacy setup wizard reads to know setup is done.
     *
     * @since WPUF_SINCE
     */
    const LEGACY_WIZARD_OPTION = 'wpuf_setup_wizard';

    /**
     * Transients set on activation for the one-time redirect (new and legacy wizard).
     *
     * @since WPUF_SINCE
     */
    const REDIRECT_TRANSIENT        = 'wpuf_onboarding_redirect';
    const LEGACY_REDIRECT_TRANSIENT = 'wpuf_activation_redirect';

    /**
     * The picked features, or null when the admin never answered.
     *
     * @since WPUF_SINCE
     *
     * @return array|null
     */
    public function features() {
        $saved = get_option( self::FEATURES_OPTION, null );

        return null === $saved ? null : (array) $saved;
    }

    /**
     * Save the picked features.
     *
     * @since WPUF_SINCE
     *
     * @param array $picked Feature ids
     *
     * @return void
     */
    public function set_features( array $picked ) {
        update_option( self::FEATURES_OPTION, $picked );
    }

    /**
     * Whether the wizard was completed.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_completed() {
        return (bool) get_option( self::COMPLETED_OPTION );
    }

    /**
     * Record completion (the legacy wizard's flag too).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function mark_completed() {
        update_option( self::COMPLETED_OPTION, 1 );
        update_option( self::LEGACY_WIZARD_OPTION, 1 );
    }

    /**
     * Steps done and the last step seen.
     *
     * @since WPUF_SINCE
     *
     * @return array { @type string[] $completed @type string $last_step }
     */
    public function progress() {
        $progress = get_option(
            self::PROGRESS_OPTION,
            [
                'completed' => [],
                'last_step' => '',
            ]
        );

        return is_array( $progress ) ? $progress : [ 'completed' => [], 'last_step' => '' ];
    }

    /**
     * Save the progress.
     *
     * @since WPUF_SINCE
     *
     * @param array $progress As progress() returns it
     *
     * @return void
     */
    public function set_progress( array $progress ) {
        update_option( self::PROGRESS_OPTION, $progress );
    }

    /**
     * Forget progress and completion (Restart).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function reset() {
        delete_option( self::PROGRESS_OPTION );
        delete_option( self::COMPLETED_OPTION );
    }

    /**
     * Plugin install errors of the last run, keyed by plugin slug.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function plugin_errors() {
        $errors = get_option( self::PLUGIN_ERRORS_OPTION, [] );

        return is_array( $errors ) ? $errors : [];
    }

    /**
     * Save or clear the plugin install errors.
     *
     * @since WPUF_SINCE
     *
     * @param array $errors Empty clears them
     *
     * @return void
     */
    public function set_plugin_errors( array $errors ) {
        if ( $errors ) {
            update_option( self::PLUGIN_ERRORS_OPTION, $errors );
        } else {
            delete_option( self::PLUGIN_ERRORS_OPTION );
        }
    }

    /**
     * Whether the activation redirect is pending.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function has_redirect() {
        return (bool) get_transient( self::REDIRECT_TRANSIENT );
    }

    /**
     * Use up the one-time activation redirect (the legacy wizard's too, so the two never fight).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function consume_redirect() {
        delete_transient( self::REDIRECT_TRANSIENT );
        delete_transient( self::LEGACY_REDIRECT_TRANSIENT );
    }
}
