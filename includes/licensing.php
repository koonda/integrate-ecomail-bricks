<?php
/**
 * Vlastní licencování pro Bricks Form - Ecomail
 *
 * Tento soubor obsahuje funkce pro správu licence pluginu.
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

/**
 * Kontrola platnosti licence
 */
function bf_ecomail_check_license() {
    // Kontrola, zda je licence platná
    if (!bf_ecomail_is_license_valid()) {
        // Zobrazení upozornění pouze na stránkách nastavení pluginu
        $screen = get_current_screen();
        if ($screen && ($screen->id === 'settings_page_bf-ecomail-settings')) {
            add_action('admin_notices', 'bf_ecomail_license_admin_notice');
        }
    }
}
add_action('admin_init', 'bf_ecomail_check_license');

/**
 * Zobrazení upozornění o neplatné licenci
 */
function bf_ecomail_license_admin_notice() {
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e( 'Bricks Form - Ecomail:', 'integrate-ecomail-bricks' ); ?></strong> 
            <?php esc_html_e( 'Vaše licence je neplatná nebo vypršela. Plugin bude fungovat, ale nebudete dostávat aktualizace.', 'integrate-ecomail-bricks' ); ?>
            <a href="<?php echo esc_url( admin_url( 'options-general.php?page=bf-ecomail-settings&tab=license' ) ); ?>"><?php esc_html_e( 'Aktivovat licenci', 'integrate-ecomail-bricks' ); ?></a>
        </p>
    </div>
    <?php
}

/**
 * AJAX kontrola stavu licence
 */
function bf_ecomail_ajax_check_license() {
    check_ajax_referer( 'bf_ecomail_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Nemáte dostatečná oprávnění.', 'integrate-ecomail-bricks' ) ) );
    }
    
    $license_data = get_option('bf_ecomail_license_data', array());
    $is_valid = !empty($license_data['status']) && $license_data['status'] === 'valid';
    
    $response = array(
        'status'  => $is_valid ? 'valid' : 'invalid',
        'message' => $is_valid ? 
            esc_html__( 'Licence je platná.', 'integrate-ecomail-bricks' ) : 
            esc_html__( 'Licence je neplatná nebo vypršela.', 'integrate-ecomail-bricks' ),
    );
    
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_bf_ecomail_check_license', 'bf_ecomail_ajax_check_license' );

/**
 * Kontrola aktualizací pluginu
 */
function bf_ecomail_check_for_updates() {
    // Kontrola, zda je licence platná
    if (!bf_ecomail_is_license_valid()) {
        return;
    }
    
    // Zde by byla implementace kontroly aktualizací
    // Pro účely demonstrace používáme zjednodušenou verzi
    
    add_filter('pre_set_site_transient_update_plugins', 'bf_ecomail_check_update');
}
add_action('init', 'bf_ecomail_check_for_updates');

/**
 * Kontrola dostupnosti aktualizace
 *
 * @param object $transient Transient objekt s informacemi o aktualizacích
 * @return object Upravený transient objekt
 */
function bf_ecomail_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }
    
    // Kontrola, zda je licence platná
    if (!bf_ecomail_is_license_valid()) {
        return $transient;
    }
    
    // Získání informací o aktuální verzi pluginu
    $plugin_path = plugin_basename(BF_ECOMAIL_PLUGIN_DIR . 'integrate-ecomail-bricks.php');
    $plugin_data = get_plugin_data(BF_ECOMAIL_PLUGIN_DIR . 'integrate-ecomail-bricks.php');
    $current_version = $plugin_data['Version'];
    
    // Simulace kontroly nové verze (v reálném prostředí by zde byl API požadavek)
    $new_version = '1.2.0'; // Simulovaná nová verze
    
    // Porovnání verzí
    if (version_compare($current_version, $new_version, '<')) {
        $transient->response[$plugin_path] = (object) array(
            'id'            => 'integrate-ecomail-bricks/integrate-ecomail-bricks.php',
            'slug'          => 'integrate-ecomail-bricks',
            'plugin'        => $plugin_path,
            'new_version'   => $new_version,
            'url'           => 'https://webypolopate.cz',
            'package'       => 'https://webypolopate.cz/download/integrate-ecomail-bricks.zip',
            'icons'         => array(),
            'banners'       => array(),
            'banners_rtl'   => array(),
            'tested'        => '6.5',
            'requires_php'  => '7.4',
            'compatibility' => new stdClass(),
        );
    }
    
    return $transient;
}

/**
 * Informace o pluginu pro obrazovku aktualizací
 *
 * @param object $result Výchozí informace o pluginu
 * @param string $action Akce
 * @param object $args Argumenty
 * @return object Upravené informace o pluginu
 */
function bf_ecomail_plugin_info($result, $action, $args) {
    // Kontrola, zda se jedná o náš plugin
    if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== 'integrate-ecomail-bricks') {
        return $result;
    }
    
    // Kontrola, zda je licence platná
    if (!bf_ecomail_is_license_valid()) {
        return $result;
    }
    
    // Simulace informací o pluginu (v reálném prostředí by zde byl API požadavek)
    $plugin_info = (object) array(
        'name'              => 'Bricks Form - Ecomail Integration',
        'slug'              => 'integrate-ecomail-bricks',
        'version'           => '1.2.0',
        'author'            => '<a href="https://webypolopate.cz">Adam Kotala</a>',
        'author_profile'    => 'https://webypolopate.cz',
        'requires'          => '5.6',
        'tested'            => '6.5',
        'requires_php'      => '7.4',
        'sections'          => array(
            'description'   => 'Integrace Bricks Form s Ecomail API.',
            'changelog'     => '<h4>1.2.0</h4><ul><li>Přidána podpora pro tagy</li><li>Vylepšeno mapování polí</li></ul>',
            'installation'  => 'Nahrajte plugin do složky /wp-content/plugins/ a aktivujte jej v administraci WordPressu.',
        ),
        'download_link'     => 'https://webypolopate.cz/download/integrate-ecomail-bricks.zip',
    );
    
    return $plugin_info;
}
add_filter('plugins_api', 'bf_ecomail_plugin_info', 10, 3);