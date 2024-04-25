<?php
  ///////////////////////////////////////////////////////////////////////////////
  // holt die Übersetzungen aus fylr
  ///////////////////////////////////////////////////////////////////////////////

  function getFylrTags() {

    // CACHE, have a look if translations are cached!
    $cacheLifetime = 1800; //  Laufzeit = 30 minuten
    $cacheDir = plugin_dir_path( __FILE__ ) . 'cache/';

    if (!file_exists($cacheDir)) {
      mkdir($cacheDir, 0777, true);
    }
    $filename_tags = $cacheDir . 'tags.cache';
    if (file_exists($filename_tags)) {
      // if cache-file is not too old
      $creationTime = filectime($filename_tags);
      if ((time() - $creationTime) < $cacheLifetime) {
          $tags = file_get_contents($filename_tags);
          return $tags;
      }
      else {
          // remove file from cache if it is too old
          unlink($filename_tags);
      }
    }
      
    $apiUrl = get_option('fylr_integration_api_url');
    $username = get_option('fylr_integration_username');
    $password = get_option('fylr_integration_password');
    $clientID = get_option('fylr_integration_clientid');
    $clientSecret = get_option('fylr_integration_clientsecret');
    $objecttype = get_option('fylr_integration_objecttype');
    $language = get_option('fylr_integration_language_ietf');
    if($language == '') {
        $language = 'de-DE';
    }
    $templateFile = get_option('fylr_integration_template_file');
    
    if (empty($apiUrl) || empty($username) || empty($password) || empty($clientID) || empty($clientSecret) || empty($templateFile) || empty($objecttype)) {
        return 'API URL, Username, Password, ClientID, ClientSecret, Objecttype and Template are required.';
    }
    
    // Login an fylr
    $fylrApiKey = getApiKey($apiUrl, $username, $password, $clientID, $clientSecret);

    $fylr_api_response = sendRequest($apiUrl, '/api/v1/tags', 'access_token=' . $fylrApiKey, null, null);       
    // Neue JSON-Liste erstellen
    $json_list = [];
      echo '<pre>';
    // Durchlaufen Sie die Tabellen im JSON-Objekt
    foreach ($fylr_api_response as $taggroup) {
        foreach ($taggroup->_tags as $tags) {
            foreach($tags as $tag) {
                if(isset($tag->displayname->$language)) {
                    $json_list[$tag->_id] = $tag->displayname->$language;
                }
                else {
                    $json_list[$tag->_id] = $tag->displayname->{array_keys((array) $tag->displayname)[0]};
                }
            }
        }
    }
    // Ausgabe der JSON-Liste
    $json_list = json_encode($json_list, JSON_PRETTY_PRINT);
    file_put_contents($filename_tags, $json_list);
  }
?>
