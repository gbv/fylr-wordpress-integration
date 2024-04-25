<?php

//$GLOBALS['fylr_search_aggregations'] = ['_pool', '_tags', 'objekt._nested:objekt__ereignisse.lk_dante_art.facetTerm', 'objekt._nested:objekt__oberbegriffsdatei.lk_dante.facetTerm', 'objekt._nested:objekt__sachgruppen.lk_dante.facetTerm'];

/*
$GLOBALS['fylr_search_aggregations'] = [
    'objekt._nested:objekt__ereignisse._nested:objekt__ereignisse__personen_institutionen.lk_gnd.conceptName',
    'objekt._nested:objekt__ereignisse._nested:objekt__ereignisse__materialien.lk_dante.facetTerm',
    'objekt._nested:objekt__ereignisse._nested:objekt__ereignisse__techniken.lk_dante.facetTerm',
    'objekt._nested:objekt__oberbegriffsdatei.lk_dante.facetTerm',
    'objekt._nested:objekt__sachgruppen.lk_dante.facetTerm'
];
*/

function makeWpBlockColumns($label, $value) {
    $html = '<div class="wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex" style="margin-bottom: 0px;">
            <div class="wp-block-column is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:40%">
            <p><strong>' . $label . ':</strong></p>
            </div>
            <div class="wp-block-column is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:60%">
            <p>' . $value . '</p>
            </div>
            </div>';
    return $html;
}

