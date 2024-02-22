<?php

function getDataFromTemplate($json) {
    
    $json = $json->objekt;
    $uuid = $json->_uuid;
    
    // Titel
    $titel = '';
    if(isset($json->{"_nested:objekt__objektbezeichnungen"})) {
        foreach($json->{"_nested:objekt__objektbezeichnungen"} as $objektbezeichnung) {
            if(isset($objektbezeichnung->{"_nested:objekt__objektbezeichnungen__objektbezeichnungen"}[0]->objektbezeichnung)) {
                $titelTest = $objektbezeichnung->{"_nested:objekt__objektbezeichnungen__objektbezeichnungen"}[0]->objektbezeichnung;
                if(strpos($titelTest, '|')) {
                    $titel = $titelTest;
                }
            }   
        }
    }

    $beschreibung = '';
    /*
        - Herstellungs-Datierung verbal (kursiv)
        - Hersteller (fett)
        - (Geburtsort Geburtsdatum - Sterbedatum in Sterbeort) (kursiv)
        - Materialien, Maße
        - HAUM, Inv.-Nr. {Inventarnummer}
        - Text
    */
    
    // Events
    if(isset($json->{"_nested:objekt__ereignisse"})) {
        foreach($json->{"_nested:objekt__ereignisse"} as $ereignis) {
            // Herstellungsevent
            if($ereignis->lk_dante_art->conceptURI == 'http://uri.gbv.de/terminology/object_related_event/4a1dbd60-1165-4901-a64b-f145a8ee5949') {
                $beschreibung .= '<!-- wp:paragraph --><p>';
                // hole datierung
                if(isset($ereignis->datierung_verbal)) {
                    $beschreibung .= '<em>' . $ereignis->datierung_verbal . '</em><br />';
                }
                // hole Person
                if(isset($ereignis->{'_nested:objekt__ereignisse__personen_institutionen'})) {
                    foreach($ereignis->{'_nested:objekt__ereignisse__personen_institutionen'} as $person) {
                        if(isset($person->lk_gnd)) {
                            $beschreibung .= '<strong>' . $person->lk_gnd->conceptName . '</strong><br />';
                        }
                    }
                }
                $beschreibung .= '</b><!-- /wp:paragraph -->';
                // hole Materialien
                $beschreibung .= '<!-- wp:paragraph --><p>';
                $materialien = [];
                if(isset($ereignis->{'_nested:objekt__ereignisse__materialien'})) {
                    foreach($ereignis->{'_nested:objekt__ereignisse__materialien'} as $materialen) {
                        foreach($materialen as $material) {
                            if(isset($material->conceptName)) {
                                array_push($materialien, $material->conceptName);
                            }
                        }
                    }
                    if(count($materialien) > 0) {
                        $beschreibung .= '<em>' . implode(', ', $materialien) . '</em>';
                    }
                }
                
                
                // hole Maße
                if(isset($json->{'_nested:objekt__masse'})) {                
                    $breite = '';
                    $hoehe = '';
                    foreach($json->{'_nested:objekt__masse'} as $masse) {
                        foreach($masse->{'_nested:objekt__masse__massangaben'} as $massangabe) {
                            // Breite
                            if($massangabe->lk_dante_dimension->conceptURI == 'http://uri.gbv.de/terminology/dimension/5ace72c7-6320-4ddf-b4ea-e5e0c4c7c80d') {
                                $breite = $massangabe->wert . ' ' . $massangabe->lk_dante_einheit->conceptName;
                            }
                            // Höhe
                            if($massangabe->lk_dante_dimension->conceptURI == 'http://uri.gbv.de/terminology/dimension/bdcdfed8-b805-4121-98ee-a78041b12b7a') {
                                $hoehe = $massangabe->wert;
                            }
                        }
                    }
                    if($breite && $hoehe) {
                        $trenner = '';
                        if(count($materialien) > 0) {
                            $trenner = ', ';
                        }
                        $beschreibung .= '<em>'. $trenner . $hoehe . ' x ' . $breite . '</em><br />';
                    }
                }
                
                // Inventarnummer
               if(isset($json->{'_nested:objekt__inventarnummern'})) {                
                    $inventarnummer = '';
                    foreach($json->{'_nested:objekt__inventarnummern'} as $inventarnummer) {
                        if($inventarnummer->lk_dante_art->conceptURI == 'http://uri.gbv.de/terminology/signature_type/9fb50efc-bf7f-42cf-a52c-7edca1decb70') {
                            $beschreibung .= '<em>' . $inventarnummer->anmerkung_intern . ' ' . $inventarnummer->inventarnummer . '</em><br /><br />';
                        }
                    }
                }
                $beschreibung .= '</b><!-- /wp:paragraph -->';
                break;
            }
        }
    }
    
    // Beschreibungstexte
    $beschreibungDE = '';
    $beschreibungEN = '';
    if(isset($json->{"_nested:objekt__objektbeschreibungen"})) {
        foreach($json->{"_nested:objekt__objektbeschreibungen"} as $objektbeschreibungen) {
            foreach($objektbeschreibungen->{"_nested:objekt__objektbeschreibungen__texte"} as $obtext) {
                if($obtext->lk_dante_sprache->conceptURI == 'http://id.loc.gov/vocabulary/iso639-2/deu') {
                    $beschreibungDE = '<em>' . $obtext->text . '</em>';
                }
                if($obtext->lk_dante_sprache->conceptURI == 'http://id.loc.gov/vocabulary/iso639-2/eng') {
                    $beschreibungEN = '<em>' . $obtext->text . '</em>';
                }
            }
        }
    }   
    $beschreibung .= '<!-- wp:paragraph --><p>';
    $beschreibung .= $beschreibungDE;
    $beschreibung .= '</b><!-- /wp:paragraph -->';
    
    $beschreibung .= '<!-- wp:paragraph {"className":"english-white"} --><p class="english-white">';
    $beschreibung .= $beschreibungEN;
    $beschreibung .= '</p><!-- /wp:paragraph -->';
    
    // Bild
    $bildUrl = '';
    if(isset($json->{"_nested:objekt__bilder_publik"}[0]->bild[0]->versions->huge->url)) {
        $bildUrl = $json->{"_nested:objekt__bilder_publik"}[0]->bild[0]->versions->huge->url;
    }

    // Bildhash
    $bildHash = '';
    if(isset($json->{"_nested:objekt__bilder_publik"}[0]->bild[0]->versions->huge->hash)) {
        $bildHash = $json->{"_nested:objekt__bilder_publik"}[0]->bild[0]->versions->huge->hash;
    }

    $api_data = array(
        'post_title' => ($titel),
        'post_content' => $beschreibung,
        'post_image' => $bildUrl,
        'post_image_hash' => $bildHash
    );
    return $api_data;
}
?>