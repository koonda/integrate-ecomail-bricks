<?php
/**
 * Nastavení administrace pluginu
 *
 * @package integrate-ecomail_bricks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Zabránění přímému přístupu
}

/**
 * Přidání stránky do administrace
 */
function bf_ecomail_add_admin_menu() {
    add_options_page(
        esc_html__( 'Bricks Form - Ecomail', 'integrate-ecomail-bricks' ), // Název stránky
        esc_html__( 'Bricks Form - Ecomail', 'integrate-ecomail-bricks' ), // Název v menu
        'manage_options', // Oprávnění
        'bf-ecomail-settings', // Slug
        'bf_ecomail_settings_page' // Callback pro vykreslení stránky
    );
}
add_action( 'admin_menu', 'bf_ecomail_add_admin_menu' );

/**
 * Vykreslení administrační stránky
 */
function bf_ecomail_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Bricks Form - Ecomail Integrace', 'integrate-ecomail-bricks' ); ?></h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=bf-ecomail-settings&tab=general" class="nav-tab <?php echo empty($_GET['tab']) || $_GET['tab'] === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Obecné nastavení', 'integrate-ecomail-bricks' ); ?>
            </a>
            <a href="?page=bf-ecomail-settings&tab=license" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'license' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Licence', 'integrate-ecomail-bricks' ); ?>
            </a>
            <a href="?page=bf-ecomail-settings&tab=documentation" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'documentation' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Dokumentace', 'integrate-ecomail-bricks' ); ?>
            </a>
        </h2>
        
        <?php
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        if ($active_tab === 'documentation') {
            // Dokumentace
            bf_ecomail_documentation_tab();
        } elseif ($active_tab === 'license') {
            // Licence
            bf_ecomail_license_tab();
        } else {
            // Obecné nastavení (API klíč)
            ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'bf_ecomail_options_group' );
                do_settings_sections( 'bf-ecomail-settings' );
                submit_button();
                ?>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Zobrazení dokumentace
 */