function getDataFromTemplate($json) {
    
    $json = $json->viamus_abguss;
    $uuid = $json->_uuid;
    
    // Titel 
    $titel = '';
    if(isset($json->kurzbezeichnung)) {
        if (trim($json->kurzbezeichnung) != '') {
            $titel = $json->kurzbezeichnung;
        }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // PANEL: ALLGEMEINE ANGABEN
    ////////////////////////////////////////////////////////////////////////////
    
    $panelHTML = '<br /><br /><h5 style="margin-top: 46px;" class="wp-block-heading"><strong>Allgemeine Angaben</strong><br></h5>';
    $foundAllgemeineAngaben = false;
    
    // inventarnummer
    $inventarnummer = '';
    if(isset($json->inventarnummer)) {
        if (trim($json->inventarnummer) != '') {
            $panelHTML .= makeWpBlockColumns('Inventarnummer', $json->inventarnummer);
            $foundAllgemeineAngaben = true;
        }
    }
    // Kurzbezeichnung
    if(isset($json->kurzbezeichnung)) {
        if (trim($json->kurzbezeichnung) != '') {
            $panelHTML .= makeWpBlockColumns('Kurzbezeichnung', $json->kurzbezeichnung);
            $foundAllgemeineAngaben = true;
        }
    }
    // geschlecht
    $geschlecht = '';
    if(isset($json->lk_geschlecht)) {
        if(isset($json->lk_geschlecht->conceptName)) {
            if (trim($json->lk_geschlecht->conceptName) != '') {
                $panelHTML .= makeWpBlockColumns('Geschlecht', $json->lk_geschlecht->conceptName);
                $foundAllgemeineAngaben = true;
            }
        }
    }
    
    if($foundAllgemeineAngaben) {
        $beschreibung .= $panelHTML;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // PANEL: ABGUSS
    ////////////////////////////////////////////////////////////////////////////
    
    $panelHTML = '<br /><br /><h5 style="margin-top: 46px;" class="wp-block-heading"><strong>Abguss</strong><br></h5>';
    $foundAbgussAngaben = false;
    
    // maße
    if(isset($json->{'_nested:viamus_abguss__masse_abguss'})) {
        foreach($json->{'_nested:viamus_abguss__masse_abguss'} as $massAngabe) {
            if(isset($massAngabe->{'_nested:viamus_abguss__masse_abguss__masse'})) {
                foreach($massAngabe->{'_nested:viamus_abguss__masse_abguss__masse'} as $mass) {
                    $panelHTML .= makeWpBlockColumns($mass->lk_dimension->conceptName  . ' Abguss', round($mass->wertzahl / 100, 2) . $mass->lk_einheit->conceptName);
                    $foundAbgussAngaben = true;
                }
            }       
        }
    }
    
    // material
    $materialien = array();
    if(isset($json->{'_nested:viamus_abguss__material_abguss'})) {
        foreach($json->{'_nested:viamus_abguss__material_abguss'} as $material) {
            if(isset($material->lk_material)) {
                if(isset($material->lk_material->conceptName)) {
                    array_push($materialien, $material->lk_material->conceptName);
                }
            }
        }
        if(count($materialien) > 0) {
            $panelHTML .= makeWpBlockColumns('Material', implode($materialien, ', '));
            $foundAllgemeineAngaben = true;
        }
    }
    
    // Bemerkung
    if(isset($json->bemerkung)) {
        if (trim($json->bemerkung) != '') {
            $panelHTML .= makeWpBlockColumns('Bemerkung', $json->bemerkung);
            $foundAllgemeineAngaben = true;
        }
    }
    
    // literatur
    $literaturen = array();
    if(isset($json->{'_nested:viamus_abguss__literatur'})) {
        foreach($json->{'_nested:viamus_abguss__literatur'} as $literatur) {
            if(isset($literatur->lk_literatur) || isset($literatur->literatur_freitext)){
                $literaturZitat = '';
                $literaturZitatParts = array();
                if(isset($literatur->lk_literatur)) {
                   $literaturZitat = $literatur->lk_literatur->conceptName;
                }
                elseif(isset($literatur->literatur_freitext)) {
                    $literaturZitat = $literatur->literatur_freitext;
                }
                if(isset($literatur->band)) {
                    array_push($literaturZitatParts, 'Band ' . $literatur->band);
                }
                if(isset($literatur->seite)) {
                    array_push($literaturZitatParts, 'Seite ' . $literatur->seite);
                }
                if(isset($literatur->nummer)) {
                    array_push($literaturZitatParts, 'Nummer ' . $literatur->nummer);
                }
                array_push($literaturen, $literaturZitat . ' ' . implode($literaturZitatParts, ', '));
            } 
        }
        if(count($literaturen) > 0) {
            $panelHTML .= makeWpBlockColumns('Literatur', implode($literaturen, '<br /><br />'));
            $foundAllgemeineAngaben = true;
        }
    }
    
    // bezugsdatum
    if(isset($json->bezugsdatum)) {
        if(isset($json->bezugsdatum->value)) {
            if (trim($json->bezugsdatum->value) != '') {
                $panelHTML .= makeWpBlockColumns('Bezugsdatum', $json->bezugsdatum->value);
                $foundAllgemeineAngaben = true;
            }
        }
    }
    
    // Bemerkung zum Bezug
    if(isset($json->bemerkung_bezug)) {
        if (trim($json->bemerkung_bezug) != '') {
            $panelHTML .= makeWpBlockColumns('Bemerkung zum Bezug', $json->bemerkung_bezug);
            $foundAllgemeineAngaben = true;
        }
    }
    
    // Bezugsquelle
    if(isset($json->lk_bezugsquelle)) {
        if(isset($json->lk_bezugsquelle->viamus_bezugsquelle)) {
            if(isset($json->lk_bezugsquelle->viamus_bezugsquelle->name)) {
                if (trim($json->lk_bezugsquelle->viamus_bezugsquelle->name) != '') {
                    $panelHTML .= makeWpBlockColumns('Bezugsquelle', $json->lk_bezugsquelle->viamus_bezugsquelle->name);
                    $foundAllgemeineAngaben = true;
                }
            }
        }
    }
    
    if($foundAbgussAngaben) {
        $beschreibung .= $panelHTML;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // PANEL: ORIGINAL
    ////////////////////////////////////////////////////////////////////////////
    
    $panelHTML = '<br /><br /><h5 style="margin-top: 46px;" class="wp-block-heading"><strong>Original</strong><br></h5>';
    $foundOriginalAngaben = false;
    
    if(isset($json->lk_original)) {
        if(isset($json->lk_original->viamus_original)) {
            $jsonOriginal = $json->lk_original->viamus_original;
            
            // Name
            if(isset($jsonOriginal->name)) {
                if (trim($jsonOriginal->name) != '') {
                    $panelHTML .= makeWpBlockColumns('Name', $jsonOriginal->name);
                    $foundOriginalAngaben = true;
                }
            }
            // material
            $materialien = array();
            if(isset($jsonOriginal->{'_nested:viamus_original__material_original'})) {
                foreach($jsonOriginal->{'_nested:viamus_original__material_original'} as $material) {
                    if(isset($material->lk_material)) {
                        if(isset($material->lk_material->conceptName)) {
                            array_push($materialien, $material->lk_material->conceptName);
                        }
                    }
                }
                if(count($materialien) > 0) {
                    $panelHTML .= makeWpBlockColumns('Material', implode($materialien, ', '));
                    $foundOriginalAngaben = true;
                }
            }
            // format
            if(isset($jsonOriginal->lk_format)) {
                if(isset($jsonOriginal->lk_format->conceptName)) {
                    if (trim($jsonOriginal->lk_format->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Format', $jsonOriginal->lk_format->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // funktion
            if(isset($jsonOriginal->lk_funktion)) {
                if(isset($jsonOriginal->lk_funktion->conceptName)) {
                    if (trim($jsonOriginal->lk_funktion->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Funktion', $jsonOriginal->lk_funktion->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // gattung
            if(isset($jsonOriginal->lk_gattung)) {
                if(isset($jsonOriginal->lk_gattung->conceptName)) {
                    if (trim($jsonOriginal->lk_gattung->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Gattung', $jsonOriginal->lk_gattung->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // untergattung
            if(isset($jsonOriginal->lk_untergattung)) {
                if(isset($jsonOriginal->lk_untergattung->conceptName)) {
                    if (trim($jsonOriginal->lk_untergattung->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Untergattung', $jsonOriginal->lk_untergattung->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // haartracht
            if(isset($jsonOriginal->lk_haartracht)) {
                if(isset($jsonOriginal->lk_haartracht->conceptName)) {
                    if (trim($jsonOriginal->lk_haartracht->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Haartracht', $jsonOriginal->lk_haartracht->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // Reliefgattung
            if(isset($jsonOriginal->lk_reliefgattung)) {
                if(isset($jsonOriginal->lk_reliefgattung->conceptName)) {
                    if (trim($jsonOriginal->lk_reliefgattung->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Reliefgattung', $jsonOriginal->lk_reliefgattung->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // Ergänzungen
            if(isset($jsonOriginal->ergaenzungen)) {
                if (trim($jsonOriginal->ergaenzungen) != '') {
                    $panelHTML .= makeWpBlockColumns('Ergänzungen', $jsonOriginal->ergaenzungen);
                    $foundOriginalAngaben = true;
                }
            }
            // Baukomplex
            if(isset($jsonOriginal->lk_baukomplex)) {
                if(isset($jsonOriginal->lk_baukomplex->conceptName)) {
                    if (trim($jsonOriginal->lk_baukomplex->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Baukomplex', $jsonOriginal->lk_baukomplex->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // Fundstelle
            if(isset($jsonOriginal->fundstelle)) {
                if (trim($jsonOriginal->fundstelle) != '') {
                    $panelHTML .= makeWpBlockColumns('Fundstelle', $jsonOriginal->fundstelle);
                    $foundOriginalAngaben = true;
                }
            }
            // herkunft
            if(isset($jsonOriginal->{'_nested:viamus_original__herkunft'})) {
                $herkuenfte = array();
                foreach($jsonOriginal->{'_nested:viamus_original__herkunft'} as $herkunft) {
                    $herkunftParts = array();
                    if(isset($herkunft->lk_land)) {
                        if(isset($herkunft->lk_land->_standard)) {
                            array_push($herkunftParts, 'Land: ' . $herkunft->lk_land->_standard->{'1'}->text->{'de-DE'});
                        }
                        if(isset($herkunft->lk_ort->_standard)) {
                            array_push($herkunftParts, 'Ort: ' . $herkunft->lk_ort->_standard->{'1'}->text->{'de-DE'});
                        }
                    }
                    if(count(herkunftParts) > 0) {
                        array_push($herkuenfte, implode($herkunftParts, ', '));
                        $foundOriginalAngaben = true;
                    }
                }
                $panelHTML .= makeWpBlockColumns('Herkunft', implode($herkuenfte, '<br />'));
                $foundOriginalAngaben = true;
            }
            // herkunft sonstiges
            if(isset($jsonOriginal->herkunft_sonstiges)) {
                if (trim($jsonOriginal->herkunft_sonstiges) != '') {
                    $panelHTML .= makeWpBlockColumns('Herkunft Sonstiges', $jsonOriginal->herkunft_sonstiges);
                    $foundOriginalAngaben = true;
                }
            }
            // Datierung
            if(isset($jsonOriginal->datierung_bereich)) {
                $datierungsString = '';
                if(isset($jsonOriginal->datierung_bereich->from)) {
                    if(trim($jsonOriginal->datierung_bereich->from) != '') {
                        $datierungsString = $jsonOriginal->datierung_bereich->from;
                    }
                }
                if(isset($jsonOriginal->datierung_bereich->to)) {
                    if(trim($jsonOriginal->datierung_bereich->to) != '') {
                        if($datierungsString != '') {
                            $datierungsString = $datierungsString . ' bis ' . $jsonOriginal->datierung_bereich->to;
                        } 
                        if($datierungsString == '') {
                            $datierungsString = $jsonOriginal->datierung_bereich->to;
                        }
                    }
                }
                if($datierungsString == '') {
                    if(isset($jsonOriginal->datierung_von)) {
                        if(isset($jsonOriginal->datierung_von->value)) {
                            if(trim($jsonOriginal->datierung_von->value) != '') {
                                $datierungsString = $jsonOriginal->datierung_von->value;
                            }
                        }
                    }
                    if(isset($jsonOriginal->datierung_bis)) {
                        if(isset($jsonOriginal->datierung_bis->value)) {
                            if(trim($jsonOriginal->datierung_bis->value) != '') {
                                if($datierungsString != '') {
                                    $datierungsString = $datierungsString . ' bis ' . $jsonOriginal->datierung_bis->value;
                                }
                                if($datierungsString == '') {
                                    $datierungsString = $jsonOriginal->datierung_bereich->value;
                                }
                            }
                        }
                    }
                }
                if($datierungsString != '') {
                    $panelHTML .= makeWpBlockColumns('Datierung', $datierungsString);
                    $foundOriginalAngaben = true;   
                }
            }
            // datierung sonstiges
            if(isset($jsonOriginal->datierung_sonstiges)) {
                if (trim($jsonOriginal->datierung_sonstiges) != '') {
                    $panelHTML .= makeWpBlockColumns('Datierung Sonstiges', $jsonOriginal->datierung_sonstiges);
                    $foundOriginalAngaben = true;
                }
            }
            // epoche
            if(isset($jsonOriginal->lk_epoche)) {
                if(isset($jsonOriginal->lk_epoche->conceptName)) {
                    if (trim($jsonOriginal->lk_epoche->conceptName) != '') {
                        $panelHTML .= makeWpBlockColumns('Epoche', $jsonOriginal->lk_epoche->conceptName);
                        $foundAllgemeineAngaben = true;
                    }
                }
            }
            // aufbewahrung
            if(isset($jsonOriginal->{'_nested:viamus_original__aufbewahrung'})) {
                $aufbewahrungen = array();
                foreach($jsonOriginal->{'_nested:viamus_original__aufbewahrung'} as $aufbewahrung) {
                    $aufbewahrungParts = array();
                    if(isset($aufbewahrung->lk_land)) {
                        if(isset($aufbewahrung->lk_land->_standard)) {
                            array_push($aufbewahrungParts, 'Land: ' . $aufbewahrung->lk_land->_standard->{'1'}->text->{'de-DE'});
                        }
                        if(isset($aufbewahrung->lk_ort->_standard)) {
                            array_push($aufbewahrungParts, 'Ort: ' . $aufbewahrung->lk_ort->_standard->{'1'}->text->{'de-DE'});
                        }
                        if(isset($aufbewahrung->lk_museum->_standard)) {
                            array_push($aufbewahrungParts, 'Museum: ' . $aufbewahrung->lk_museum->_standard->{'1'}->text->{'de-DE'});
                        }
                        if(isset($aufbewahrung->lk_sammlung->_standard)) {
                            array_push($aufbewahrungParts, 'Sammlung: ' . $aufbewahrung->lk_sammlung->_standard->{'1'}->text->{'de-DE'});
                        }
                        if(isset($aufbewahrung->inventarnummer)) {
                            array_push($aufbewahrungParts, 'Inventarnummer: ' . $aufbewahrung->inventarnummer);
                        }
                        if(isset($aufbewahrung->sonstiges)) {
                            array_push($aufbewahrungParts, 'Sonstiges: ' . $aufbewahrung->sonstiges);
                        }
                    }
                    if(count($aufbewahrungParts) > 0) {
                        array_push($aufbewahrungen, implode($aufbewahrungParts, '<br />'));
                        $foundOriginalAngaben = true;
                    }
                }
                $panelHTML .= makeWpBlockColumns('Aufbewahrung', implode($aufbewahrungen, '<br /><br />'));
                $foundOriginalAngaben = true;
            }
            // literatur
            $literaturen = array();
            if(isset($jsonOriginal->{'_nested:viamus_original__literatur_original'})) {
                foreach($jsonOriginal->{'_nested:viamus_original__literatur_original'} as $literatur) {
                    if(isset($literatur->lk_literatur)){
                        $literaturZitat = '';
                        $literaturZitatParts = array();
                        if(isset($literatur->lk_literatur)) {
                           $literaturZitat = $literatur->lk_literatur->conceptName;
                        }
                        if(isset($literatur->band)) {
                            array_push($literaturZitatParts, 'Band ' . $literatur->band);
                        }
                        if(isset($literatur->seite)) {
                            array_push($literaturZitatParts, 'Seite ' . $literatur->seite);
                        }
                        if(isset($literatur->nummer)) {
                            array_push($literaturZitatParts, 'Nummer ' . $literatur->nummer);
                        }
                        array_push($literaturen, $literaturZitat . ' ' . implode($literaturZitatParts, ', '));
                    } 
                }
                if(count($literaturen) > 0) {
                    $panelHTML .= makeWpBlockColumns('Literatur', implode($literaturen, '<br /><br />'));
                    $foundAllgemeineAngaben = true;
                }
            }
            // typus
            if(isset($jsonOriginal->lk_typus)) {
                if(isset($jsonOriginal->lk_typus->_standard)) {
                    $panelHTML .= makeWpBlockColumns('Typus', $jsonOriginal->lk_typus->_standard->{'1'}->text->{'de-DE'});
                    $foundAllgemeineAngaben = true;
                }
            }
        }    
    }
    
    if($foundOriginalAngaben) {
        $beschreibung .= $panelHTML;
    }
    
    ///////////////////////////////////////////////////////////////////////
    // BESCHREIBUNG
    ///////////////////////////////////////////////////////////////////////
    
    $beschreibungsTexte = array();
    if(isset($json->{'_nested:viamus_abguss__beschreibung'})) {
        if(count($json->{'_nested:viamus_abguss__beschreibung'}) > 0) {
            foreach($json->{'_nested:viamus_abguss__beschreibung'} as $beschreibungsEntry) {
                if(isset($beschreibungsEntry->text)) {
                    if(trim($beschreibungsEntry->text) != '') {
                        array_push($beschreibungsTexte, $beschreibungsEntry->text);
                    }
                }
            }
        }
        if(count($beschreibungsTexte) > 0) {
            $beschreibung .= '<hr style=" border: 0; height: 2px; background-color: black; margin: 30px 0px;">' . implode($beschreibungsTexte, '<br /><br />') . '<br /><br />';
        }
    }
    
    ///////////////////////////////////////////////////////////////////////
    // BILDER
    ///////////////////////////////////////////////////////////////////////
    
    $bildUrl = false;
    $bildHash = false;
    
    // weitere Bilder
    if(isset($json->{"_nested:viamus_abguss__abbildungen"})) {
        if(count($json->{"_nested:viamus_abguss__abbildungen"}) > 0) {
            $moreImages = array();
            foreach($json->{"_nested:viamus_abguss__abbildungen"} as $imageKey=>$image) {
                if($imageKey > 0){
                    $bildUrl = $image->abbildung[0]->versions->huge->url;
                    array_push($moreImages, $bildUrl);
                }
            }
            if(count($moreImages) > 0) {
                $moreImagesHTML = '';
                foreach($moreImages as $imageUrl) {
                    $moreImagesHTML = $moreImagesHTML . '<img src="' . $imageUrl . '" border="0" />';
                }
                $panelHTML = makeWpBlockColumns('Mehr Bilder', $moreImagesHTML);
                $beschreibung .= $panelHTML;
            }

            // Hauptbild
            $bildUrl = '';
            if(isset($json->{"_nested:viamus_abguss__abbildungen"}[0]->abbildung[0]->versions->huge->url)) {
                $bildUrl = $json->{"_nested:viamus_abguss__abbildungen"}[0]->abbildung[0]->versions->huge->url;
            }

            // Hauptbildhash
            $bildHash = '';
            if(isset($json->{"_nested:viamus_abguss__abbildungen"}[0]->abbildung[0]->versions->huge->hash)) {
                $bildHash = $json->{"_nested:viamus_abguss__abbildungen"}[0]->abbildung[0]->versions->huge->hash;
            }
        }
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