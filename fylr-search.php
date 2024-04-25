<?php

// Funktion zum Anzeigen des Suchformulars
function fylr_search_form($search_query, $aggregations) {
    
    $translations = json_decode(getFylrTranslations());
    $translations_tags = json_decode(getFylrTags());
    
    // include css
    $plugin_dir = plugin_dir_path(__FILE__);
    $cssPath = 'css/default.search.css';
    if(file_exists($plugin_dir . $cssPath)) {
        $css = file_get_contents($plugin_dir . $cssPath);
        echo '<style type="text/css">' . $css . '</style>';
    }
    
    // Extrahieren Sie die Facetteninformationen aus Ihrer API-Antwort
    
    // Anzeigen der Facetten im Widget
    echo '<div class="fylr-facets">';
    foreach ($aggregations as $facetName=>$facet) {
        echo '<div data-facetfield="' . $facetName . '" class="fylr-facet">';
        
        $translatedFacetName = substr($facetName, strrpos($facetName, "_nested:") + 8);
        if (substr($translatedFacetName, -10) === ".facetTerm") {
            $translatedFacetName = substr($translatedFacetName, 0, -10);
        }
        if (substr($translatedFacetName, -12) === ".conceptName") {
            $translatedFacetName = substr($translatedFacetName, 0, -12);
        }
        
        if(isset($translations->$translatedFacetName)) {
            $translatedFacetName = $translations->$translatedFacetName;
        }
        if($translatedFacetName == '') {
            $translatedFacetName = $facetName;
        }
        
        if($translatedFacetName == '_tags') {
            $translatedFacetName = 'Tags';
        }
        
        if($translatedFacetName == '_pool') {
            $translatedFacetName = 'Bereich';
        }
        
        echo '<h4>' . $translatedFacetName . '</h4>';
        
        
        // linked objects
        if(isset($facet->linked_objects)) {    
            foreach($facet->linked_objects as $linked_object) {
                $facetLabel = '';
                if(isset($linked_object->_path)) {
                    foreach($linked_object->_path as $pathElem) {
                        $facetLabel = $facetLabel . ' → ' . $pathElem->text;
                    }
                    $facetLabel .= '(' . $linked_object->count . ')';
                }
                $checkboxStatus = '';
                if(is_facet_in_url($facetName, $term->term)) {
                    $checkboxStatus = 'checked';
                }
                $checkboxURL = generate_facet_toggle_link($facetName, $term->term);
                echo '<div class="fylr-facet" data-facetfield="' . $facetName . '" data-facetvalue="' . $linked_object->_id . '"><div class="checkbox ' . $checkboxStatus . '" onclick="this.classList.toggle(\'checked\')"><a href="' . $checkboxURL . '" target="_self"></a></div>' . $facetLabel . '</div>';
            }
        }
        
        // tags
        elseif($facetName == '_tags') {
            foreach($facet->terms as $term) {
                $facetLabel = $term->term;
                
                $facetLabel = $translations_tags->$facetLabel;
                    
                $checkboxStatus = '';
                if(is_facet_in_url($facetName, $term->term)) {
                    $checkboxStatus = 'checked';
                }
                $checkboxURL = generate_facet_toggle_link($facetName, $term->term);
                $facetLabel .= '(' . $term->count . ')';
                echo '<div class="fylr-facet" data-facetfield="' . $facetName . '" data-facetvalue="' . $term->term . '"><div class="checkbox ' . $checkboxStatus . '" onclick="this.classList.toggle(\'checked\')"><a href="' . $checkboxURL . '" target="_self"></a></div>' . $facetLabel . '</div>';
            }
        }
        
        // terms
        elseif(isset($facet->terms)) {
            foreach($facet->terms as $term) {
                $facetLabel = $term->term;
                
                // bei DANTE-Feldern die URI extrahieren und abschneiden
                if(strpos($term->term, '@$@') !== false) {
                    $facetLabelParts = explode('@$@', $term->term);
                    $facetLabel = $facetLabelParts[0];
                }
                    
                $checkboxStatus = '';
                if(is_facet_in_url($facetName, $term->term)) {
                    $checkboxStatus = 'checked';
                }
                $checkboxURL = generate_facet_toggle_link($facetName, $term->term);
                $facetLabel .= '(' . $term->count . ')';
                echo '<div class="fylr-facet" data-facetfield="' . $facetName . '" data-facetvalue="' . $term->term . '"><div class="checkbox ' . $checkboxStatus . '" onclick="this.classList.toggle(\'checked\')"><a href="' . $checkboxURL . '" target="_self"></a></div>' . $facetLabel . '</div>';
            }
        }
        echo '</div>';    
    }
    echo '</div>';
    
    // Generiere das Suchformular
    $search_form = '
    <div class="search-container">
        <form method="get" action="' . esc_url(get_permalink()) . '">
            <input type="text" name="fylr_search" placeholder="Suche nach..." value="' . $search_query . '" />
            <button type="submit">Suchen</button>
        </form>
    </div>';

    // Rückgabe des Suchformulars
    return $search_form;
}

