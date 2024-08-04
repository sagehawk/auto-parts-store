<?php
function processCreditCard($creditCardInfo, $amount) {
    $url = 'http://blitz.cs.niu.edu/CreditCard/';
    $data = array(
        'vendor' => 'VE001-99',
        'trans' => uniqid(),
        'cc' => $creditCardInfo['number'],
        'name' => $creditCardInfo['name'],
        'exp' => $creditCardInfo['expiration'],
        'amount' => $amount
    );

    $options = array(
        'http' => array(
            'header' => array('Content-type: application/json', 'Accept: application/json'),
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if (isset($response['authorization'])) {
        return $response['authorization'];
    } else {
        return "Error: " . $result;
    }
}
?>