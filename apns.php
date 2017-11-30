<?php

// Production mode
//$certificateFile = 'apns-dis.pem';
//$pushServer = 'ssl://gateway.push.apple.com:2195';
//$feedbackServer = 'ssl://feedback.push.apple.com:2196';

// Sandbox mode
$certificateFile = 'apns-dev.pem';
$pushServer = 'ssl://gateway.sandbox.push.apple.com:2195';
$feedbackServer = 'ssl://feedback.sandbox.push.apple.com:2196';

// push notification
$streamContext = stream_context_create();
$passphrase = 'Your passphrase'; //if it didn't set,comment out

stream_context_set_option($streamContext, 'ssl', 'local_cert',$certificateFile);
stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

$fp = stream_socket_client(
    $pushServer,
    $error,
    $errorStr,
    100,
    STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
    $streamContext
);
$payloadObject = array(
    'aps' => array(
        'alert' => '',
        'sound' => 'default', // 'default'=>'custom.wav'
        'badge' => 3,
        'category' => 'realtime',
        "mutable-content" => 1  //must exist
    ),
    'media' => ''
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //getting values

    $payloadObject['aps']['alert'] = $_POST['alert'];
    $payloadObject['media']= $_POST['media'];

    $payload = json_encode($payloadObject);

    echo $payload;
}


// make payload

$deviceToken = 'Your Device Token';


$expire = time() + 3600;
$id = time();

if ($expire) {
    // Enhanced mode
    $binary  = pack('CNNnH*n', 1, $id, $expire, 32, $deviceToken, strlen($payload)).$payload;
} else {
    // Simple mode
    $binary  = pack('CnH*n', 0, 32, $deviceToken, strlen($payload)).$payload;
}
$result = fwrite($fp, $binary);


fclose($fp);

?>
