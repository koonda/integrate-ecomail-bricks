<?php
/**
 * Název pluginu: Bricks Form - Ecomail Integrace
 * Plugin URI: https://yourwebsite.com
 * Popis: Integruje Bricks Form Widget s Ecomail API.
 * Verze: 1.0.0
 * Autor: Your Name
 * Autor URI: https://yourwebsite.com
 * Licence: Proprietary
 * Licence URI: https://yourwebsite.com/license
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

// Načtení potřebných souborů
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/ecomail-api.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/bricks-integration.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/license-handler.php';

/**
 * Funkce při aktivaci pluginu
 */
function bf_ecomail_activate() {
    add_option(BF_ECOMAIL_API_OPTION, '');
    add_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION, '');
}
register_activation_hook(__FILE__, 'bf_ecomail_activate');

/**
 * Funkce při deaktivaci pluginu
 */
function bf_ecomail_deactivate() {
    delete_option(BF_ECOMAIL_API_OPTION);
    delete_option(BF_ECOMAIL_PLUGIN_LICENSE_OPTION);
}
register_deactivation_hook(__FILE__, 'bf_ecomail_deactivate');
