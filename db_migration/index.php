<?php

include '../install/function.php';
$conn = db_connect();

// create the stores table
$query = "CREATE TABLE stores (store_id serial primary key, store_url VARCHAR(255) not null, access_token VARCHAR(255) not null)";
echo "CREATING QUERY: $query...";
pg_query($query) or die($query."<br>");
echo "DONE!<br>";

