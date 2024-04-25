<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Erstellen Sie eine Klasse für Ihr Elementor-Widget
class Fylr_Search_Results_Widget extends \Elementor\Widget_Base {

    // Definieren Sie eine eindeutige Bezeichnung für Ihr Widget
    public function get_name() {
        return 'fylr-search-results-widget';
    }

    // Geben Sie den Titel Ihres Widgets an
    public function get_title() {
        return __('Fylr Search Results', 'fylr-integration');
    }

    // Definieren Sie eine Beschreibung für Ihr Widget
    public function get_description() {
        return __('Displays search results from Fylr API.', 'fylr-integration');
    }

    // Geben Sie die Kategorien an, unter denen Ihr Widget in der Elementor-UI angezeigt wird
    public function get_categories() {
        return ['general'];
    }

    // Definieren Sie die Inhalte und Optionen Ihres Widgets
    protected function _register_controls() {
        include('elementor-search-widget.controls.inc.php');
    }

    // Funktion zum Rendern Ihres Widgets auf der Website
    protected function render() {
        // Holen Sie die Einstellungen aus den WordPress-Optionen
        $apiUrl = get_option('fylr_integration_api_url');
        $username = get_option('fylr_integration_username');
        $password = get_option('fylr_integration_password');
        $clientID = get_option('fylr_integration_clientid');
        $clientSecret = get_option('fylr_integration_clientsecret');
        $objectType = get_option('fylr_integration_objecttype');
        $templateFile = get_option('fylr_integration_template_file');

        // Überprüfen Sie, ob alle erforderlichen Einstellungen vorhanden sind
        if (empty($apiUrl) || empty($username) || empty($password) || empty($clientID) || empty($clientSecret) || empty($objectType) || empty($templateFile)) {
            echo 'Bitte konfigurieren Sie alle Einstellungen in den Fylr-Integrationseinstellungen.';
            return;
        }
        
        $settings = $this->get_settings_for_display();
        
        // CSS-Styles zusammenstellen
        $style = '<style>';
        $style .= '.fylr-search-results { display: flex; flex-wrap: wrap; margin: 0; padding: 0; }';
        $style .= '.fylr-search-results .fylr-search-result { flex: 0 0 calc(' . (100 / $settings['columns']) . '% - ' . $settings['column_spacing_right']['size'] . $settings['column_spacing_right']['unit'] . '); margin-bottom: ' . $settings['column_spacing_bottom']['size'] . $settings['column_spacing_bottom']['unit'] . ';  margin-right: ' . $settings['column_spacing_right']['size'] . $settings['column_spacing_right']['unit'] . '; }';
        $style .= '.fylr-search-results .fylr-search-result .title, .fylr-search-results .fylr-search-result .title a { display: ' . ($settings['show_title'] === 'yes' ? 'block' : 'none') . '; line-height: ' . $settings['title_line_height']['size'] . $settings['title_line_height']['unit'] . '; font-weight: ' . $settings['title_font_weight'] . '; color: ' . $settings['title_color'] .'; font-size: ' . $settings['title_font_size']['size'] . $settings['title_font_size']['unit'] . '; }';
        $style .= '.fylr-search-results .fylr-search-result .content { display: ' . ($settings['show_content'] === 'yes' ? 'block' : 'none') . '; line-height: ' . $settings['content_line_height']['size'] . $settings['content_line_height']['unit'] . '; font-weight: ' . $settings['content_font_weight'] . '; color: ' . $settings['content_color'] .'; font-size: ' . $settings['content_font_size']['size'] . $settings['content_font_size']['unit'] . '; }';
        $style .= '.fylr-search-results .fylr-search-result .thumbnail img { border-radius: ' . $settings['image_border-radius']['size'] . 'px; width: ' . $settings['image_width_percentage']['size'] . '%; display: ' . ($settings['show_image'] === 'yes' ? 'block' : 'none') . '; ' . $image_width_style . ' }';
        $style .= '</style>';
        
        echo $style;

        // Code, der die Suchergebnisse abruft und anzeigt
        $search_results = fylr_search_posts_by_uuid(); // Funktion zum Abrufen der Suchergebnisse
        echo $search_results; // Ausgabe der Suchergebnisse
    }
}

// Registrieren Sie Ihr Widget, sodass Elementor es verwenden kann
function register_fylr_search_results_widget() {
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Fylr_Search_Results_Widget());
}
add_action('elementor/widgets/widgets_registered', 'register_fylr_search_results_widget');
