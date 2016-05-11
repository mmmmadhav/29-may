<?php

include 'function.php';

$shop = $_REQUEST['shop'];
$scope = "read_products,write_products";
$api_key = '66276348ee0d46a3414416569fc15b6a';
$oauth = 'https://' . $shop . '/admin/oauth/authorize?scope=' . $scope . '&client_id=' . $api_key . '&redirect_uri=https://meynard-test-app.herokuapp.com/install';

if ($shop && !isset($_GET['code'])) {
    // check if store exists in our db
    $conn = db_connect();
    $result = pg_query("SELECT * FROM stores WHERE store_url = '$shop'");
    $store = pg_fetch_assoc($result);
    if (!empty($store)) { //user exists redirect him to backpanel
        header("Location: /backpanel?shop=$shop");
        exit();
    } else { // store not found, redirect to oauth
        header("Location: $oauth");
        exit();
    }
} else if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $exchange_token_response = exchangeTempCodeForPermannetToken($shop, $code);
    
    if (!empty($exchange_token_response->access_token)) {
        $permanent_token = $exchange_token_response->access_token;
        
        $conn = db_connect();
        pg_query("INSERT INTO stores(store_url, access_token) VALUES('$shop', '$permanent_token')");
        
        // let's create a session for this shop
        $_SESSION['shop_session'] = time();
        
        header(("Location: /backpanel?shop=$shop"));
        exit();
    } else {
        exit("No token received!");
    }
}
