<?php

include '../install/function.php';
$shop = $_GET['shop'];

if ($shop !== '') { // isset($_SESSION['shop_session']) && 
    $conn = db_connect();
    $result = pg_query("SELECT * FROM stores WHERE store_url = '$shop'");
    $store_info = pg_fetch_assoc($result);
    
    $shop_info = getShopInfo($shop, $store_info['access_token']);
} else {
    exit("<h2>You're not logged in or verified.</h2>");
}

?>

<h1>Your Shop Info:</h1>

<?php

echo "<pre>";
print_r($shop_info);
echo "</pre>";

?>


<h1>Products:</h1>
<?php 
$products = getProducts($shop, $store_info['access_token']);
echo "<pre>";
print_r($products);
echo "</pre>";