<?php

    function generate_facet_toggle_link($facet_name, $facet_value) {
        // Zerlegt die aktuelle URL in ihre Bestandteile
        $url_components = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url_components['query'] ?? '', $params);

        // Der Schlüssel für Facetten im Query-String
        $facet_key = 'fylr_facets';

        // Prüft, ob die Facette bereits existiert
        $existing_facets = array();
        if(isset($params[$facet_key])) {
            // Trennt die Facetten in ein Array
            $existing_facets = explode(';', $params[$facet_key]);
        }

        // Baut die Facetten-Identifikation
        $facet_identification = "$facet_name=$facet_value";

        // Sucht nach der Facette im aktuellen Facetten-Array
        $facet_index = array_search($facet_identification, $existing_facets);

        if($facet_index !== false) {
            // Facette existiert, also entferne sie
            unset($existing_facets[$facet_index]);
        } else {
            // Facette existiert nicht, also füge sie hinzu
            $existing_facets[] = $facet_identification;
        }

        // Aktualisiert den fylr_facets-Parameter in den URL-Parametern
        if(count($existing_facets) > 0) {
            $params[$facet_key] = implode(';', $existing_facets);
        } else {
            unset($params[$facet_key]);
        }

        // Baut die neue URL zusammen
        $new_query = http_build_query($params);
        $new_url = $url_components['path'] . '?' . $new_query;

        return $new_url;
    }

    function is_facet_in_url($facet_name, $facet_value) {
        // Zerlegt die aktuelle URL in ihre Bestandteile
        $url_components = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url_components['query'] ?? '', $params);

        // Der Schlüssel für Facetten im Query-String
        $facet_key = 'fylr_facets';

        // Prüft, ob die Facette bereits existiert
        if(isset($params[$facet_key])) {
            // Trennt die Facetten in ein Array
            $existing_facets = explode(';', $params[$facet_key]);
            $facet_identification = "$facet_name=$facet_value";

            // Sucht nach der Facette im aktuellen Facetten-Array
            return in_array($facet_identification, $existing_facets);
        }

        return false;
    }

    function get_facets_from_url() {
        // Verwendet $_SERVER['REQUEST_URI'], um die aktuelle URL zu bekommen
        $url_components = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url_components['query'] ?? '', $params);

        // Der Schlüssel für Facetten im Query-String
        $facet_key = 'fylr_facets';
        $facets = [];

        if (isset($params[$facet_key])) {
            // Trennt die Facetten in ein Array, basierend auf dem Semikolon als Trennzeichen
            $facetsString = $params[$facet_key];
            $facets = explode(';', $facetsString);
        }

        return $facets;
    }


    function generate_search_json_for_facets($facets) {
        $searches = [];

        foreach ($facets as $facet) {
            // Zerlege die Facette in ihren Namen und Wert
            list($facetName, $facetValue) = explode('=', $facet);

            // Generiere das JSON für die aktuelle Facette
            $search = [
                "__filter" => "Facet",
                "type" => "complex",
                "bool" => "must",
                "search" => [
                    [
                        "bool" => "must",
                        "fields" => [
                            "$facetName"
                        ],
                        "type" => "in",
                        "in" => [
                            "$facetValue"
                        ],
                        "__filter" => "Facet"
                    ]
                ]
            ];

            $searches[] = $search;
        }

        return $searches;
    }

?>