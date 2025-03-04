<?php
/**
 * Plugin Name: Bricks Form - Ecomail Integration
 * Plugin URI: https://webypolopate.cz
 * Description: Propojte vaše Bricks formuláře s newslettrovou službou Ecomail rychle & jednoduše.
 * Version: 1.2.2
 * Requires at least: 5.6
 * Tested up to: 6.7.2
 * Requires PHP: 7.4
 * Author: Adam Kotala
 * Author URI: https://webypolopate.cz
 * License: Proprietary
 * License URI: https://webypolopate.cz/produkty/propojeni-ecomail-pro-bricks-builder/
 * Text Domain: integrate-ecomail-bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

// Definování konstant pro plugin
define( 'BF_ECOMAIL_PLUGIN_VERSION', '1.2.2' );
define( 'BF_ECOMAIL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'BF_ECOMAIL_PLUGIN_SLUG', 'integrate-ecomail-bricks' );
define( 'BF_ECOMAIL_PLUGIN_LICENSE_OPTION', 'bf_ecomail_license_key' );
define( 'BF_ECOMAIL_API_OPTION', 'bf_ecomail_api_key' );

// Načtení souborů pluginu
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/ecomail-api.php';
require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/bricks-integration.php';

// Načtení SureCart SDK pro licencování a aktualizace, pokud ještě není načten
if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
    require_once BF_ECOMAIL_PLUGIN_DIR . 'wordpress-sdk/src/Client.php';
    // Načtení souboru pro licencování
    require_once BF_ECOMAIL_PLUGIN_DIR . 'includes/licensing.php';
}

/**
 * Aktivace pluginu
 */
function bf_ecomail_activate() {
    // Přidání výchozích nastavení
    add_option( BF_ECOMAIL_API_OPTION, '' );
    add_option( BF_ECOMAIL_PLUGIN_LICENSE_OPTION, '' );
    
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bf_ecomail_activate' );

/**
 * Deaktivace pluginu
 */
function bf_ecomail_deactivate() {
    // Při deaktivaci ponecháme nastavení, aby uživatel nemusel znovu zadávat API klíč
    // Pokud chcete smazat všechna nastavení, odkomentujte následující řádky
    // delete_option( BF_ECOMAIL_API_OPTION );
    // delete_option( BF_ECOMAIL_PLUGIN_LICENSE_OPTION );
    
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bf_ecomail_deactivate' );

/**
 * Odinstalace pluginu
 */
function bf_ecomail_uninstall() {
    // Smazání všech nastavení při odinstalaci
    delete_option( BF_ECOMAIL_API_OPTION );
    delete_option( BF_ECOMAIL_PLUGIN_LICENSE_OPTION );
}
register_uninstall_hook( __FILE__, 'bf_ecomail_uninstall' );

/**
 * Přidání odkazu na nastavení do seznamu pluginů
 */
function bf_ecomail_add_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=bf-ecomail-settings' ) . '">' . esc_html__( 'Nastavení', 'integrate-ecomail-bricks' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bf_ecomail_add_settings_link' );

/**
 * Přidání odkazů do řádku pluginu v seznamu pluginů
 */
function bf_ecomail_plugin_row_meta( $links, $file ) {
    if ( BF_ECOMAIL_PLUGIN_BASENAME === $file ) {
        $row_meta = array(
            'docs' => '<a href="' . esc_url( admin_url( 'options-general.php?page=bf-ecomail-settings&tab=documentation' ) ) . '" aria-label="' . esc_attr__( 'Zobrazit dokumentaci', 'integrate-ecomail-bricks' ) . '">' . esc_html__( 'Dokumentace', 'integrate-ecomail-bricks' ) . '</a>',
            'support' => '<a href="https://webypolopate.cz/podpora" target="_blank" aria-label="' . esc_attr__( 'Získat podporu', 'integrate-ecomail-bricks' ) . '">' . esc_html__( 'Podpora', 'integrate-ecomail-bricks' ) . '</a>',
        );

        return array_merge( $links, $row_meta );
    }

    return $links;
}
add_filter( 'plugin_row_meta', 'bf_ecomail_plugin_row_meta', 10, 2 );