<?php

function sendRequest($apiUrl, $urlPart, $urlParameters, $data, $method) {
    
    $url = $apiUrl . $urlPart;
    if(strpos($urlParameters, 'access_token=') === false) {
        $urlParameters = str_replace('token=', 'access_token=', $urlParameters);
    }
    
    // get access_token from urlParameters
    $parts = parse_url($urlParameters);
    if(isset($parts['path'])) {
        parse_str($parts['path'], $query);
        $apiKey = $query['access_token'];
        $oauth2Header = 'Authorization: Bearer ' . $apiKey;
    }
    else {
    if(isset($parts['query'])) {
      parse_str($parts['query'], $query);
      $apiKey = $query['access_token'];
      $oauth2Header = 'Authorization: Bearer ' . $apiKey;
    }
    }
    // if urlParameters
    if ($urlParameters) {
      $url .= '?' . $urlParameters;
    }
    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

    $responseHeaders = array();

    $headers = array(
        'Accept: application/json',
        'content-type: application/json',
        'charset=UTF-8'
    );

    if(isset($oauth2Header)) {
      array_push($headers, $oauth2Header);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method == 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    
    $response = json_decode($response);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // if no result, output http-statuscode
    if ($response == false || $response == null || $response == 'NULL' || $httpcode != 200) {
        return false;
    }

    $version = curl_version();
    extract(curl_getinfo($ch));

    curl_close($ch);

    return $response;
}

?>
