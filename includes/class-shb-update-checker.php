<?php
/**
 * GitHub Release Update Checker
 *
 * @package SanctuaryHotelBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers automatic updates from the plugin's GitHub releases.
 *
 * The bundled Plugin Update Checker library is copied from Invoice-Forge and
 * intentionally requires a release asset. GitHub source archives are not used
 * because they may not contain the plugin's packaged dependencies.
 */
class SHB_Update_Checker {

    /** @var string */
    private const GITHUB_REPOSITORY = 'konchetobe/WordpressPlugin-Hotel-Bookings-MEGAMOT';

    /**
     * Register the GitHub update integration.
     */
    public static function init() {
        $library = SHB_PLUGIN_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';

        if (!file_exists($library)) {
            return;
        }

        require_once $library;

        if (!class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
            return;
        }

        try {
            $update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                'https://github.com/' . self::GITHUB_REPOSITORY,
                SHB_PLUGIN_FILE,
                'sanctuary-hotel-booking'
            );

            $update_checker->setBranch('main');
            $update_checker->getVcsApi()->enableReleaseAssets(
                null,
                \YahnisElsts\PluginUpdateChecker\v5p6\Vcs\Api::REQUIRE_RELEASE_ASSETS
            );
        } catch (\Throwable $exception) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Sanctuary Hotel Booking] Update checker failed: ' . $exception->getMessage());
            }
        }
    }
}