function bf_ecomail_documentation_tab() {
    ?>
    <div class="bf-ecomail-documentation">
        <h2><?php esc_html_e( 'Dokumentace', 'integrate-ecomail-bricks' ); ?></h2>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Začínáme', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'Pro správné fungování integrace je potřeba:', 'integrate-ecomail-bricks' ); ?></p>
            <ol>
                <li><?php esc_html_e( 'Zadat platný API klíč Ecomail v záložce "Obecné nastavení"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Vytvořit formulář v Bricks Builderu', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Přidat akci "Ecomail" v nastavení formuláře', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Vybrat seznam kontaktů a nastavit mapování polí přímo v nastavení formuláře', 'integrate-ecomail-bricks' ); ?></li>
            </ol>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Získání API klíče', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'API klíč najdete ve vašem účtu Ecomail:', 'integrate-ecomail-bricks' ); ?></p>
            <ol>
                <li><?php esc_html_e( 'Přihlaste se do svého účtu Ecomail', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Přejděte do sekce "Nastavení"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Vyberte záložku "API"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Zkopírujte váš API klíč', 'integrate-ecomail-bricks' ); ?></li>
            </ol>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Vytvoření formuláře v Bricks', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'Postup vytvoření formuláře:', 'integrate-ecomail-bricks' ); ?></p>
            <ol>
                <li><?php esc_html_e( 'V Bricks Builderu přidejte element "Form"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Přidejte potřebná pole (minimálně pole pro email)', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'V nastavení formuláře v sekci "Actions" vyberte "Ecomail"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'V záložce "Ecomail" vyberte seznam kontaktů a nastavte mapování polí', 'integrate-ecomail-bricks' ); ?></li>
            </ol>
            <p><?php esc_html_e( 'Důležité: Pro každé pole formuláře nastavte jedinečný identifikátor v sekci "ID" nebo "Name" v nastavení pole.', 'integrate-ecomail-bricks' ); ?></p>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Mapování polí', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'Mapování polí umožňuje propojit pole z vašeho formuláře s odpovídajícími poli v Ecomail:', 'integrate-ecomail-bricks' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Email - povinné pole pro identifikaci kontaktu', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Jméno (name) - křestní jméno kontaktu', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Příjmení (surname) - příjmení kontaktu', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Vlastní pole - jakákoliv další pole, která máte v Ecomail', 'integrate-ecomail-bricks' ); ?></li>
            </ul>
            <p><?php esc_html_e( 'Pro každé pole zadejte ID pole z formuláře ve formátu "form-field-XXX", kde XXX je ID pole v Bricks.', 'integrate-ecomail-bricks' ); ?></p>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Tagy', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'Můžete přidat tagy, které budou přiřazeny kontaktu při přihlášení:', 'integrate-ecomail-bricks' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'V nastavení formuláře v sekci "Tagy" zadejte tagy oddělené čárkou', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Tagy pomáhají segmentovat vaše kontakty pro cílené kampaně', 'integrate-ecomail-bricks' ); ?></li>
             </ul>
            <p><?php esc_html_e( 'Pozor: Tagy jsou case sensitive (rozlišují velká a malá písmena).', 'integrate-ecomail-bricks' ); ?></p>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Další nastavení', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'V nastavení formuláře můžete nakonfigurovat další možnosti:', 'integrate-ecomail-bricks' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Spustit autoresponder - aktivuje automatické odpovědi po přihlášení', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Spustit notifikaci - odešle notifikaci o novém kontaktu', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Aktualizovat existující kontakt - aktualizuje údaje, pokud kontakt již existuje', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Přeskočit potvrzení - přeskočí potvrzovací email (double opt-in)', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Znovu přihlásit - znovu přihlásí kontakty, které se odhlásily', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Debug mód - zapne podrobné logování pro řešení problémů', 'integrate-ecomail-bricks' ); ?></li>
            </ul>
        </div>
        
        <div class="bf-ecomail-doc-section">
            <h3><?php esc_html_e( 'Řešení problémů', 'integrate-ecomail-bricks' ); ?></h3>
            <p><?php esc_html_e( 'Pokud integrace nefunguje správně, zkontrolujte:', 'integrate-ecomail-bricks' ); ?></p>
            <ol>
                <li><?php esc_html_e( 'Platnost API klíče - použijte tlačítko "Otestovat API klíč"', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Správné mapování polí - ID polí musí odpovídat polím ve formuláři', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Povinné pole email - musí být správně namapováno', 'integrate-ecomail-bricks' ); ?></li>
                <li><?php esc_html_e( 'Vybraný seznam kontaktů - musí být vybrán platný seznam', 'integrate-ecomail-bricks' ); ?></li>
            </ol>
            <p><?php esc_html_e( 'Pro podrobnější diagnostiku zapněte WP_DEBUG v souboru wp-config.php a zkontrolujte chybové hlášení.', 'integrate-ecomail-bricks' ); ?></p>
        </div>
    </div>
    <style>
        .bf-ecomail-documentation {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
        }
        .bf-ecomail-doc-section {
            margin-bottom: 30px;
        }
        .bf-ecomail-doc-section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .bf-ecomail-doc-section ul, 
        .bf-ecomail-doc-section ol {
            margin-left: 20px;
        }
    </style>
    <?php
}

/**
 * Zobrazení záložky s licencí
 */
function bf_ecomail_license_tab() {
    // Zpracování formuláře licence
    if (isset($_POST['bf_ecomail_license_action']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bf_ecomail_license_nonce')) {
        $action = sanitize_text_field($_POST['bf_ecomail_license_action']);
        
        if ($action === 'activate' && isset($_POST['license_key'])) {
            $license_key = sanitize_text_field($_POST['license_key']);
            $result = bf_ecomail_activate_license($license_key);
            
            if (is_wp_error($result)) {
                add_settings_error(
                    'bf_ecomail_license',
                    'license_activation_error',
                    $result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'bf_ecomail_license',
                    'license_activated',
                    esc_html__('Licence byla úspěšně aktivována.', 'integrate-ecomail-bricks'),
                    'success'
                );
            }
        } elseif ($action === 'deactivate') {
            $result = bf_ecomail_deactivate_license();
            
            if (is_wp_error($result)) {
                add_settings_error(
                    'bf_ecomail_license',
                    'license_deactivation_error',
                    $result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'bf_ecomail_license',
                    'license_deactivated',
                    esc_html__('Licence byla úspěšně deaktivována.', 'integrate-ecomail-bricks'),
                    'success'
                );
            }
        }
    }
    
    // Získání stavu licence
    $license_data = get_option('bf_ecomail_license_data', array());
    $license_key = get_option('bf_ecomail_license_key', '');
    $is_active = !empty($license_data['status']) && $license_data['status'] === 'valid';
    
    ?>
    <div class="bf-ecomail-license-container">
        <h2><?php esc_html_e('Správa licence', 'integrate-ecomail-bricks'); ?></h2>
        
        <?php settings_errors('bf_ecomail_license'); ?>
        
        <div class="bf-ecomail-license-status">
            <h3><?php esc_html_e('Stav licence', 'integrate-ecomail-bricks'); ?></h3>
            
            <?php if ($is_active): ?>
                <div class="bf-ecomail-license-active">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <p><?php esc_html_e('Vaše licence je aktivní a platná.', 'integrate-ecomail-bricks'); ?></p>
                    <?php if (!empty($license_data['expires'])): ?>
                        <p><?php echo sprintf(esc_html__('Platnost licence vyprší: %s', 'integrate-ecomail-bricks'), date_i18n(get_option('date_format'), strtotime($license_data['expires']))); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bf-ecomail-license-invalid">
                    <span class="dashicons dashicons-dismiss"></span>
                    <p><?php esc_html_e('Vaše licence není aktivována nebo je neplatná.', 'integrate-ecomail-bricks'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bf-ecomail-license-form">
            <form method="post" action="">
                <?php wp_nonce_field('bf_ecomail_license_nonce'); ?>
                
                <?php if (!$is_active): ?>
                    <div class="bf-ecomail-license-activate">
                        <h3><?php esc_html_e('Aktivovat licenci', 'integrate-ecomail-bricks'); ?></h3>
                        <p><?php esc_html_e('Zadejte svůj licenční klíč pro aktivaci pluginu.', 'integrate-ecomail-bricks'); ?></p>
                        
                        <div class="bf-ecomail-license-key-input">
                            <label for="license_key"><?php esc_html_e('Licenční klíč:', 'integrate-ecomail-bricks'); ?></label>
                            <input type="text" name="license_key" id="license_key" class="regular-text" value="<?php echo esc_attr($license_key); ?>" />
                        </div>
                        
                        <input type="hidden" name="bf_ecomail_license_action" value="activate" />
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Aktivovat licenci', 'integrate-ecomail-bricks'); ?>" />
                        </p>
                    </div>
                <?php else: ?>
                    <div class="bf-ecomail-license-deactivate">
                        <h3><?php esc_html_e('Deaktivovat licenci', 'integrate-ecomail-bricks'); ?></h3>
                        <p><?php esc_html_e('Vaše licence je aktivována pro tuto stránku. Můžete ji deaktivovat, pokud ji chcete použít na jiné stránce.', 'integrate-ecomail-bricks'); ?></p>
                        
                        <div class="bf-ecomail-license-info">
                            <p><strong><?php esc_html_e('Licenční klíč:', 'integrate-ecomail-bricks'); ?></strong> <?php echo esc_html(substr($license_key, 0, 5) . '...' . substr($license_key, -5)); ?></p>
                            <?php if (!empty($license_data['customer_name'])): ?>
                                <p><strong><?php esc_html_e('Zákazník:', 'integrate-ecomail-bricks'); ?></strong> <?php echo esc_html($license_data['customer_name']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($license_data['customer_email'])): ?>
                                <p><strong><?php esc_html_e('Email:', 'integrate-ecomail-bricks'); ?></strong> <?php echo esc_html($license_data['customer_email']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="bf_ecomail_license_action" value="deactivate" />
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-secondary" value="<?php esc_attr_e('Deaktivovat licenci', 'integrate-ecomail-bricks'); ?>" />
                        </p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="bf-ecomail-license-info-box">
            <h3><?php esc_html_e('Informace o licenci', 'integrate-ecomail-bricks'); ?></h3>
            <p><?php esc_html_e('Licence vám umožňuje:', 'integrate-ecomail-bricks'); ?></p>
            <ul>
                <li><?php esc_html_e('Používat plugin na jedné produkční stránce', 'integrate-ecomail-bricks'); ?></li>
                <li><?php esc_html_e('Přístup k aktualizacím pluginu', 'integrate-ecomail-bricks'); ?></li>
                <li><?php esc_html_e('Přístup k technické podpoře', 'integrate-ecomail-bricks'); ?></li>
            </ul>
            <p><?php esc_html_e('Pokud nemáte licen ční klíč, můžete jej zakoupit na', 'integrate-ecomail-bricks'); ?> <a href="https://webypolopate.cz" target="_blank">webypolopate.cz</a>.</p>
        </div>
    </div>
    
    <style>
        .bf-ecomail-license-container {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            max-width: 800px;
        }
        .bf-ecomail-license-status {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f8f8;
            border-left: 4px solid #ccc;
        }
        .bf-ecomail-license-active {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
        }
        .bf-ecomail-license-active .dashicons {
            color: #46b450;
            font-size: 30px;
            margin-right: 10px;
            float: left;
            margin-bottom: 15px;
        }
        .bf-ecomail-license-active p {
            margin-left: 40px;
            margin-top: -25px;
        }
        .bf-ecomail-license-invalid {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
        }
        .bf-ecomail-license-invalid .dashicons {
            color: #dc3232;
            font-size: 30px;
            margin-right: 10px;
            float: left;
            margin-bottom: 15px;
        }
        .bf-ecomail-license-invalid p {
            margin-left: 40px;
            margin-top: -25px;
        }
        .bf-ecomail-license-form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f8f8;
        }
        .bf-ecomail-license-key-input {
            margin-bottom: 15px;
        }
        .bf-ecomail-license-key-input label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .bf-ecomail-license-key-input input {
            width: 100%;
            max-width: 400px;
        }
        .bf-ecomail-license-info {
            margin-bottom: 15px;
        }
        .bf-ecomail-license-info-box {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #00a0d2;
        }
        .bf-ecomail-license-info-box ul {
            margin-left: 20px;
            list-style-type: disc;
        }
    </style>
    <?php
}

/**
 * Registrace nastavení
 */
function bf_ecomail_register_settings() {
    // Registrace nastavení API klíče
    register_setting( 'bf_ecomail_options_group', BF_ECOMAIL_API_OPTION, 'bf_ecomail_validate_api_key' );

    add_settings_section(
        'bf_ecomail_main_section',
        esc_html__( 'Nastavení API klíče', 'integrate-ecomail-bricks' ),
        'bf_ecomail_section_callback',
        'bf-ecomail-settings'
    );

    add_settings_field(
        'bf_ecomail_api_key',
        esc_html__( 'Ecomail API klíč', 'integrate-ecomail-bricks' ),
        'bf_ecomail_api_key_callback',
        'bf-ecomail-settings',
        'bf_ecomail_main_section'
    );
    
    // Přidání nastavení pro cache seznamů
    add_settings_section(
        'bf_ecomail_cache_section',
        esc_html__( 'Nastavení cache', 'integrate-ecomail-bricks' ),
        'bf_ecomail_cache_section_callback',
        'bf-ecomail-settings'
    );
}
add_action( 'admin_init', 'bf_ecomail_register_settings' );

/**
 * Popis sekce API klíče
 */
function bf_ecomail_section_callback() {
    echo '<p>' . esc_html__( 'Zadejte svůj API klíč pro propojení s Ecomail. API klíč najdete v nastavení vašeho účtu Ecomail.', 'integrate-ecomail-bricks' ) . '</p>';
}

/**
 * Popis sekce cache
 */
function bf_ecomail_cache_section_callback() {
    echo '<p>' . esc_html__( 'Seznamy kontaktů jsou automaticky ukládány do cache na 1 hodinu pro zlepšení výkonu. Pokud jste provedli změny v seznamech v Ecomail, můžete vymazat cache a načíst aktuální data.', 'integrate-ecomail-bricks' ) . '</p>';
    
    // Přidání tlačítka pro vymazání cache
    echo '<button type="button" id="bf-ecomail-clear-cache" class="button button-secondary">' . esc_html__( 'Vymazat cache seznamů', 'integrate-ecomail-bricks' ) . '</button>';
    echo '<span id="bf-ecomail-cache-result" style="margin-left: 10px;"></span>';
    
    // Přidání JavaScriptu pro vymazání cache
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#bf-ecomail-clear-cache').on('click', function() {
            var resultSpan = $('#bf-ecomail-cache-result');
            
            resultSpan.html('<span style="color: blue;"><?php echo esc_js( __( 'Vymazávání cache...', 'integrate-ecomail-bricks' ) ); ?></span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bf_ecomail_clear_cache',
                    nonce: '<?php echo wp_create_nonce( 'bf_ecomail_clear_cache_nonce' ); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultSpan.html('<span style="color: green;">' + response.data.message + '</span>');
                    } else {
                        resultSpan.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    resultSpan.html('<span style="color: red;"><?php echo esc_js( __( 'Chyba při komunikaci se serverem', 'integrate-ecomail-bricks' ) ); ?></span>');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Pole pro API klíč
 */
function bf_ecomail_api_key_callback() {
    $api_key = get_option( BF_ECOMAIL_API_OPTION );
    echo '<input type="text" name="' . esc_attr( BF_ECOMAIL_API_OPTION ) . '" value="' . esc_attr( $api_key ) . '" class="regular-text">';
    echo '<p class="description">' . esc_html__( 'Po zadání API klíče se automaticky načtou dostupné seznamy kontaktů.', 'integrate-ecomail-bricks' ) . '</p>';
    
    // Přidání tlačítka pro test API klíče
    echo '<button type="button" id="bf-ecomail-test-api" class="button button-secondary">' . esc_html__( 'Otestovat API klíč', 'integrate-ecomail-bricks' ) . '</button>';
    echo '<span id="bf-ecomail-test-result" style="margin-left: 10px;"></span>';
    
    // Přidání JavaScriptu pro testování API klíče
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#bf-ecomail-test-api').on('click', function() {
            var apiKey = $('input[name="<?php echo esc_attr( BF_ECOMAIL_API_OPTION ); ?>"]').val();
            var resultSpan = $('#bf-ecomail-test-result');
            
            if (!apiKey) {
                resultSpan.html('<span style="color: red;"><?php echo esc_js( __( 'Zadejte API klíč', 'integrate-ecomail-bricks' ) ); ?></span>');
                return;
            }
            
            resultSpan.html('<span style="color: blue;"><?php echo esc_js( __( 'Testování...', 'integrate-ecomail-bricks' ) ); ?></span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bf_ecomail_test_api',
                    api_key: apiKey,
                    nonce: '<?php echo wp_create_nonce( 'bf_ecomail_test_api_nonce' ); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultSpan.html('<span style="color: green;">' + response.data.message + '</span>');
                    } else {
                        resultSpan.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    resultSpan.html('<span style="color: red;"><?php echo esc_js( __( 'Chyba při komunikaci se serverem', 'integrate-ecomail-bricks' ) ); ?></span>');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Ověření API klíče
 *
 * @param string $api_key API klíč.
 * @return string Sanitovaný a ověřený API klíč.
 */
function bf_ecomail_validate_api_key( $api_key ) {
    $api_key = sanitize_text_field( $api_key );

    if ( empty( $api_key ) ) {
        add_settings_error( 'bf_ecomail_api_key', 'empty-api', esc_html__( 'API klíč nemůže být prázdný.', 'integrate-ecomail-bricks' ), 'error' );
        return '';
    }

    $response = wp_remote_get( 'https://api2.ecomailapp.cz/lists', array(
        'headers' => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        add_settings_error( 'bf_ecomail_api_key', 'invalid-api', esc_html__( 'Chyba při komunikaci s Ecomail API. Zkontrolujte připojení.', 'integrate-ecomail-bricks' ), 'error' );
        return get_option( BF_ECOMAIL_API_OPTION );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status_code ) {
        add_settings_error( 'bf_ecomail_api_key', 'invalid-api', esc_html__( 'Neplatný API klíč. Zkontrolujte správnost zadání.', 'integrate-ecomail-bricks' ), 'error' );
        return get_option( BF_ECOMAIL_API_OPTION );
    }

    add_settings_error( 'bf_ecomail_api_key', 'valid-api', esc_html__( 'API klíč byl úspěšně ověřen.', 'integrate-ecomail-bricks' ), 'success' );
    return $api_key;
}

/**
 * AJAX handler pro testování API klíče
 */
function bf_ecomail_test_api_callback() {
    // Ověření nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bf_ecomail_test_api_nonce' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Bezpečnostní ověření selhalo.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Ověření oprávnění
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Nemáte dostatečná oprávnění.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Získání a sanitace API klíče
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'API klíč nemůže být prázdný.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Testování API klíče
    $response = wp_remote_get( 'https://api2.ecomailapp.cz/lists', array(
        'headers' => array(
            'key'          => $api_key,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 10,
    ) );
    
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Chyba při komunikaci s Ecomail API. Zkontrolujte připojení.', 'integrate-ecomail-bricks' ) ) );
    }
    
    $status_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Neplatný API klíč. Zkontrolujte správnost zadání.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Získání počtu seznamů
    $body = wp_remote_retrieve_body( $response );
    $lists = json_decode( $body, true );
    $count = is_array( $lists ) ? count( $lists ) : 0;
    
    // Uložení seznamů do cache
    if (function_exists('bf_ecomail_get_lists')) {
        bf_ecomail_get_lists($api_key, true);
    }
    
    wp_send_json_success( array( 
        'message' => sprintf( 
            esc_html__( 'API klíč je platný. Nalezeno %d seznamů.', 'integrate-ecomail-bricks' ), 
            $count 
        ) 
    ) );
}
add_action( 'wp_ajax_bf_ecomail_test_api', 'bf_ecomail_test_api_callback' );

/**
 * AJAX handler pro vymazání cache
 */
function bf_ecomail_clear_cache_callback() {
    // Ověření nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bf_ecomail_clear_cache_nonce' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Bezpečnostní ověření selhalo.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Ověření oprávnění
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Nemáte dostatečná oprávnění.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Získání API klíče
    $api_key = get_option( BF_ECOMAIL_API_OPTION );
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'API klíč není nastaven.', 'integrate-ecomail-bricks' ) ) );
    }
    
    // Vymazání cache
    if (function_exists('bf_ecomail_clear_lists_cache')) {
        $result = bf_ecomail_clear_lists_cache($api_key);
        
        if ($result) {
            // Načtení aktuálních dat
            if (function_exists('bf_ecomail_get_lists')) {
                $lists = bf_ecomail_get_lists($api_key, true);
                $count = is_array($lists) && !is_wp_error($lists) ? count($lists) : 0;
                
                wp_send_json_success( array( 
                    'message' => sprintf( 
                        esc_html__( 'Cache byla vymazána. Načteno %d aktuálních seznamů.', 'integrate-ecomail-bricks' ), 
                        $count 
                    ) 
                ) );
            } else {
                wp_send_json_success( array( 'message' => esc_html__( 'Cache byla vymazána.', 'integrate-ecomail-bricks' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => esc_html__( 'Cache nemohla být vymazána.', 'integrate-ecomail-bricks' ) ) );
        }
    } else {
        wp_send_json_error( array( 'message' => esc_html__( 'Funkce pro vymazání cache není dostupná.', 'integrate-ecomail-bricks' ) ) );
    }
}
add_action( 'wp_ajax_bf_ecomail_clear_cache', 'bf_ecomail_clear_cache_callback' );