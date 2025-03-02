<?php
/**
 * SureCart Licensing Integration pro Bricks Form - Ecomail
 *
 * Tento soubor inicializuje a spravuje licenci pomocí SureCart Licensing SDK.
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

// Načtení SureCart Licensing Client, pokud ještě není načten.
if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
    require_once BF_ECOMAIL_PLUGIN_DIR . 'licensing/src/Client.php';
}

// Inicializace licenčního klienta
global $sc_license;
$sc_license = new \SureCart\Licensing\Client( 'Bricks Form - Ecomail', 'pt_42CmfQhhCzUNorRQHcmQCAfW', __FILE__ );

// Nastavení textdomain pro překlady
$sc_license->set_textdomain( 'integrate-ecomail-bricks' );

// Přidání stránky pro správu licence jako podstránku v menu "Nastavení"
$sc_license->settings()->add_page( array(
    'type'        => 'submenu',
    'parent_slug' => 'options-general.php',
    'page_title'  => esc_html__( 'Manage License', 'integrate-ecomail-bricks' ),
    'menu_title'  => esc_html__( 'License Settings', 'integrate-ecomail-bricks' ),
    'capability'  => 'manage_options',
    'menu_slug'   => 'bricks-ecomail-license',
) );

/**
 * Kontrola platnosti licence při načtení administrace.
 */
function bf_ecomail_verify_license() {
    global $sc_license;
    if ( ! $sc_license->is_valid() ) {
        // Deaktivuje plugin, pokud licence není platná
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die(
            '<p><strong>Bricks Form - Ecomail:</strong> ' . esc_html__( 'Your license is invalid or expired. Please update your license.', 'integrate-ecomail-bricks' ) . '</p>',
            esc_html__( 'License Error', 'integrate-ecomail-bricks' ),
            array( 'back_link' => true )
        );
    }
}
add_action( 'admin_init', 'bf_ecomail_verify_license' );

/**
 * Zobrazení upozornění v administraci, pokud licence není platná.
 */
function bf_ecomail_license_admin_notice() {
    global $sc_license;
    if ( ! $sc_license->is_valid() ) {
        echo '<div class="notice notice-error"><p><strong>Bricks Form - Ecomail:</strong> ' . esc_html__( 'Your license is invalid or expired. Please update your license.', 'integrate-ecomail-bricks' ) . '</p></div>';
    }
}
add_action( 'admin_notices', 'bf_ecomail_license_admin_notice' );

/**
 * AJAX endpoint pro kontrolu licence.
 */
function bf_ecomail_ajax_check_license() {
    check_ajax_referer( 'bf_ecomail_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission.', 'integrate-ecomail-bricks' ) ) );
    }
    global $sc_license;
    $response = array(
        'status'  => $sc_license->is_valid() ? 'valid' : 'invalid',
        'message' => $sc_license->is_valid() ? esc_html__( 'License is valid.', 'integrate-ecomail-bricks' ) : esc_html__( 'License is invalid or expired.', 'integrate-ecomail-bricks' ),
    );
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_bf_ecomail_check_license', 'bf_ecomail_ajax_check_license' );

/**
 * Registrace aktualizačního kontroléru přes SureCart Licensing.
 *
 * Předpokládá se, že release.json je správně nakonfigurován na tvém serveru.
 */
function bf_ecomail_register_update_checker() {
    global $sc_license;
    $sc_license->register_update_checker( 'https://yourwebsite.com/release.json' );
}
add_action( 'init', 'bf_ecomail_register_update_checker' );
