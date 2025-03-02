<?php
/**
 * Integrace Bricks Form s Ecomail API
 *
 * Přidává vlastní akci "Ecomail" do Bricks Form widgetu, která umožňuje odesílání dat do Ecomail.
 * V nastavení formuláře se dynamicky načítají všechny dostupné seznamy z Ecomail API.
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

/**
 * Přidání vlastní akce a vlastního ovládacího prvku pro výběr seznamu do nastavení formuláře.
 *
 * Pomocí filtru 'bricks/elements/form/controls' přidáme naši vlastní akci "Ecomail" a také
 * pole pro výběr Ecomail seznamu, které se zobrazí přímo v nastavení formuláře.
 *
 * @param array $controls Seznam existujících ovládacích prvků formuláře.
 * @return array Upravený seznam ovládacích prvků.
 */
function bf_ecomail_add_custom_form_action( $controls ) {
    // Přidání naší vlastní akce do seznamu akcí
    $controls['actions']['options']['ecomail'] = esc_html__( 'Ecomail', 'integrate-ecomail-bricks' );
    
    // Dynamické načtení dostupných seznamů přes API
    $options = [];
    $api_key = get_option( BF_ECOMAIL_API_OPTION );
    if ( empty( $api_key ) ) {
        $options[''] = esc_html__( 'Nejprve nastavte API klíč', 'integrate-ecomail-bricks' );
    } else {
        if ( function_exists( 'bf_ecomail_get_lists' ) ) {
            $lists = bf_ecomail_get_lists( $api_key );
            if ( is_wp_error( $lists ) ) {
                $options[''] = sprintf( esc_html__( 'Chyba: %s', 'integrate-ecomail-bricks' ), $lists->get_error_message() );
            } else {
                if ( ! empty( $lists ) && is_array( $lists ) ) {
                    foreach ( $lists as $list ) {
                        $list_id   = isset( $list['id'] ) ? $list['id'] : '';
                        $list_name = isset( $list['name'] ) ? $list['name'] : sprintf( esc_html__( 'Seznam %s', 'integrate-ecomail-bricks' ), $list_id );
                        if ( ! empty( $list_id ) ) {
                            $options[ $list_id ] = $list_name;
                        }
                    }
                }
                if ( empty( $options ) ) {
                    $options[''] = esc_html__( 'Žádné seznamy nebyly nalezeny', 'integrate-ecomail-bricks' );
                }
            }
        } else {
            $options[''] = esc_html__( 'Funkce pro načtení seznamů není dostupná', 'integrate-ecomail-bricks' );
        }
    }
    
    // Přidání vlastního ovládacího prvku pro výběr seznamu do nastavení formuláře
    $controls['ecomail_list'] = [
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Vyberte seznam', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => $options,
        'description' => esc_html__( 'Vyberte Ecomail seznam, do kterého budou odeslána data', 'integrate-ecomail-bricks' ),
        'required'    => false,
    ];
    
    return $controls;
}
add_filter( 'bricks/elements/form/controls', 'bf_ecomail_add_custom_form_action' );

/**
 * Zpracování vlastní akce "Ecomail" při odeslání formuláře.
 *
 * Tato funkce se spustí, když uživatel odesílá formulář s vybranou akcí "Ecomail".
 * Přijímá objekt $form, ze kterého získáme nastavení (včetně hodnoty našeho vlastního pole 'ecomail_list')
 * a odeslaná data.
 *
 * @param Bricks\Form $form Objekt formuláře.
 */
function bf_ecomail_process_custom_action( $form ) {
    // Získání nastavení formuláře a odeslaných polí
    $settings = $form->get_settings();
    $fields   = $form->get_fields();

    // Získání API klíče z globálního nastavení (toto nastavení musí být nakonfigurováno v pluginu)
    $api_key = get_option( BF_ECOMAIL_API_OPTION );
    if ( empty( $api_key ) ) {
        error_log( esc_html__( 'Ecomail API klíč není nastaven.', 'integrate-ecomail-bricks' ) );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__( 'Interní chyba: API klíč není nastaven.', 'integrate-ecomail-bricks' ),
        ]);
        return;
    }

    // Získání ID seznamu přímo z nastavení formuláře (náš vlastní ovládací prvek 'ecomail_list')
    $list_id = ( isset( $settings['ecomail_list'] ) && ! empty( $settings['ecomail_list'] ) ) ? absint( $settings['ecomail_list'] ) : 1;

    // Příprava dat z formuláře – mapování polí na data, která Ecomail API očekává.
    // Předpokládáme, že ve formuláři jsou pole s identifikátory např. 'form-field-email', 'form-field-firstname', 'form-field-lastname'
    $data_to_send = array(
        'email'     => isset( $fields['form-field-email'] ) ? sanitize_email( $fields['form-field-email'] ) : '',
        'firstname' => isset( $fields['form-field-firstname'] ) ? sanitize_text_field( $fields['form-field-firstname'] ) : '',
        'lastname'  => isset( $fields['form-field-lastname'] ) ? sanitize_text_field( $fields['form-field-lastname'] ) : '',
    );

    // Volání funkce pro odeslání dat do Ecomail (definované v souboru ecomail-api.php)
    if ( function_exists( 'bf_ecomail_send_data_to_ecomail' ) ) {
        $result = bf_ecomail_send_data_to_ecomail( $data_to_send, $api_key, $list_id );
    } else {
        error_log( esc_html__( 'Funkce pro odeslání dat do Ecomail není dostupná.', 'integrate-ecomail-bricks' ) );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__( 'Interní chyba: funkce odeslání dat není dostupná.', 'integrate-ecomail-bricks' ),
        ]);
        return;
    }

    // Nastavení výsledku formuláře podle odpovědi z Ecomail API
    if ( is_wp_error( $result ) ) {
        error_log( sprintf( esc_html__( 'Chyba při odesílání dat do Ecomail: %s', 'integrate-ecomail-bricks' ), $result->get_error_message() ) );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => sprintf( esc_html__( 'Chyba: %s', 'integrate-ecomail-bricks' ), $result->get_error_message() ),
        ]);
    } else {
        error_log( esc_html__( 'Data byla úspěšně odeslána do Ecomail.', 'integrate-ecomail-bricks' ) );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'success',
            'message' => esc_html__( 'Data byla úspěšně odeslána do Ecomail.', 'integrate-ecomail-bricks' ),
        ]);
    }
}
add_action( 'bricks/form/action/ecomail', 'bf_ecomail_process_custom_action', 10, 1 );
