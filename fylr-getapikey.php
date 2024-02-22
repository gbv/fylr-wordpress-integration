<?php
  ///////////////////////////////////////////////////////////////////////////////
  // holt einen API-Token per Login/PWD
  ///////////////////////////////////////////////////////////////////////////////

  function getApiKey($apiUrl, $login, $password, $clientID, $clientSecret) {
    $apiKey = false;

    // CACHE, have a look if api-key is cached!
    $cacheLifetime = 1800; //  Laufzeit = 30 minuten
    $cacheDir = plugin_dir_path( __FILE__ ) . 'cache/';

    if (!file_exists($cacheDir)) {
      mkdir($cacheDir, 0777, true);
    }
    $filename_apikey = $cacheDir . 'apikey.cache';
    if (file_exists($filename_apikey)) {
      // if cache-file is not too old
      $creationTime = filectime($filename_apikey);
      if ((time() - $creationTime) < $cacheLifetime) {
          $apiKey = file_get_contents($filename_apikey);
          return $apiKey;
      }
      else {
          // remove file from cache if it is too old
          unlink($filename_apikey);
      }
    }

    // Login via fylr
    $url = $apiUrl . '/api/oauth2/token';
    $data = http_build_query(array(
      'grant_type' => 'password',
      'scope' => 'offline',
      'username' => $login,
      'password' => $password,
      'client_id' => $clientID,
      'client_secret' => $clientSecret
    ));

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    if ($response === FALSE) {
        // Fehler beim Senden der Anfrage
        die("Fehler beim Senden der Anfrage: " . curl_error($curl));
    }
    if($response) {
        $responseJSON = json_decode($response);
        if($responseJSON) {
          if(isset($responseJSON->access_token)) {
            $accessToken = $responseJSON->access_token;
            curl_close($curl);
            file_put_contents($filename_apikey, $accessToken, LOCK_EX);
            return $accessToken;
          }
        }
    }
    else {
        die("Verbindung zum fylr fehlgeschlagen!");
    }
  }
?>
