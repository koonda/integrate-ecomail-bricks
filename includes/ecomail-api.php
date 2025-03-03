<?php
/**
 * Ecomail API integrace
 *
 * Tento soubor obsahuje funkce pro komunikaci s Ecomail API.
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

/**
 * Odeslání dat do Ecomail API pro přihlášení odběratele do listu.
 *
 * Předpokládá se, že data obsahují minimálně email a volitelně jméno a příjmení.
 * Podle dokumentace se odesílají v rámci klíče "subscriber_data". Ostatní parametry
 * (trigger_autoresponders, update_existing, atd.) lze dle potřeby upravit.
 *
 * @param array  $subscriber_data Data o odběrateli. Očekává se struktura:
 *                                - email (povinné)
 *                                - name (volitelné)
 *                                - surname (volitelné)
 *                                - další vlastní pole
 * @param string $api_key         API klíč pro autentizaci.
 * @param int    $list_id         ID listu, do kterého se má odběratel přidat.
 * @param array  $options         Další možnosti pro API požadavek.
 *
 * @return array|WP_Error Vrací pole s výsledkem operace nebo objekt WP_Error v případě chyby.
 */
function bf_ecomail_send_data_to_ecomail( $subscriber_data, $api_key, $list_id, $options = array() ) {
    // Kontrola povinných parametrů
    if ( empty( $subscriber_data['email'] ) ) {
        return new WP_Error( 'missing_email', __( 'Email je povinný.', 'integrate-ecomail-bricks' ) );
    }
    
    if ( empty( $list_id ) ) {
        return new WP_Error( 'missing_list_id', __( 'ID seznamu je povinné.', 'integrate-ecomail-bricks' ) );
    }
    
    // Nastavení endpointu
    $endpoint = sprintf( 'https://api2.ecomailapp.cz/lists/%d/subscribe', absint( $list_id ) );
    
    // Výchozí nastavení
    $default_options = array(
        'trigger_autoresponders' => false,
        'trigger_notification'   => false,
        'update_existing'        => true,
        'skip_confirmation'      => true,
        'resubscribe'            => false,
    );
    
    // Sloučení výchozích a uživatelských nastavení
    $options = wp_parse_args( $options, $default_options );
    
    // Příprava dat pro odeslání
    $body = array(
        'subscriber_data'        => $subscriber_data,
        'trigger_autoresponders' => $options['trigger_autoresponders'],
        'trigger_notification'   => $options['trigger_notification'],
        'update_existing'        => $options['update_existing'],
        'skip_confirmation'      => $options['skip_confirmation'],
        'resubscribe'            => $options['resubscribe'],
    );

    $args = array(
        'method'      => 'POST',
        'headers'     => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'body'        => wp_json_encode( $body ),
        'timeout'     => 15,
    );

    // Logování požadavku v debug módu
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] Odesílání požadavku na: ' . $endpoint );
        error_log( '[Bricks Form - Ecomail] Data: ' . wp_json_encode( $body ) );
        
        // Vytvoření podrobného debug souboru
        if (function_exists('bf_ecomail_write_debug_file')) {
            $debug_content = "=== ECOMAIL API REQUEST ===\n";
            $debug_content .= "Endpoint: " . $endpoint . "\n";
            $debug_content .= "Method: POST\n";
            $debug_content .= "Headers: " . print_r($args['headers'], true) . "\n";
            $debug_content .= "Body: " . print_r($body, true) . "\n\n";
            
            bf_ecomail_write_debug_file($debug_content);
        }
    }

    $response = wp_remote_post( $endpoint, $args );

    // Kontrola, zda nedošlo k chybě při komunikaci
    if ( is_wp_error( $response ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[Bricks Form - Ecomail] Chyba komunikace: ' . $response->get_error_message() );
            
            // Přidání informací o chybě do debug souboru
            if (function_exists('bf_ecomail_write_debug_file')) {
                $debug_content = "=== ECOMAIL API ERROR ===\n";
                $debug_content .= "Error: " . $response->get_error_message() . "\n";
                $debug_content .= "Error Code: " . $response->get_error_code() . "\n";
                $debug_content .= "Error Data: " . print_r($response->get_error_data(), true) . "\n";
                
                bf_ecomail_write_debug_file($debug_content);
            }
        }
        return $response;
    }

    $status_code   = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    // Logování odpovědi v debug módu
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] Odpověď (HTTP ' . $status_code . '): ' . $response_body );
        
        // Přidání odpovědi do debug souboru
        if (function_exists('bf_ecomail_write_debug_file')) {
            $debug_content = "=== ECOMAIL API RESPONSE ===\n";
            $debug_content .= "Status Code: " . $status_code . "\n";
            $debug_content .= "Response Body: " . $response_body . "\n";
            $debug_content .= "Response Headers: " . print_r(wp_remote_retrieve_headers($response), true) . "\n";
            
            bf_ecomail_write_debug_file($debug_content);
        }
    }

    // Zkusíme dekódovat JSON odpověď
    $result = json_decode( $response_body, true );

    // Pokud není HTTP status 200 nebo 201, považujeme volání za neúspěšné.
    if ( $status_code !== 200 && $status_code !== 201 ) {
        $error_message = isset( $result['message'] ) ? $result['message'] : __( 'Neznámá chyba při komunikaci s Ecomail API.', 'integrate-ecomail-bricks' );
        return new WP_Error(
            'ecomail_api_error',
            sprintf( __( 'Chyba při odesílání dat do Ecomail (HTTP %s): %s', 'integrate-ecomail-bricks' ), $status_code, $error_message ),
            $result
        );
    }

    return $result;
}

