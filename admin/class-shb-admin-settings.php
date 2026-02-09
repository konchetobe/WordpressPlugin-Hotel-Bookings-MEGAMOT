<?php
/**
 * Admin Settings Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Settings
{

    public static function render_page()
    {
        // Save settings
        if (isset($_POST['shb_save_settings']) && wp_verify_nonce($_POST['shb_settings_nonce'], 'shb_settings')) {
            self::save_settings();
            echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'sanctuary-hotel-booking') . '</p></div>';
        }

        include SHB_PLUGIN_DIR . 'admin/views/settings.php';
    }

    private static function save_settings()
    {
        $fields = array(
            'shb_currency' => 'sanitize_text_field',
            'shb_currency_symbol' => 'sanitize_text_field',
            'shb_check_in_time' => 'sanitize_text_field',
            'shb_check_out_time' => 'sanitize_text_field',
            'shb_stripe_enabled' => 'intval',
            'shb_stripe_test_mode' => 'intval',
            'shb_stripe_test_publishable_key' => 'sanitize_text_field',
            'shb_stripe_test_secret_key' => 'sanitize_text_field',
            'shb_stripe_live_publishable_key' => 'sanitize_text_field',
            'shb_stripe_live_secret_key' => 'sanitize_text_field',
            'shb_paypal_enabled' => 'intval',
            'shb_bank_transfer_enabled' => 'intval',
            'shb_bank_account_holder' => 'sanitize_text_field',
            'shb_bank_iban' => 'sanitize_text_field',
            'shb_bank_bic' => 'sanitize_text_field',
            'shb_bank_name' => 'sanitize_text_field',
            'shb_bank_instructions' => 'sanitize_textarea_field',
            'shb_email_notifications' => 'intval',
            'shb_admin_email' => 'sanitize_email',
            'shb_attach_ics' => 'intval',
            'shb_primary_color' => 'sanitize_hex_color',
            'shb_accent_color' => 'sanitize_hex_color',
            'shb_card_style' => 'sanitize_text_field',
            'shb_button_style' => 'sanitize_text_field',
            'shb_font_family' => 'sanitize_text_field',
            'shb_custom_css' => 'wp_strip_all_tags',
        );

        $checkbox_fields = array(
            'shb_stripe_enabled',
            'shb_stripe_test_mode',
            'shb_paypal_enabled',
            'shb_bank_transfer_enabled',
            'shb_email_notifications',
            'shb_attach_ics',
        );

        foreach ($fields as $field => $sanitize) {
            if (isset($_POST[$field])) {
                update_option($field, call_user_func($sanitize, $_POST[$field]));
            } else {
                if (in_array($field, $checkbox_fields)) {
                    update_option($field, '0');
                }
            }
        }
    }
}
