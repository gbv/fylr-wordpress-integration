<?php

function getDataFromTemplate($json) {
    
    $json = $json->objekt;
    $uuid = $json->_uuid;
    
    // Titel
    $titel = '';
    if(isset($json->{"_nested:objekt__objektbezeichnungen"}[0]->{"_nested:objekt__objektbezeichnungen__objektbezeichnungen"}[0]->objektbezeichnung)) {
        $titel = $json->{"_nested:objekt__objektbezeichnungen"}[0]->{"_nested:objekt__objektbezeichnungen__objektbezeichnungen"}[0]->objektbezeichnung;
    }    

    $beschreibung = '';
    if(isset($json->{"_nested:objekt__objektbeschreibungen"}[0]->{"_nested:objekt__objektbeschreibungen__texte"}[0]->text)) {
        $beschreibung = $json->{"_nested:objekt__objektbeschreibungen"}[0]->{"_nested:objekt__objektbeschreibungen__texte"}[0]->text;
    }    

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
        'post_title' => ($titel . 'zzzzzzzz'),
        'post_content' => $beschreibung,
        'post_image' => $bildUrl,
        'post_image_hash' => $bildHash
    );
    return $api_data;
}
?>