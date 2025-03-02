<?php
/**
 * Nastavení administrace pluginu
 */

if (!defined('ABSPATH')) {
    exit; // Zabránění přímému přístupu
}

// Přidání stránky do administrace
function bf_ecomail_add_admin_menu() {
    add_options_page(
        'Bricks Form - Ecomail', // Název stránky
        'Bricks Form - Ecomail', // Název v menu
        'manage_options', // Oprávnění
        'bf-ecomail-settings', // Slug
        'bf_ecomail_settings_page' // Callback pro vykreslení stránky
    );
}
add_action('admin_menu', 'bf_ecomail_add_admin_menu');

// Vykreslení administrační stránky
function bf_ecomail_settings_page() {
    ?>
    <div class="wrap">
        <h1>Bricks Form - Ecomail Integrace</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('bf_ecomail_options_group');
            do_settings_sections('bf-ecomail-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registrace nastavení
function bf_ecomail_register_settings() {
    register_setting('bf_ecomail_options_group', BF_ECOMAIL_API_OPTION, 'bf_ecomail_validate_api_key');

    add_settings_section(
        'bf_ecomail_main_section',
        'Nastavení API klíče',
        'bf_ecomail_section_callback',
        'bf-ecomail-settings'
    );

    add_settings_field(
        'bf_ecomail_api_key',
        'Ecomail API klíč',
        'bf_ecomail_api_key_callback',
        'bf-ecomail-settings',
        'bf_ecomail_main_section'
    );
}
add_action('admin_init', 'bf_ecomail_register_settings');

// Popis sekce
function bf_ecomail_section_callback() {
    echo '<p>Zadejte svůj API klíč pro propojení s Ecomail.</p>';
}

// Pole pro API klíč
function bf_ecomail_api_key_callback() {
    $api_key = get_option(BF_ECOMAIL_API_OPTION);
    echo '<input type="text" name="'. BF_ECOMAIL_API_OPTION .'" value="' . esc_attr($api_key) . '" class="regular-text">';
}

// Ověření API klíče
function bf_ecomail_validate_api_key($api_key) {
    $response = wp_remote_get('https://api2.ecomailapp.cz/lists', [
        'headers' => [
            'key' => $api_key,
            'Content-Type' => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        add_settings_error('bf_ecomail_api_key', 'invalid-api', 'Chyba při komunikaci s Ecomail API. Zkontrolujte připojení.', 'error');
        return get_option(BF_ECOMAIL_API_OPTION);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        add_settings_error('bf_ecomail_api_key', 'invalid-api', 'Neplatný API klíč. Zkontrolujte správnost zadání.', 'error');
        return get_option(BF_ECOMAIL_API_OPTION);
    }

    return $api_key;
}