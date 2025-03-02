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

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

// Definování konstant pro plugin
define( 'BF_ECOMAIL_PLUGIN_VERSION', '1.0.0' );
define( 'BF_ECOMAIL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_SLUG', 'integrate-ecomail-bricks' );
define( 'BF_ECOMAIL_PLUGIN_LICENSE_OPTION', 'bf_ecomail_license_key' );
define( 'BF_ECOMAIL_API_OPTION', 'bf_ecomail_api_key' );

// Načtení souboru pro licencování – zde se očekává, že licensing.php definuje licence funkce (s podmínkou, aby nedošlo k duplicitě)
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/licensing.php';

// Načtení dalších souborů pluginu
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/ecomail-api.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/bricks-integration.php';

// Načtení SureCart SDK pro aktualizace, pokud ještě není načten
if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
    require_once BF_ECOMAIL_PLUGIN_DIR . 'licensing/src/Client.php';
}

// Inicializace licenčního klienta SureCart
global $sc_license;
$sc_license = new \SureCart\Licensing\Client( 'Bricks Form - Ecomail', 'pt_42CmfQhhCzUNorRQHcmQCAfW', __FILE__ );

// Nastavení textdomain odložené na init, aby se překlady načetly ve správný čas.
add_action( 'init', function() use ( $sc_license ) {
    $sc_license->set_textdomain( 'integrate-ecomail-bricks' );
} );

/**
 * Aktivace pluginu
 */
function bf_ecomail_activate() {
    add_option( BF_ECOMAIL_API_OPTION, '' );
    add_option( BF_ECOMAIL_PLUGIN_LICENSE_OPTION, '' );
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bf_ecomail_activate' );

/**
 * Deaktivace pluginu
 */
function bf_ecomail_deactivate() {
    delete_option( BF_ECOMAIL_API_OPTION );
    delete_option( BF_ECOMAIL_PLUGIN_LICENSE_OPTION );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bf_ecomail_deactivate' );

// Poznámka: Všechny licence funkce (například ověřování licence, AJAX endpointy a upozornění) by měly být centralizovány v licensing.php.
// Tím se vyhneš duplicitní deklaraci funkcí (viz chybové hlášení "Cannot redeclare bf_ecomail_verify_license()").

