<?php

/*
Plugin Name: Fylr-Integration
Description: Use fylr-data in wordpress-instances
Version: 1.0
*/

require_once('fylr-request.php');
require_once('fylr-getapikey.php');

// Register settings
function fylr_integration_register_settings() {
    add_option('fylr_integration_api_url', '');
    add_option('fylr_integration_username', '');
    add_option('fylr_integration_password', '');
    add_option('fylr_integration_clientid', '');
    add_option('fylr_integration_clientsecret', '');
    add_option('fylr_integration_template', '');
    add_option('fylr_integration_template_file', '');

    register_setting('fylr_integration_options_group', 'fylr_integration_api_url');
    register_setting('fylr_integration_options_group', 'fylr_integration_username');
    register_setting('fylr_integration_options_group', 'fylr_integration_password');
    register_setting('fylr_integration_options_group', 'fylr_integration_clientid');
    register_setting('fylr_integration_options_group', 'fylr_integration_clientsecret');
    register_setting('fylr_integration_options_group', 'fylr_integration_template');
    register_setting('fylr_integration_options_group', 'fylr_integration_template_file');
}

add_action('admin_init', 'fylr_integration_register_settings');

// Add settings page
function fylr_integration_add_settings_page() {
    add_options_page('Fylr Integration Settings', 'Fylr Integration Settings', 'manage_options', 'fylr_integration_settings', 'fylr_integration_render_settings_page');
}

add_action('admin_menu', 'fylr_integration_add_settings_page');