/**
 * Načtení dostupných seznamů (lists) z Ecomail API.
 *
 * @param string $api_key API klíč pro autentizaci.
 * @param bool   $force_refresh Vynutit obnovení cache.
 *
 * @return array|WP_Error Pole se seznamy nebo WP_Error při chybě.
 */
function bf_ecomail_get_lists( $api_key, $force_refresh = false ) {
    // Kontrola API klíče
    if ( empty( $api_key ) ) {
        return new WP_Error( 'missing_api_key', __( 'API klíč je povinný.', 'integrate-ecomail-bricks' ) );
    }
    
    // Název transientu pro cache
    $transient_name = 'bf_ecomail_lists_' . md5($api_key);
    
    // Pokud není vyžádáno obnovení, zkusíme načíst z cache
    if (!$force_refresh) {
        $cached_lists = get_transient($transient_name);
        if ($cached_lists !== false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Bricks Form - Ecomail] Načítání seznamů z cache');
            }
            return $cached_lists;
        }
    }
    
    $endpoint = 'https://api2.ecomailapp.cz/lists';
    $args = array(
        'headers' => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 15,
    );

    // Logování požadavku v debug módu
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] Načítání seznamů z: ' . $endpoint );
    }

    $response = wp_remote_get( $endpoint, $args );

    if ( is_wp_error( $response ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[Bricks Form - Ecomail] Chyba při načítání seznamů: ' . $response->get_error_message() );
        }
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    
    // Logování odpovědi v debug módu
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] Odpověď seznamů (HTTP ' . $status_code . '): ' . $body );
    }
    
    if ( 200 !== $status_code ) {
        return new WP_Error(
            'ecomail_api_error',
            sprintf( __( 'Chyba při načítání seznamů z Ecomail (HTTP %s).', 'integrate-ecomail-bricks' ), $status_code )
        );
    }

    $lists = json_decode( $body, true );

    if ( ! is_array( $lists ) ) {
        return new WP_Error( 'ecomail_api_error', __( 'Neplatná odpověď od Ecomail API.', 'integrate-ecomail-bricks' ) );
    }
    
    // Uložení do cache na 1 hodinu (3600 sekund)
    set_transient($transient_name, $lists, 3600);
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Bricks Form - Ecomail] Seznamy uloženy do cache na 1 hodinu');
    }

    return $lists;
}