// Funktion zum Durchführen der Suche und Anzeigen der Ergebnisse
function fylr_search_posts_by_uuid() {
    // Verarbeite die Suchanfrage und zeige die Suchergebnisse an
    
    // searchstring
    if (isset($_GET['fylr_search']) && !empty($_GET['fylr_search'])) {
        $search_query = $_GET['fylr_search'];
    } else {
        $search_query = '*';
    }
    
    // aggregations for ui
    if (isset($_GET['fylr_facets']) && !empty($_GET['fylr_facets'])) {
        $search_facets = $_GET['fylr_facets'];
    }

    // Durchsuche die Fylr-API mit der Suchanfrage
    $apiUrl = get_option('fylr_integration_api_url');
    $username = get_option('fylr_integration_username');        
    $password = get_option('fylr_integration_password');
    $clientID = get_option('fylr_integration_clientid');
    $clientSecret = get_option('fylr_integration_clientsecret');
    $objecttype = get_option('fylr_integration_objecttype');

    // Überprüfe, ob alle benötigten Einstellungen vorhanden sind
    if (empty($apiUrl) || empty($username) || empty($password) || empty($clientID) || empty($clientSecret) || empty($objecttype)) {
        return 'Fylr-Integration: Fehlende Plugin-Konfiguration. Bitte stellen Sie sicher, dass alle Einstellungen korrekt eingegeben wurden.';
    }

    // liste aller vorhandenen fylr-uuids in wordpress
    $uuidList = json_encode(get_all_fylr_uuids());

    // aggregations for fylr-search-query
    $aggregations = new stdClass();
    if(isset($GLOBALS['fylr_search_aggregations'])) {
        if($GLOBALS['fylr_search_aggregations']) {
            if(is_array($GLOBALS['fylr_search_aggregations'])){
                if(count($GLOBALS['fylr_search_aggregations']) > 0) {
                    foreach($GLOBALS['fylr_search_aggregations'] as $aggField) {
                        if($aggField == '_pool') {
                            $aggregation = '{ "type": "linked_object", "field": "_pool", "limit": 10, "sort": "count"}';    
                        }
                        elseif($aggField == '_tags') {
                            $aggregation = '{ "type": "term", "field": "_tags._id", "limit": 10, "sort": "count"}';    
                        } else {
                            $aggregation = '{ "type": "term", "field": "' . $aggField . '", "limit": 10, "sort": "count"}';    
                        }
                    $aggregations->$aggField = json_decode($aggregation);
                    }
                }
            }
        }
        $aggregations = json_encode($aggregations);
    }

    // baue fylr-query       
    $wildcardQueryPart = '';
    if($search_query != '*') {
        $wildcardQueryPart = '{ "type": "complex", "search": [ { "type": "match", "mode": "wildcard", "string": "' . $search_query . '*", "phrase": false, "bool": "must" } ] },';
    }
    
    // Extrahiere die Facetten aus der aktuellen URL
    $facets = get_facets_from_url();

    // Generiere das JSON für die Suche basierend auf den Facetten
    $filterQuerys = generate_search_json_for_facets($facets);
    
    $fylr_query = '{ "offset": 0, "limit": 1000, "aggregations" : ' . $aggregations . ', "search": [ ' . $wildcardQueryPart . ' { "type": "complex", "search": [ { "type": "in", "in": ' . $uuidList . ', "fields": ["_uuid"], "bool": "must" } ] } ], "format": "long", "objecttypes": [ "' . $objecttype . '" ] }';
    
    $fylr_query = json_decode($fylr_query);
    foreach($filterQuerys as $filterQuery) {
        array_push($fylr_query->search, $filterQuery);        
    }
    $fylr_query = json_encode($fylr_query);
    
    // Login an fylr
    $fylrApiKey = getApiKey($apiUrl, $username, $password, $clientID, $clientSecret);
    // search in fylr
    $fylr_api_response = sendRequest($apiUrl, '/api/v1/search', 'access_token=' . $fylrApiKey, $fylr_query, POST);     
    $aggregationsResult = $fylr_api_response->aggregations;

    // Extrahiere die UUIDs aus der API-Antwort
    $uuids = [];
    if($fylr_api_response) {
        if($fylr_api_response->count > 0) {
            foreach($fylr_api_response->objects as $object) {
                array_push($uuids, $object->_uuid);
            }
        }
    }

    // Durchsuche die WordPress-Beiträge nach den gefundenen UUIDs
    $args = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'fylr_external_uuid',
                'value' => $uuids,
                'compare' => 'IN',
            ),
        ),
    );
    $search_results = new WP_Query($args);

    // Zeige die Suchergebnisse an
    ob_start(); // Starte den Output-Buffer
    if ($search_results->have_posts()) {
        echo fylr_search_form($search_query, $aggregationsResult); // Zeige das Suchformular an
        echo '<div class="fylr-search-results">'; // Container für Suchergebnisse
        while ($search_results->have_posts()) {
            $search_results->the_post();
            // Hier kannst du die gefundenen Beiträge anzeigen, z.B. den Titel, das Bild und den Auszug
            echo '<div class="fylr-search-result">';
            // Titel mit Link zum Beitrag
            echo '<h2 class="title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
            // Beitragsbild mit Link zum Beitrag
            if (has_post_thumbnail()) {
                echo '<div class="thumbnail"><a href="' . get_permalink() . '">' . get_the_post_thumbnail() . '</a></div>';
            }
            // Auszug
            echo '<div class="content">';
            the_excerpt();
            echo '</div>';
            echo '</div>';
        }
        echo '</div>'; // Schließt .search-results
        wp_reset_postdata();
    } else {
        echo '<p class="no-results">Keine Ergebnisse gefunden.</p>';
    }
}

function get_all_fylr_uuids() {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Abrufen aller Beiträge
        'meta_key' => 'fylr_external_uuid', // Metaschlüssel für die fylr-UUID
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'fylr_external_uuid',
                'compare' => 'EXISTS', // Stellt sicher, dass der Metaschlüssel existiert
            ),
            array(
                'key' => 'fylr_external_uuid',
                'value' => '', // Überprüfe, ob der Wert nicht leer ist
                'compare' => '!=',
            ),
        ),
    );

    $query = new WP_Query($args);
    $uuids = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $fylr_uuid = get_post_meta(get_the_ID(), 'fylr_external_uuid', true);
            if (!empty($fylr_uuid)) {
                $uuids[] = $fylr_uuid;
            }
        }
        wp_reset_postdata();
    }

    return $uuids;
}

?>