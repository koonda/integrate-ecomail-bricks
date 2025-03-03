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
            // Načtení seznamů z cache nebo z API (pokud cache neexistuje)
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
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Vyberte seznam', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => $options,
        'description' => esc_html__( 'Vyberte Ecomail seznam, do kterého budou odeslána data', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    // Přidání mapování polí přímo do nastavení formuláře
    // Použijeme prázdné pole options a nastavíme map_fields na true, což je správný způsob pro Bricks
    $controls['ecomail_field_email'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Email (povinné)', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro email', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_field_name'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Jméno', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro jméno', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_field_surname'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Příjmení', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro příjmení', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_field_phone'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Telefon', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro telefon', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_field_city'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Město', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro město', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_field_country'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Země', 'integrate-ecomail-bricks' ),
        'type'        => 'select',
        'options'     => [],
        'map_fields'  => true,
        'description' => esc_html__( 'Vyberte pole formuláře pro zemi', 'integrate-ecomail-bricks' ),
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    // Vlastní pole - repeater není v Bricks k dispozici, proto použijeme textové pole s instrukcemi
    $controls['ecomail_custom_fields'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Vlastní pole', 'integrate-ecomail-bricks' ),
        'type'        => 'textarea',
        'description' => esc_html__( 'Zadejte vlastní pole ve formátu "nazev_pole_v_ecomail:form-field-id", každé pole na nový řádek', 'integrate-ecomail-bricks' ),
        'placeholder' => "company:form-field-company\nwebsite:form-field-website",
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    // Přidání pole pro tagy
    $controls['ecomail_tags'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Tagy', 'integrate-ecomail-bricks' ),
        'type'        => 'textarea',
        'description' => esc_html__( 'Zadejte tagy, které budou přiřazeny kontaktu, každý tag na nový řádek', 'integrate-ecomail-bricks' ),
        'placeholder' => "newsletter\nwebinar\npromo",
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    // Přidání dalších nastavení pro Ecomail
    $controls['ecomail_trigger_autoresponders'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Spustit autoresponder', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Spustit automatické odpovědi po přihlášení', 'integrate-ecomail-bricks' ),
        'default'     => false,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_trigger_notification'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Spustit notifikaci', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Odeslat notifikaci o novém kontaktu', 'integrate-ecomail-bricks' ),
        'default'     => false,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_update_existing'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Aktualizovat existující kontakt', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Pokud kontakt již existuje, aktualizovat jeho údaje', 'integrate-ecomail-bricks' ),
        'default'     => true,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_skip_confirmation'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Přeskočit potvrzení', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Přeskočit potvrzovací email (double opt-in)', 'integrate-ecomail-bricks' ),
        'default'     => true,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    $controls['ecomail_resubscribe'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Znovu přihlásit', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Znovu přihlásit kontakty, které se odhlásily', 'integrate-ecomail-bricks' ),
        'default'     => false,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    // Přidání přepínače pro debug mód
    $controls['ecomail_debug_mode'] = [
        'tab'         => 'content',
        'group'       => 'ecomail',
        'label'       => esc_html__( 'Debug mód', 'integrate-ecomail-bricks' ),
        'type'        => 'checkbox',
        'description' => esc_html__( 'Zapnout podrobné logování pro řešení problémů', 'integrate-ecomail-bricks' ),
        'default'     => false,
        'required'    => ['actions', '=', 'ecomail'],
    ];
    
    return $controls;
}
add_filter( 'bricks/elements/form/controls', 'bf_ecomail_add_custom_form_action' );

/**
 * Přidání skupiny ovládacích prvků pro Ecomail
 *
 * @param array $control_groups Skupiny ovládacích prvků
 * @return array Upravené skupiny ovládacích prvků
 */
function bf_ecomail_add_control_group( $control_groups ) {
    $control_groups['ecomail'] = [
        'title' => esc_html__( 'Ecomail', 'integrate-ecomail-bricks' ),
        'tab' => 'content',
        'required' => [ 'actions', '=', 'ecomail' ],
    ];
    
    return $control_groups;
}
add_filter( 'bricks/elements/form/control_groups', 'bf_ecomail_add_control_group' );

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

    // Kontrola, zda je zapnutý debug mód
    $debug_mode = isset($settings['ecomail_debug_mode']) ? (bool)$settings['ecomail_debug_mode'] : false;

    // Debug log všech polí a nastavení
    if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
        bf_ecomail_log_debug('=== ZAČÁTEK ZPRACOVÁNÍ FORMULÁŘE ===');
        bf_ecomail_log_debug('Všechna pole formuláře: ' . print_r($fields, true));
        bf_ecomail_log_debug('Nastavení formuláře: ' . print_r($settings, true));
    }

    // Získání API klíče z globálního nastavení
    $api_key = get_option( BF_ECOMAIL_API_OPTION );
    if ( empty( $api_key ) ) {
        bf_ecomail_log_error( 'Ecomail API klíč není nastaven.' );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__( 'Interní chyba: API klíč není nastaven.', 'integrate-ecomail-bricks' ),
        ]);
        return;
    }

    // Získání ID seznamu přímo z nastavení formuláře
    $list_id = ( isset( $settings['ecomail_list'] ) && ! empty( $settings['ecomail_list'] ) ) ? absint( $settings['ecomail_list'] ) : 0;
    
    if ( empty( $list_id ) ) {
        bf_ecomail_log_error( 'Nebyl vybrán žádný seznam kontaktů v nastavení formuláře.' );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__( 'Chyba: Nebyl vybrán žádný seznam kontaktů.', 'integrate-ecomail-bricks' ),
        ]);
        return;
    }

    // Příprava dat z formuláře podle mapování v nastavení formuláře
    $subscriber_data = array();
    
    // Zpracování základních polí
    $field_mappings = array(
        'email'     => isset($settings['ecomail_field_email']) ? $settings['ecomail_field_email'] : '',
        'name'      => isset($settings['ecomail_field_name']) ? $settings['ecomail_field_name'] : '',
        'surname'   => isset($settings['ecomail_field_surname']) ? $settings['ecomail_field_surname'] : '',
        'phone'     => isset($settings['ecomail_field_phone']) ? $settings['ecomail_field_phone'] : '',
        'city'      => isset($settings['ecomail_field_city']) ? $settings['ecomail_field_city'] : '',
        'country'   => isset($settings['ecomail_field_country']) ? $settings['ecomail_field_country'] : '',
    );
    
    // Logování mapování polí
    if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
        bf_ecomail_log_debug('Mapování polí: ' . print_r($field_mappings, true));
    }
    
    // Zpracování mapovaných polí - opravený způsob získání hodnot z formuláře
    foreach ($field_mappings as $ecomail_field => $form_field_id) {
        if (!empty($form_field_id)) {
            // Bricks může ukládat hodnoty polí různými způsoby, zkusíme všechny možné formáty
            $value = null;
            $found_method = '';
            
            // 1. Zkusíme přímý přístup k poli podle ID
            if (isset($fields[$form_field_id])) {
                $value = $fields[$form_field_id];
                $found_method = "přímý přístup k poli";
            } 
            // 2. Zkusíme s prefixem form-field-
            elseif (isset($fields['form-field-' . $form_field_id])) {
                $value = $fields['form-field-' . $form_field_id];
                $found_method = "s prefixem form-field-";
            }
            // 3. Zkusíme, zda ID pole již obsahuje prefix form-field-
            elseif (strpos($form_field_id, 'form-field-') === 0 && isset($fields[$form_field_id])) {
                $value = $fields[$form_field_id];
                $found_method = "s již obsaženým prefixem";
            }
            // 4. Projdeme všechna pole a hledáme podle klíče
            else {
                foreach ($fields as $key => $val) {
                    // Zkontrolujeme, zda klíč končí naším ID (může obsahovat různé prefixy)
                    if (preg_match('/' . preg_quote($form_field_id, '/') . '$/', $key)) {
                        $value = $val;
                        $found_method = "pomocí regex v klíči {$key}";
                        break;
                    }
                }
            }
            
            // 5. Zkusíme najít pole podle části názvu (pro případ, že ID je jiné než očekáváme)
            if ($value === null) {
                foreach ($fields as $key => $val) {
                    // Hledáme, zda klíč obsahuje část našeho ID
                    if (strpos($key, $form_field_id) !== false) {
                        $value = $val;
                        $found_method = "podle části názvu v klíči {$key}";
                        break;
                    }
                }
            }
            
            // Pokud jsme našli hodnotu, přidáme ji do dat odběratele
            if ($value !== null) {
                $subscriber_data[$ecomail_field] = sanitize_text_field($value);
                if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                    bf_ecomail_log_debug("Pole {$ecomail_field} nastaveno na hodnotu: '{$value}' (nalezeno {$found_method})");
                }
            } else {
                if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                    bf_ecomail_log_debug("Pole {$ecomail_field} nebylo nalezeno v odeslaných datech. Hledaný identifikátor: {$form_field_id}");
                    bf_ecomail_log_debug("Dostupná pole formuláře: " . implode(', ', array_keys($fields)));
                }
            }
        }
    }
    
    // Kontrola povinného pole email
    if (empty($subscriber_data['email'])) {
        bf_ecomail_log_error('Chybí povinné pole email nebo není správně namapováno.');
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__('Chyba: Email je povinný a musí být správně namapován.', 'integrate-ecomail-bricks'),
        ]);
        return;
    }
    
    // Sanitace emailu
    $subscriber_data['email'] = sanitize_email($subscriber_data['email']);
    
    // Zpracování vlastních polí
    if (!empty($settings['ecomail_custom_fields'])) {
        $custom_fields_text = $settings['ecomail_custom_fields'];
        $custom_fields_lines = explode("\n", $custom_fields_text);
        
        foreach ($custom_fields_lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            
            $ecomail_field = trim($parts[0]);
            $form_field_id = trim($parts[1]);
            
            // Kontrola, zda pole existuje v odeslaných datech - použijeme stejnou logiku jako výše
            if (!empty($ecomail_field) && !empty($form_field_id)) {
                $value = null;
                $found_method = '';
                
                // Zkusíme různé způsoby přístupu k hodnotě pole
                if (isset($fields[$form_field_id])) {
                    $value = $fields[$form_field_id];
                    $found_method = "přímý přístup k poli";
                } elseif (isset($fields['form-field-' . $form_field_id])) {
                    $value = $fields['form-field-' . $form_field_id];
                    $found_method = "s prefixem form-field-";
                } elseif (strpos($form_field_id, 'form-field-') === 0 && isset($fields[$form_field_id])) {
                    $value = $fields[$form_field_id];
                    $found_method = "s již obsaženým prefixem";
                } else {
                    foreach ($fields as $key => $val) {
                        if (preg_match('/' . preg_quote($form_field_id, '/') . '$/', $key)) {
                            $value = $val;
                            $found_method = "pomocí regex v klíči {$key}";
                            break;
                        }
                    }
                }
                
                // Zkusíme najít pole podle části názvu
                if ($value === null) {
                    foreach ($fields as $key => $val) {
                        if (strpos($key, $form_field_id) !== false) {
                            $value = $val;
                            $found_method = "podle části názvu v klíči {$key}";
                            break;
                        }
                    }
                }
                
                if ($value !== null) {
                    $subscriber_data[$ecomail_field] = sanitize_text_field($value);
                    if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                        bf_ecomail_log_debug("Vlastní pole {$ecomail_field} nastaveno na hodnotu: '{$value}' (nalezeno {$found_method})");
                    }
                } else {
                    if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                        bf_ecomail_log_debug("Vlastní pole {$ecomail_field} nebylo nalezeno v odeslaných datech. Hledaný identifikátor: {$form_field_id}");
                    }
                }
            }
        }
    }
    
    // Zpracování tagů
    if (!empty($settings['ecomail_tags'])) {
        $tags_text = $settings['ecomail_tags'];
        $tags_lines = explode("\n", $tags_text);
        $tags = array();
        
        foreach ($tags_lines as $line) {
            $tag = trim($line);
            if (!empty($tag)) {
                $tags[] = $tag;
            }
        }
        
        if (!empty($tags)) {
            $subscriber_data['tags'] = $tags;
            
            if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
                bf_ecomail_log_debug("Přidány tagy: " . implode(', ', $tags));
            }
        }
    }
    
    // Získání dalších nastavení z formuláře
    $options = array(
        'trigger_autoresponders' => isset($settings['ecomail_trigger_autoresponders']) ? (bool)$settings['ecomail_trigger_autoresponders'] : false,
        'trigger_notification'   => isset($settings['ecomail_trigger_notification']) ? (bool)$settings['ecomail_trigger_notification'] : false,
        'update_existing'        => isset($settings['ecomail_update_existing']) ? (bool)$settings['ecomail_update_existing'] : true,
        'skip_confirmation'      => isset($settings['ecomail_skip_confirmation']) ? (bool)$settings['ecomail_skip_confirmation'] : true,
        'resubscribe'            => isset($settings['ecomail_resubscribe']) ? (bool)$settings['ecomail_resubscribe'] : false,
    );

    // Logování dat pro debug
    if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
        bf_ecomail_log_debug('Odesílání dat do Ecomail:');
        bf_ecomail_log_debug('Seznam ID: ' . $list_id);
        bf_ecomail_log_debug('Data odběratele: ' . print_r($subscriber_data, true));
        bf_ecomail_log_debug('Možnosti: ' . print_r($options, true));
    }

    // Volání funkce pro odeslání dat do Ecomail
    if ( function_exists( 'bf_ecomail_send_data_to_ecomail' ) ) {
        $result = bf_ecomail_send_data_to_ecomail( $subscriber_data, $api_key, $list_id, $options );
    } else {
        bf_ecomail_log_error( 'Funkce pro odeslání dat do Ecomail není dostupná.' );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => esc_html__( 'Interní chyba: funkce odeslání dat není dostupná.', 'integrate-ecomail-bricks' ),
        ]);
        return;
    }

    // Nastavení výsledku formuláře podle odpovědi z Ecomail API
    if ( is_wp_error( $result ) ) {
        bf_ecomail_log_error( sprintf( 'Chyba při odesílání dat do Ecomail: %s', $result->get_error_message() ) );
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'error',
            'message' => sprintf( esc_html__( 'Chyba: %s', 'integrate-ecomail-bricks' ), $result->get_error_message() ),
        ]);
    } else {
        bf_ecomail_log_success( 'Data byla úspěšně odeslána do Ecomail.' );
        if ($debug_mode || (defined('WP_DEBUG') && WP_DEBUG)) {
            bf_ecomail_log_debug('Odpověď z Ecomail API: ' . print_r($result, true));
            bf_ecomail_log_debug('=== KONEC ZPRACOVÁNÍ FORMULÁŘE ===');
        }
        $form->set_result([
            'action'  => 'ecomail',
            'type'    => 'success',
            'message' => esc_html__( 'Data byla úspěšně odeslána do Ecomail.', 'integrate-ecomail-bricks' ),
        ]);
    }
}
add_action( 'bricks/form/action/ecomail', 'bf_ecomail_process_custom_action', 10, 1 );

