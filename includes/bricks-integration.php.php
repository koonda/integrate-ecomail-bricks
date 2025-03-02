<?php
/**
 * Plugin Name: Bricks Form - Ecomail Integration
 * Plugin URI: https://yourwebsite.com
 * Description: Integrace Bricks Form s Ecomail API a licencováním SureCart.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: Proprietary
 * License URI: https://yourwebsite.com/license
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

/**
 * Aktivace pluginu
 */
function bf_ecomail_activate() {
    add_option(BF_ECOMAIL_API_OPTION, '');
    add_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION, '');
}
register_activation_hook(__FILE__, 'bf_ecomail_activate');

/**
 * Deaktivace pluginu
 */
function bf_ecomail_deactivate() {
    delete_option(BF_ECOMAIL_API_OPTION);
    delete_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION);
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
