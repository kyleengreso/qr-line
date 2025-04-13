<?php
echo "Daemon Started\n";

$serverName = "localhost";

while (TRUE) {
    // datetime today in format yyyy-mm-dd_hh:mm:ss
    $time = date("Y-m-d_H:i:s");
    // delay 1 sec
    sleep(1);
    echo "[" . $time . "] " ."Running... \n";

    // how to access the endpoint using $serverName is url
    // $url = $serverName . "/public/api/api_endpoint.php?counters"; // replace with your endpoint
    // $ch = curl_init($url);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // // Execute the request
    // $response = curl_exec($ch);
    // if ($response === false) {
    //     echo "Error: " . curl_error($ch) . "\n";
    // } else {
    //     echo "Response: " . $response . "\n";
    // }

    // how about post with json
    $url = $serverName . "/public/api/api_endpoint.php"; // replace with your endpoint
    $data = array(
        'method' => 'refresh_count'
        // 'key2' => 'value2'
    );
    $jsonData = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ));
    // Execute the request
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error: " . curl_error($ch) . "\n";
    } else {
        echo "Response: " . $response . "\n";
    }
}


?>