<?php
/**
 * Plugin Name: Bricks Form - Ecomail Integration
 * Plugin URI: https://yourwebsite.com
 * Description: Integrace Bricks Form s Ecomail API a licencováním SureCart.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: Proprietary
 * License URI: https://yourwebsite.com/license
 * Text Domain: integrate-ecomail-bricks
 */

if (!defined('ABSPATH')) {
    exit; // Zabránění přímému přístupu
}

// Definování konstant pro plugin
define('BF_ECOMAIL_PLUGIN_VERSION', '1.0.0');
define('BF_ECOMAIL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BF_ECOMAIL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BF_ECOMAIL_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BF_ECOMAIL_PLUGIN_SLUG', 'integrate-ecomail-bricks');
define('BF_ECOMAIL_PLUGIN_LICENSE_OPTION', 'bf_ecomail_license_key');
define('BF_ECOMAIL_API_OPTION', 'bf_ecomail_api_key');

// Načtení souboru pro licencování
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/licensing.php';

// Načtení dalších souborů pluginu
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/ecomail-api.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/bricks-integration.php';

// Načtení SureCart SDK pro aktualizace
if (!class_exists('SureCart\Licensing\Client')) {
    require_once BF_ECOMAIL_PLUGIN_DIR . 'licensing/src/Client.php';
}

// Inicializace licenčního klienta SureCart
$sc_license = new \SureCart\Licensing\Client('Bricks Form - Ecomail', 'pt_42CmfQhhCzUNorRQHcmQCAfW', __FILE__);
$sc_license->set_textdomain('bricks-ecomail');

/**
 * Aktivace pluginu
 */
function bf_ecomail_activate() {
    add_option(BF_ECOMAIL_API_OPTION, '');
    add_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION, '');
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'bf_ecomail_activate');

/**
 * Deaktivace pluginu
 */
function bf_ecomail_deactivate() {
    delete_option(BF_ECOMAIL_API_OPTION);
    delete_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION);
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'bf_ecomail_deactivate');

/**
 * Upozornění na expiraci licence v administraci
 */
function bf_ecomail_license_admin_notice() {
    $license_status = get_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION);
    if ($license_status !== 'valid') {
        echo '<div class="notice notice-error"><p><strong>Bricks Form - Ecomail:</strong> Vaše licence je neplatná nebo vypršela. <a href="options-general.php?page=bricks-ecomail-license">Obnovte licenci zde</a>.</p></div>';
    }
}
add_action('admin_notices', 'bf_ecomail_license_admin_notice');

/**
 * Přidání odkazu na správu licence do seznamu pluginů
 */
function bf_ecomail_plugin_action_links($links) {
    $settings_link = '<a href="options-general.php?page=bricks-ecomail-license">Správa licence</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . BF_ECOMAIL_PLUGIN_BASENAME, 'bf_ecomail_plugin_action_links');

/**
 * Kontrola a ověření licence před načtením pluginu
 */
function bf_ecomail_verify_license() {
    global $sc_license;
    if (!$sc_license->is_valid()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('<p><strong>Bricks Form - Ecomail:</strong> Vaše licence je neplatná nebo vypršela. <a href="options-general.php?page=bricks-ecomail-license">Obnovte licenci zde</a>.</p>', 'Chyba licence', ['back_link' => true]);
    }
}
add_action('admin_init', 'bf_ecomail_verify_license');

/**
 * Registrace aktualizací pluginu přes SureCart
 */
function bf_ecomail_register_update_checker() {
    global $sc_license;
    $sc_license->register_update_checker('https://yourwebsite.com/release.json');
}
add_action('init', 'bf_ecomail_register_update_checker');

/**
 * AJAX ověření licence
 */
function bf_ecomail_ajax_check_license() {
    global $sc_license;
    $response = [
        'status' => $sc_license->is_valid() ? 'valid' : 'invalid',
        'message' => $sc_license->is_valid() ? 'Licence je platná.' : 'Licence je neplatná nebo vypršela.'
    ];
    wp_send_json_success($response);
}
add_action('wp_ajax_bf_ecomail_check_license', 'bf_ecomail_ajax_check_license');

/**
 * AJAX kontrola dostupnosti aktualizací pluginu
 */
function bf_ecomail_ajax_check_updates() {
    global $sc_license;
    $update_available = $sc_license->check_for_update();
    $response = [
        'status' => $update_available ? 'available' : 'up-to-date',
        'message' => $update_available ? 'Je dostupná nová verze pluginu!' : 'Váš plugin je aktuální.'
    ];
    wp_send_json_success($response);
}
add_action('wp_ajax_bf_ecomail_check_updates', 'bf_ecomail_ajax_check_updates');

/**
 * Upozornění na dostupnost nové verze pluginu v administraci
 */
function bf_ecomail_update_admin_notice() {
    global $sc_license;
    if ($sc_license->check_for_update()) {
        echo '<div class="notice notice-info"><p><strong>Bricks Form - Ecomail:</strong> Je dostupná nová verze pluginu. <a href="update-core.php">Aktualizujte nyní</a>.</p></div>';
    }
}
add_action('admin_notices', 'bf_ecomail_update_admin_notice');
