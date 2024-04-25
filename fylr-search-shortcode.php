<?php
// Funktion zum Registrieren des Shortcodes
function fylr_search_register_shortcodes() {
    add_shortcode('fylr_search', 'fylr_search_posts_by_uuid');
}

// Kurzcode registrieren
add_action('init', 'fylr_search_register_shortcodes');

?>