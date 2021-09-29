<?php

set_time_limit(9999999999999);

$arrContextOptions=array(
    "http" => array(
        "method" => "GET",
        "header" =>
            "Content-Type: application/xml; charset=utf-8;\r\n".
            "Connection: close\r\n",
        "ignore_errors" => true,
    ),
    "ssl"=>array(
        "allow_self_signed"=>true,
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$json = file_get_contents('https://localhost:4433/AutoProcessa',false, stream_context_create($arrContextOptions));
$obj = json_decode($json);

foreach ($obj as $id){

    $url = '';
    $url = 'https://localhost:4433/processar?id='.$id;

    $ch = curl_init();
    // IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
    // in most cases, you should set it to true
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $id;
    //sleep(1);
}

var_dump($obj);

?>
