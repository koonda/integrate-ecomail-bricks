<?php
/**
 * SureCart Licensing Integration pro Bricks Form - Ecomail
 *
 * Tento soubor obsahuje funkce pro správu licence pluginu pomocí SureCart API.
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

// Definování konstant pro licencování
define('BF_ECOMAIL_LICENSE_API_URL', 'https://api.surecart.com/v1/public');
define('BF_ECOMAIL_PUBLIC_TOKEN', 'pt_42CmfQhhCzUNorRQHcmQCAfW'); // Veřejný token pro SureCart API

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
 * Kontrola platnosti licence
 *
 * @return bool True pokud je licence platná, jinak false
 */
function bf_ecomail_is_license_valid() {
    $license_data = get_option('bf_ecomail_license_data', array());
    
    if (empty($license_data) || empty($license_data['status']) || $license_data['status'] !== 'valid') {
        return false;
    }
    
    // Kontrola expirace
    if (!empty($license_data['expires']) && strtotime($license_data['expires']) < time()) {
        $license_data['status'] = 'expired';
        update_option('bf_ecomail_license_data', $license_data);
        return false;
    }
    
    // Kontrola aktivace každých 7 dní
    $last_check = get_option('bf_ecomail_license_last_check', 0);
    $week_in_seconds = 7 * DAY_IN_SECONDS;
    
    if (time() - $last_check > $week_in_seconds) {
        // Ověření aktivace
        if (!empty($license_data['activation_id'])) {
            $activation = bf_ecomail_get_activation($license_data['activation_id']);
            
            if (is_wp_error($activation) || empty($activation->id)) {
                $license_data['status'] = 'invalid';
                update_option('bf_ecomail_license_data', $license_data);
                return false;
            }
        }
        
        update_option('bf_ecomail_license_last_check', time());
    }
    
    return true;
}

/**
 * Ověření licence přes SureCart API
 *
 * @param string $license_key Licenční klíč
 * @return object|WP_Error Objekt s informacemi o licenci nebo WP_Error
 */
function bf_ecomail_validate_license($license_key) {
    if (empty($license_key)) {
        return new WP_Error('missing_license_key', esc_html__('Prosím zadejte licenční klíč.', 'integrate-ecomail-bricks'));
    }
    
    $response = wp_remote_get(
        BF_ECOMAIL_LICENSE_API_URL . '/licenses/' . $license_key,
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . BF_ECOMAIL_PUBLIC_TOKEN,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ),
            'timeout' => 15
        )
    );
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response));
    
    if ($status_code !== 200) {
        if ($status_code === 404) {
            return new WP_Error('not_found', esc_html__('Tento licenční klíč není platný. Zkontrolujte jej a zkuste to znovu.', 'integrate-ecomail-bricks'));
        }
        
        $error_message = isset($body->message) ? $body->message : esc_html__('Neznámá chyba při ověřování licence.', 'integrate-ecomail-bricks');
        return new WP_Error('api_error', $error_message);
    }
    
    // Kontrola, zda je odpověď validní
    if (empty($body) || empty($body->id)) {
        return new WP_Error('invalid_response', esc_html__('Neplatná odpověď z licenčního serveru.', 'integrate-ecomail-bricks'));
    }
    
    // Kontrola stavu licence
    if (isset($body->status) && $body->status === 'revoked') {
        return new WP_Error('revoked', esc_html__('Tato licence byla zrušena. Pro získání nové licence proveďte nový nákup.', 'integrate-ecomail-bricks'));
    }
    
    return $body;
}

/**
 * Vytvoření aktivace přes SureCart API
 *
 * @param string $license_id ID licence (ne licenční klíč)
 * @return object|WP_Error Objekt s informacemi o aktivaci nebo WP_Error
 */
function bf_ecomail_create_activation($license_id) {
    if (empty($license_id)) {
        return new WP_Error('missing_license_id', esc_html__('Chybí ID licence.', 'integrate-ecomail-bricks'));
    }
    
    $response = wp_remote_post(
        BF_ECOMAIL_LICENSE_API_URL . '/activations',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . BF_ECOMAIL_PUBLIC_TOKEN,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ),
            'body'    => wp_json_encode(array(
                'activation' => array(
                    'fingerprint' => esc_url_raw(get_site_url()),
                    'name'        => get_bloginfo('name'),
                    'license'     => $license_id
                )
            )),
            'timeout' => 15
        )
    );
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response));
    
    if ($status_code !== 200 && $status_code !== 201) {
        $error_message = isset($body->message) ? $body->message : esc_html__('Neznámá chyba při aktivaci licence.', 'integrate-ecomail-bricks');
        
        // Pokud máme podrobnější chybové zprávy, použijeme je
        if (!empty($body->validation_errors) && is_array($body->validation_errors)) {
            $error_messages = array();
            foreach ($body->validation_errors as $error) {
                if (!empty($error->message)) {
                    $error_messages[] = $error->message;
                }
            }
            if (!empty($error_messages)) {
                $error_message = implode(', ', $error_messages);
            }
        }
        
        return new WP_Error('api_error', $error_message);
    }
    
    // Kontrola, zda je odpověď validní
    if (empty($body) || empty($body->id)) {
        return new WP_Error('invalid_response', esc_html__('Neplatná odpověď z licenčního serveru při aktivaci.', 'integrate-ecomail-bricks'));
    }
    
    return $body;
}

