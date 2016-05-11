<?php

function exchangeTempCodeForPermannetToken($shop, $code) {
    
    // encode the data
    $data = json_encode(array("client_id" => '66276348ee0d46a3414416569fc15b6a', "client_secret" => 'ef090083ab1b85ac11936738f1843108', "code" => $code));

    // the curl url
    $curl_url = "https://$shop/admin/oauth/access_token";

    // set curl options
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    // execute curl
    $response = json_decode(curl_exec($ch));

    // close curl
    curl_close($ch);
    
    return $response;
}

function getShopInfo($shop, $token) {
    // the curl url
    $curl_url = "https://$shop/admin/shop.json";

    // set curl options
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $token"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    // execute curl
    $response = json_decode(curl_exec($ch));

    // close curl
    curl_close($ch);
    
    return $response;
}

function getProducts($shop, $token) {
    
    $curl_url = "https://$shop/admin/products.json";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $token"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $response = json_decode(curl_exec($ch));
    
    curl_close($ch);
    
    return $response;
    
}


function db_connect() {
    $host = "ec2-54-225-165-132.compute-1.amazonaws.com";
    $db_name = "d7f48par2n1s4e";
    $db_user = "wakkhmtlvqmsbn";
    $db_pass = " N2DE4UZLg2RZQSXrqM48rLK8Jr";
    $conn_string = "host=$host dbname=$db_name user=$db_user password=$db_pass";
    $conn = pg_connect($conn_string) or die($conn_string);
    return $conn;
}