/**
 * Vymaže cache seznamů Ecomail.
 *
 * @param string $api_key API klíč pro autentizaci.
 * @return bool True pokud byla cache vymazána, jinak false.
 */
function bf_ecomail_clear_lists_cache($api_key) {
    if (empty($api_key)) {
        return false;
    }
    
    $transient_name = 'bf_ecomail_lists_' . md5($api_key);
    $result = delete_transient($transient_name);
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Bricks Form - Ecomail] Cache seznamů byla ' . ($result ? 'vymazána' : 'nevymazána'));
    }
    
    return $result;
}

/**
 * Získání informací o konkrétním seznamu z Ecomail API.
 *
 * @param string $api_key API klíč pro autentizaci.
 * @param int    $list_id ID seznamu.
 *
 * @return array|WP_Error Informace o seznamu nebo WP_Error při chybě.
 */
function bf_ecomail_get_list( $api_key, $list_id ) {
    // Kontrola API klíče a ID seznamu
    if ( empty( $api_key ) ) {
        return new WP_Error( 'missing_api_key', __( 'API klíč je povinný.', 'integrate-ecomail-bricks' ) );
    }
    
    if ( empty( $list_id ) ) {
        return new WP_Error( 'missing_list_id', __( 'ID seznamu je povinné.', 'integrate-ecomail-bricks' ) );
    }
    
    $endpoint = sprintf( 'https://api2.ecomailapp.cz/lists/%d', absint( $list_id ) );
    $args = array(
        'headers' => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 15,
    );

    $response = wp_remote_get( $endpoint, $args );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status_code ) {
        return new WP_Error(
            'ecomail_api_error',
            sprintf( __( 'Chyba při načítání informací o seznamu z Ecomail (HTTP %s).', 'integrate-ecomail-bricks' ), $status_code )
        );
    }

    $body = wp_remote_retrieve_body( $response );
    $list = json_decode( $body, true );

    if ( ! is_array( $list ) ) {
        return new WP_Error( 'ecomail_api_error', __( 'Neplatná odpověď od Ecomail API.', 'integrate-ecomail-bricks' ) );
    }

    return $list;
}

/**
 * Získání vlastních polí pro seznam z Ecomail API.
 *
 * @param string $api_key API klíč pro autentizaci.
 * @param int    $list_id ID seznamu.
 *
 * @return array|WP_Error Pole s vlastními poli nebo WP_Error při chybě.
 */
function bf_ecomail_get_custom_fields( $api_key, $list_id ) {
    // Kontrola API klíče a ID seznamu
    if ( empty( $api_key ) ) {
        return new WP_Error( 'missing_api_key', __( 'API klíč je povinný.', 'integrate-ecomail-bricks' ) );
    }
    
    if ( empty( $list_id ) ) {
        return new WP_Error( 'missing_list_id', __( 'ID seznamu je povinné.', 'integrate-ecomail-bricks' ) );
    }
    
    $endpoint = sprintf( 'https://api2.ecomailapp.cz/lists/%d/custom-fields', absint( $list_id ) );
    $args = array(
        'headers' => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 15,
    );

    $response = wp_remote_get( $endpoint, $args );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status_code ) {
        return new WP_Error(
            'ecomail_api_error',
            sprintf( __( 'Chyba při načítání vlastních polí z Ecomail (HTTP %s).', 'integrate-ecomail-bricks' ), $status_code )
        );
    }

    $body = wp_remote_retrieve_body( $response );
    $custom_fields = json_decode( $body, true );

    if ( ! is_array( $custom_fields ) ) {
        return new WP_Error( 'ecomail_api_error', __( 'Neplatná odpověď od Ecomail API.', 'integrate-ecomail-bricks' ) );
    }

    return $custom_fields;
}