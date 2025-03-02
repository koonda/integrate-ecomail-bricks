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

// Nastavíme cestu k SDK – ve vaší instalaci se nachází v "wordpress-sdk/src/Client.php"
$client_file = BF_ECOMAIL_PLUGIN_DIR . 'wordpress-sdk/src/Client.php';

// Kontrola existence souboru s SDK
if ( file_exists( $client_file ) ) {
    require_once $client_file;
} else {
    error_log( 'SureCart SDK nebyl nalezen. Prosím stáhněte si jej z https://github.com/surecart/wordpress-sdk/ a umístěte do složky wordpress-sdk/src/Client.php' );
    // Volitelně: wp_die( 'SureCart SDK nebyl nalezen. Plugin nemůže být spuštěn.' );
}

// Inicializace licenčního klienta a nastavení textdomain provedeme na hooku 'init'
add_action( 'init', 'bf_ecomail_init_license', 1 );
function bf_ecomail_init_license() {
    global $sc_license;
    $sc_license = new \SureCart\Licensing\Client( 'Bricks Form - Ecomail', 'pt_42CmfQhhCzUNorRQHcmQCAfW', __FILE__ );
    $sc_license->set_textdomain( 'integrate-ecomail-bricks' );
    
    // Registrace aktualizačního kontroléru, pokud metoda existuje
    if ( method_exists( $sc_license, 'register_update_checker' ) ) {
        $sc_license->register_update_checker( 'https://yourwebsite.com/release.json' );
    } else {
        error_log( 'Metoda register_update_checker není dostupná v SureCart SDK.' );
    }
    
    // Přidání stránky pro správu licence jako podstránku v menu "Nastavení"
    $sc_license->settings()->add_page( array(
        'type'        => 'submenu',
        'parent_slug' => 'options-general.php',
        'page_title'  => esc_html__( 'Manage License', 'integrate-ecomail-bricks' ),
        'menu_title'  => esc_html__( 'License Settings', 'integrate-ecomail-bricks' ),
        'capability'  => 'manage_options',
        'menu_slug'   => 'bricks-ecomail-license',
    ) );
}

// Funkce jsou deklarovány podmíněně, aby se zabránilo redeklaraci:

if ( ! function_exists( 'bf_ecomail_verify_license' ) ) {
    function bf_ecomail_verify_license() {
        global $sc_license;
        if ( ! isset( $sc_license ) || ! $sc_license->is_valid() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die(
                '<p><strong>Bricks Form - Ecomail:</strong> ' . esc_html__( 'Your license is invalid or expired. Please update your license.', 'integrate-ecomail-bricks' ) . '</p>',
                esc_html__( 'License Error', 'integrate-ecomail-bricks' ),
                array( 'back_link' => true )
            );
        }
    }
}
add_action( 'admin_init', 'bf_ecomail_verify_license' );

if ( ! function_exists( 'bf_ecomail_license_admin_notice' ) ) {
    function bf_ecomail_license_admin_notice() {
        global $sc_license;
        if ( ! isset( $sc_license ) || ! $sc_license->is_valid() ) {
            echo '<div class="notice notice-error"><p><strong>Bricks Form - Ecomail:</strong> ' . esc_html__( 'Your license is invalid or expired. Please update your license.', 'integrate-ecomail-bricks' ) . '</p></div>';
        }
    }
}
add_action( 'admin_notices', 'bf_ecomail_license_admin_notice' );

if ( ! function_exists( 'bf_ecomail_ajax_check_license' ) ) {
    function bf_ecomail_ajax_check_license() {
        check_ajax_referer( 'bf_ecomail_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission.', 'integrate-ecomail-bricks' ) ) );
        }
        global $sc_license;
        $response = array(
            'status'  => ( isset( $sc_license ) && $sc_license->is_valid() ) ? 'valid' : 'invalid',
            'message' => ( isset( $sc_license ) && $sc_license->is_valid() ) ? esc_html__( 'License is valid.', 'integrate-ecomail-bricks' ) : esc_html__( 'License is invalid or expired.', 'integrate-ecomail-bricks' ),
        );
        wp_send_json_success( $response );
    }
}
add_action( 'wp_ajax_bf_ecomail_check_license', 'bf_ecomail_ajax_check_license' );