/**
 * Získání informací o aktivaci přes SureCart API
 *
 * @param string $activation_id ID aktivace
 * @return object|WP_Error Objekt s informacemi o aktivaci nebo WP_Error
 */
function bf_ecomail_get_activation($activation_id) {
    if (empty($activation_id)) {
        return new WP_Error('missing_activation_id', esc_html__('Chybí ID aktivace.', 'integrate-ecomail-bricks'));
    }
    
    $response = wp_remote_get(
        BF_ECOMAIL_LICENSE_API_URL . '/activations/' . $activation_id,
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . BF_ECOMAIL_PUBLIC_TOKEN,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ),
            'timeout' => 15
        )
    );
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response));
    
    if ($status_code !== 200) {
        if ($status_code === 404) {
            return new WP_Error('not_found', esc_html__('Aktivace nebyla nalezena.', 'integrate-ecomail-bricks'));
        }
        
        $error_message = isset($body->message) ? $body->message : esc_html__('Neznámá chyba při získávání informací o aktivaci.', 'integrate-ecomail-bricks');
        return new WP_Error('api_error', $error_message);
    }
    
    return $body;
}

/**
 * Smazání aktivace přes SureCart API
 *
 * @param string $activation_id ID aktivace
 * @return bool|WP_Error True při úspěchu nebo WP_Error
 */
function bf_ecomail_delete_activation($activation_id) {
    if (empty($activation_id)) {
        return new WP_Error('missing_activation_id', esc_html__('Chybí ID aktivace.', 'integrate-ecomail-bricks'));
    }
    
    $response = wp_remote_request(
        BF_ECOMAIL_LICENSE_API_URL . '/activations/' . $activation_id,
        array(
            'method'  => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . BF_ECOMAIL_PUBLIC_TOKEN,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ),
            'timeout' => 15
        )
    );
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code !== 200 && $status_code !== 204) {
        if ($status_code === 404) {
            return new WP_Error('not_found', esc_html__('Aktivace nebyla nalezena.', 'integrate-ecomail-bricks'));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        $error_message = isset($body->message) ? $body->message : esc_html__('Neznámá chyba při deaktivaci licence.', 'integrate-ecomail-bricks');
        return new WP_Error('api_error', $error_message);
    }
    
    return true;
}

/**
 * Získání aktuální verze pluginu z SureCart API
 *
 * @return object|WP_Error Objekt s informacemi o aktuální verzi nebo WP_Error
 */
function bf_ecomail_get_current_release() {
    $license_data = get_option('bf_ecomail_license_data', array());
    
    if (empty($license_data) || empty($license_data['license']) || empty($license_data['activation_id'])) {
        return new WP_Error('missing_license', esc_html__('Licence není aktivována.', 'integrate-ecomail-bricks'));
    }
    
    $license_key = $license_data['license'];
    $activation_id = $license_data['activation_id'];
    
    $response = wp_remote_get(
        BF_ECOMAIL_LICENSE_API_URL . '/licenses/' . $license_key . '/expose_current_release?activation_id=' . $activation_id . '&expose_for=10800',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . BF_ECOMAIL_PUBLIC_TOKEN,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ),
            'timeout' => 15
        )
    );
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response));
    
    if ($status_code !== 200) {
        $error_message = isset($body->message) ? $body->message : esc_html__('Neznámá chyba při získávání informací o aktuální verzi.', 'integrate-ecomail-bricks');
        return new WP_Error('api_error', $error_message);
    }
    
    return $body;
}

/**
 * Kontrola aktualizací pluginu
 */