/**
 * Logování chyb
 *
 * @param string $message Zpráva k zalogování.
 */
function bf_ecomail_log_error( $message ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] ERROR: ' . $message );
    }
}

/**
 * Logování úspěšných operací
 *
 * @param string $message Zpráva k zalogování.
 */
function bf_ecomail_log_success( $message ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] SUCCESS: ' . $message );
    }
}

/**
 * Logování debug informací
 *
 * @param string $message Zpráva k zalogování.
 */
function bf_ecomail_log_debug( $message ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[Bricks Form - Ecomail] DEBUG: ' . $message );
    }
}

/**
 * Vytvoření souboru s debug informacemi
 * 
 * @param string $content Obsah k zapsání do souboru.
 */
function bf_ecomail_write_debug_file($content) {
    $upload_dir = wp_upload_dir();
    $debug_dir = trailingslashit($upload_dir['basedir']) . 'ecomail-debug';
    
    // Vytvoření adresáře, pokud neexistuje
    if (!file_exists($debug_dir)) {
        wp_mkdir_p($debug_dir);
    }
    
    // Vytvoření .htaccess souboru pro zabezpečení adresáře
    $htaccess_file = trailingslashit($debug_dir) . '.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents($htaccess_file, $htaccess_content);
    }
    
    // Vytvoření index.php souboru pro zabezpečení adresáře
    $index_file = trailingslashit($debug_dir) . 'index.php';
    if (!file_exists($index_file)) {
        $index_content = "<?php\n// Silence is golden.";
        file_put_contents($index_file, $index_content);
    }
    
    // Vytvoření debug souboru s časovou značkou
    $timestamp = date('Y-m-d-H-i-s');
    $debug_file = trailingslashit($debug_dir) . "ecomail-debug-{$timestamp}.log";
    
    // Zápis obsahu do souboru
    file_put_contents($debug_file, $content, FILE_APPEND);
    
    return $debug_file;
}