// Render settings page
function fylr_integration_render_settings_page() {
    // Holen Sie sich die Liste der Template-Dateien im Plugin-Verzeichnis
    $templates = array();
    $plugin_dir = plugin_dir_path(__FILE__);
    $files = scandir($plugin_dir . '/templates');
    foreach ($files as $file) {
        if (strpos($file, '.template.php') !== false) {
            $template_name = str_replace('.template.php', '', $file);
            $templates[$template_name] = $template_name;
        }
    }
    ?>
    <div class="wrap">
        <h2>Fylr Integration Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('fylr_integration_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Fylr-URL:</th>
                    <td><input type="text" name="fylr_integration_api_url" value="<?php echo esc_attr(get_option('fylr_integration_api_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Username:</th>
                    <td><input type="text" name="fylr_integration_username" value="<?php echo esc_attr(get_option('fylr_integration_username')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Password:</th>
                    <td><input type="password" name="fylr_integration_password" value="<?php echo esc_attr(get_option('fylr_integration_password')); ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">ClientID:</th>
                    <td><input type="text" name="fylr_integration_clientid" value="<?php echo esc_attr(get_option('fylr_integration_clientid')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">ClientSecret:</th>
                    <td><input type="text" name="fylr_integration_clientsecret" value="<?php echo esc_attr(get_option('fylr_integration_clientsecret')); ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Select Template:</th>
                    <td>
                        <select name="fylr_integration_template_file">
                                <option value=""></option>
                            <?php foreach ($templates as $template) : ?>
                                <option value="<?php echo esc_attr($template); ?>" <?php selected(get_option('fylr_integration_template_file'), $template); ?>><?php echo esc_html($template); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">OR Enter Template:</th>
                    <td>
                        <textarea name="fylr_integration_template" rows="20" cols="140"><?php echo esc_html(get_option('fylr_integration_template')); ?></textarea>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Hook in die Funktion, um die Metabox hinzuzufügen
add_action('add_meta_boxes', 'fylr_external_uuid_meta_box');

function fylr_external_uuid_meta_box() {
    add_meta_box(
        'fylr_external_uuid_meta_box', // Metabox-ID
        'Fylr UUID', // Metabox-Titel
        'fylr_external_uuid_meta_box_callback', // Callback-Funktion zum Anzeigen der Metabox-Inhalte
        'post', // Post-Typ, auf den die Metabox angewendet wird
        'side', // Position der Metabox (z.B. 'normal', 'side', 'advanced')
        'high' // Priorität der Metabox (z.B. 'high', 'low')
    );
}

// Callback-Funktion zum Anzeigen der Metabox-Inhalte
function fylr_external_uuid_meta_box_callback($post) {
    // Hole gespeicherte externe ID für den aktuellen Beitrag
    $external_fylr_uuid = get_post_meta($post->ID, 'fylr_external_uuid', true);

    // Ausgabe des Eingabefelds für die externe ID
    echo '<label for="fylr_external_uuid">Fylr UUID:</label>';
    echo '<input type="text" id="fylr_external_uuid" name="fylr_external_uuid" value="' . esc_attr($external_fylr_uuid) . '" />';
}

// Hook in die Funktion, um die gespeicherte externe ID zu speichern
add_action('save_post', 'save_fylr_external_uuid');

function save_fylr_external_uuid($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; // Wenn Autosave läuft, abbrechen

    // Überprüfe, ob der Benutzer die Berechtigung zum Speichern hat
    if (!current_user_can('edit_post', $post_id)) return;

    // Überprüfe, ob die externe ID gesetzt ist, und speichere sie
    if (isset($_POST['fylr_external_uuid'])) {
        update_post_meta($post_id, 'fylr_external_uuid', sanitize_text_field($_POST['fylr_external_uuid']));
    }
}

// Hook in die Funktion, um den Beitrag mit Daten von der API zu aktualisieren
add_action('wp', 'update_post_with_api_data');

function update_post_with_api_data() {
    global $post;
    if (!is_admin() && is_singular('post')) { // Stelle sicher, dass der Code nur auf Einzelbeitragsseiten ausgeführt wird
        $external_fylr_uuid = get_post_meta($post->ID, 'fylr_external_uuid', true);
        if ($external_fylr_uuid) {
            $api_data = custom_get_api_data($external_fylr_uuid); // Funktion, um Daten von der API abzurufen
            if ($api_data) {
                $post_args = array(
                    'ID'           => $post->ID,
                    'post_title'   => $api_data['post_title'], // Annahme, dass die API-Titeldaten hier verfügbar sind
                    'post_content' => $api_data['post_content'], // Annahme, dass die API-Beschreibung hier verfügbar sind
                    // Du kannst hier weitere Felder aktualisieren (post_excerpt, post_author, etc.)
                );
                // Aktualisiere den Beitrag
                wp_update_post($post_args);

                // Setze das Beitragsbild, wenn verfügbar
                if (!empty($api_data['post_image']) && !empty($api_data['post_image_hash'])) {
                    custom_set_post_featured_image($post->ID, $api_data['post_title'], $api_data['post_image'], $api_data['post_image_hash']);
                }
            }
        }
    }
}

// Funktion, um Daten von der externen API abzurufen
function custom_get_api_data($external_fylr_uuid) {

    $apiUrl = get_option('fylr_integration_api_url');
    $username = get_option('fylr_integration_username');
    $password = get_option('fylr_integration_password');
    $clientID = get_option('fylr_integration_clientid');
    $clientSecret = get_option('fylr_integration_clientsecret');
    $template = get_option('fylr_integration_template');
    $templateFile = get_option('fylr_integration_template_file');

    if (empty($apiUrl) || empty($username) || empty($password) || empty($clientID) || empty($clientSecret) || empty($template)) {
        return 'API URL, Username, Password, ClientID, ClientSecret and Template are required.';
    }
    
    // Login an fylr
    $fylrApiKey = getApiKey($apiUrl, $username, $password, $clientID, $clientSecret);

    // get record
    $uuid = $external_fylr_uuid; // ID des Kastens aus dem Shortcode-Attribut
    
    $json = sendRequest($apiUrl, '/api/v1/objects/uuid/' . $uuid . '/latest', 'access_token=' . $fylrApiKey, $data, GET);
    
    $api_data = [];
    
    if($templateFile) {
        // pass data to template
        include('templates/' . $templateFile . '.template.php');

        $api_data = getDataFromTemplate($json);
    }
    else {
        $api_data = execute_template_code($template, $json);    
    }
    
    return $api_data;
}

// Funktion zum Ausführen des Template-Codes und Abrufen der Daten
function execute_template_code($template_code, $json_data) {
    // Erstelle temporäre Datei mit dem Template-Code
    $temp_template_file = plugin_dir_path( __FILE__ ) . 'cache//custom_template_' . md5($template_code) . '.php';
    file_put_contents($temp_template_file, $template_code);
    
    // Führe den PHP-Code im Template aus
    include($temp_template_file);
    $api_data = getDataFromTemplate($json_data);
    // Lösche die temporäre Datei
    unlink($temp_template_file);

    // Rückgabe der Daten aus dem Template
    return $api_data;
}

// Funktion zum Festlegen des Beitragsbilds
function custom_set_post_featured_image($post_id, $title, $image_url, $image_hash) {
    // Überprüfe, ob das Bild bereits in der Mediathek vorhanden ist
    $existing_attachment = get_posts(array(
        'post_type' => 'attachment',
        'meta_query' => array(
            array(
                'key' => 'image_md5_hash', // Meta-Key für den MD5-Hash des Bildes
                'value' => $image_hash,
            ),
        ),
        'posts_per_page' => 1,
    ));

    if ($existing_attachment) {
        // Wenn das Bild bereits in der Mediathek vorhanden ist, setze es als Beitragsbild
        $attach_id = $existing_attachment[0]->ID;
    } else {
        // Wenn das Bild nicht in der Mediathek vorhanden ist, lade es hoch und speichere den MD5-Hash als Metadaten
        $attach_id = custom_upload_image($title, $image_url, $image_hash);
        update_post_meta($attach_id, 'image_md5_hash', $image_hash);
    }

    // Setze das Beitragsbild
    update_post_meta($post_id, '_thumbnail_id', $attach_id);
}

// Funktion zum Hochladen des Bildes in die Mediathek
function custom_upload_image($title, $image_url, $image_hash) {
    // Lade das Bild herunter
    $image_name = basename(parse_url($image_url, PHP_URL_PATH)); // Extrahiere den Dateinamen aus der URL
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);

    // Generiere den Dateinamen aus dem Titel
    $file_extension = pathinfo($image_name, PATHINFO_EXTENSION);
    $filename = $image_hash . '.' . $file_extension;

    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents($file, $image_data);

    // Dateianhang erstellen
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $file);
    
    // Generiere das Vorschaubild
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

?>