function bf_ecomail_check_for_updates() {
    // Kontrola, zda je licence platná
    if (!bf_ecomail_is_license_valid()) {
        return;
    }
    
    add_filter('pre_set_site_transient_update_plugins', 'bf_ecomail_check_update');
    add_filter('plugins_api', 'bf_ecomail_plugin_info', 10, 3);
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
    
    // Získání informací o aktuální verzi z API
    $current_release = bf_ecomail_get_current_release();
    
    if (is_wp_error($current_release) || empty($current_release->release_json)) {
        return $transient;
    }
    
    $release_info = $current_release->release_json;
    $new_version = isset($release_info->version) ? $release_info->version : '';
    
    // Porovnání verzí
    if (!empty($new_version) && version_compare($current_version, $new_version, '<')) {
        $item = (object) array(
            'id'            => 'integrate-ecomail-bricks/integrate-ecomail-bricks.php',
            'slug'          => 'integrate-ecomail-bricks',
            'plugin'        => $plugin_path,
            'new_version'   => $new_version,
            'url'           => isset($release_info->url) ? $release_info->url : 'https://webypolopate.cz',
            'package'       => isset($current_release->url) ? $current_release->url : '',
            'icons'         => array(),
            'banners'       => array(),
            'banners_rtl'   => array(),
            'tested'        => isset($release_info->tested) ? $release_info->tested : '',
            'requires_php'  => isset($release_info->requires_php) ? $release_info->requires_php : '',
            'compatibility' => new stdClass(),
        );
        
        $transient->response[$plugin_path] = $item;
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
    
    // Získání informací o aktuální verzi z API
    $current_release = bf_ecomail_get_current_release();
    
    if (is_wp_error($current_release) || empty($current_release->release_json)) {
        return $result;
    }
    
    $release_info = $current_release->release_json;
    
    // Vytvoření objektu s informacemi o pluginu
    $plugin_info = (object) array(
        'name'              => isset($release_info->name) ? $release_info->name : 'Bricks Form - Ecomail Integration',
        'slug'              => 'integrate-ecomail-bricks',
        'version'           => isset($release_info->version) ? $release_info->version : '',
        'author'            => isset($release_info->author) ? $release_info->author : '<a href="https://webypolopate.cz">Adam Kotala</a>',
        'author_profile'    => isset($release_info->author_profile) ? $release_info->author_profile : 'https://webypolopate.cz',
        'requires'          => isset($release_info->requires) ? $release_info->requires : '',
        'tested'            => isset($release_info->tested) ? $release_info->tested : '',
        'requires_php'      => isset($release_info->requires_php) ? $release_info->requires_php : '',
        'sections'          => isset($release_info->sections) ? (array) $release_info->sections : array(),
        'download_link'     => isset($current_release->url) ? $current_release->url : '',
    );
    
    return $plugin_info;
}

/**
 * Aktivace licence
 *
 * @param string $license_key Licenční klíč
 * @return bool|WP_Error True při úspěchu, WP_Error při chybě
 */
function bf_ecomail_activate_license($license_key) {
    if (empty($license_key)) {
        return new WP_Error('missing_license_key', esc_html__('Prosím zadejte licenční klíč.', 'integrate-ecomail-bricks'));
    }
    
    // Uložení licenčního klíče
    update_option('bf_ecomail_license_key', $license_key);
    
    // Ověření licence přes SureCart API
    $license = bf_ecomail_validate_license($license_key);
    
    if (is_wp_error($license)) {
        return $license;
    }
    
    // Vytvoření aktivace - použijeme ID licence, ne licenční klíč
    $activation = bf_ecomail_create_activation($license->id);
    
    if (is_wp_error($activation)) {
        return $activation;
    }
    
    // Uložení dat aktivace
    $license_data = array(
        'license'        => $license_key,
        'license_id'     => $license->id,
        'status'         => 'valid',
        'activation_id'  => $activation->id,
        'expires'        => isset($license->revokes_at) ? $license->revokes_at : null,
        'customer_name'  => isset($license->customer_name) ? $license->customer_name : '',
        'customer_email' => isset($license->customer_email) ? $license->customer_email : '',
        'item_name'      => 'Bricks Form - Ecomail Integration',
        'activated_at'   => current_time('mysql')
    );
    
    update_option('bf_ecomail_license_data', $license_data);
    update_option('bf_ecomail_license_last_check', time());
    
    return true;
}

/**
 * Deaktivace licence
 *
 * @return bool|WP_Error True při úspěchu, WP_Error při chybě
 */
function bf_ecomail_deactivate_license() {
    $license_data = get_option('bf_ecomail_license_data', array());
    
    if (empty($license_data) || empty($license_data['activation_id'])) {
        delete_option('bf_ecomail_license_data');
        delete_option('bf_ecomail_license_last_check');
        return true;
    }
    
    // Deaktivace přes SureCart API
    $result = bf_ecomail_delete_activation($license_data['activation_id']);
    
    if (is_wp_error($result)) {
        // Pokud je chyba "not_found", znamená to, že aktivace již byla smazána
        if ($result->get_error_code() === 'not_found') {
            delete_option('bf_ecomail_license_data');
            delete_option('bf_ecomail_license_last_check');
            return true;
        }
        return $result;
    }
    
    // Vymazání dat licence
    delete_option('bf_ecomail_license_data');
    delete_option('bf_ecomail_license_last_check');
    
    return true;
}

/**
 * Logování licenčních operací
 *
 * @param string $message Zpráva k zalogování
 * @param string $type Typ zprávy (error, success, debug)
 */
function bf_ecomail_license_log($message, $type = 'debug') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Bricks Form - Ecomail License] ' . strtoupper($type) . ': ' . $message);
    }
}