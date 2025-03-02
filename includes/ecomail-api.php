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
 *                                - firstname (volitelné)
 *                                - lastname (volitelné)
 * @param string $api_key         API klíč pro autentizaci.
 * @param int    $list_id         (Volitelný) ID listu, do kterého se má odběratel přidat. Pokud není zadáno, použije se výchozí hodnota.
 *
 * @return array|WP_Error Vrací pole s výsledkem operace nebo objekt WP_Error v případě chyby.
 */
function bf_ecomail_send_data_to_ecomail( $subscriber_data, $api_key, $list_id = 0 ) {
    // Nastavení endpointu
    // Pokud je zadáno platné ID listu, použijeme endpoint s tímto ID,
    // jinak se použije výchozí list ID (v tomto příkladu 1).
    if ( $list_id > 0 ) {
        $endpoint = sprintf( 'https://api2.ecomailapp.cz/lists/%d/subscribe', absint( $list_id ) );
    } else {
        // Definice výchozího listu – uprav dle potřeby.
        $endpoint = 'https://api2.ecomailapp.cz/lists/1/subscribe';
    }

    // Příprava dat pro odeslání
    $body = array(
        'subscriber_data'        => array(
            'email'     => isset( $subscriber_data['email'] ) ? sanitize_email( $subscriber_data['email'] ) : '',
            'firstname' => isset( $subscriber_data['firstname'] ) ? sanitize_text_field( $subscriber_data['firstname'] ) : '',
            'lastname'  => isset( $subscriber_data['lastname'] ) ? sanitize_text_field( $subscriber_data['lastname'] ) : '',
        ),
        // Volitelné parametry – uprav podle svých potřeb
        'trigger_autoresponders' => false,
        'trigger_notification'   => false,
        'update_existing'        => true,
        'skip_confirmation'      => true,
        'resubscribe'            => false,
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

    $response = wp_remote_post( $endpoint, $args );

    // Kontrola, zda nedošlo k chybě při komunikaci
    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code   = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    // Zkusíme dekódovat JSON odpověď
    $result = json_decode( $response_body, true );

    // Pokud není HTTP status 200 nebo 201, považujeme volání za neúspěšné.
    if ( $status_code !== 200 && $status_code !== 201 ) {
        return new WP_Error(
            'ecomail_api_error',
            sprintf( __( 'Chyba při odesílání dat do Ecomail (HTTP %s).', 'integrate-ecomail-bricks' ), $status_code ),
            $result
        );
    }

    return $result;
}

/**
 * Načtení dostupných seznamů (lists) z Ecomail API.
 *
 * @param string $api_key API klíč pro autentizaci.
 *
 * @return array|WP_Error Pole se seznamy nebo WP_Error při chybě.
 */
function bf_ecomail_get_lists( $api_key ) {
    $endpoint = 'https://api2.ecomailapp.cz/lists';
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
            sprintf( __( 'Chyba při načítání seznamů z Ecomail (HTTP %s).', 'integrate-ecomail-bricks' ), $status_code )
        );
    }

    $body  = wp_remote_retrieve_body( $response );
    $lists = json_decode( $body, true );

    if ( ! is_array( $lists ) ) {
        return new WP_Error( 'ecomail_api_error', __( 'Neplatná odpověď od Ecomail API.', 'integrate-ecomail-bricks' ) );
    }

    return $lists;
}

