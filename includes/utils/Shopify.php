<?php

error_reporting(E_ALL);

class Shopify {

    protected $_APP_KEY;
    protected $_APP_SECRET;

    public function __construct() {

        $this->initializeKeys();
    }

    protected function initializeKeys() {

        $this->_APP_KEY = "66276348ee0d46a3414416569fc15b6a";
        $this->_APP_SECRET = "ef090083ab1b85ac11936738f1843108";
    }

    /**
     * this method is used to exchange the temporary access code into a permanent access token
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $TempCode the temp code returned by Shopify
     * @return json the access_token
     */
    public function exchangeTempTokenForPermanentToken($ShopifyURL, $TempCode) {
        // encode the data
        $data = json_encode(array("client_id" => $this->_APP_KEY, "client_secret" => $this->_APP_SECRET, "code" => $TempCode));

        // the curl url
        $curl_url = "https://$ShopifyURL/admin/oauth/access_token";

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

    /**
     * this method is used to get all the scripts for a specific store
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @return json list of scripts
     */
    public function getAllScripts($ShopifyURL, $AccessToken) {
        // the curl url
        $curl_url = "https://$ShopifyURL/admin/script_tags.json";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);
        
        return $response;
    }

    /**
     * this method is used to check if the app script already exists in the store to prevent duplicate scripts being added to the store
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @return boolean - true if count > 0 otherwise false
     */
    public function isScriptExists($ShopifyURL, $AccessToken) {
        // the curl url
        $curl_url = "https://$ShopifyURL/admin/script_tags/count.json";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);

        $script_count = isset($response->count) ? $response->count : 0;
        
        return $script_count > 0 ? true : false;
    }

    /**
     * this method is used to delete a specific script
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     */
    public function deleteScript($ShopifyURL, $AccessToken, $ScriptID) {
        // the curl url
        $curl_url = "https://$ShopifyURL/admin/script_tags/$ScriptID.json";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);
        
        return $response;
    }

    /**
     * this method is used to deploy the app script the store owner's website
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     */
    public function deployScript($ShopifyURL, $AccessToken, $StoreID, $is_new_user, $log_request = true) {
        // before deploying the script, we check first if we already have the scripts/email_capture.js on the server, if not create it
        if (!file_exists(realpath(dirname(__FILE__) . '../../../')."../scripts/email_capture.js")) {
            $scripts_dir = realpath(dirname(__FILE__) . '../../../')."../scripts";
            $templates_dir = realpath(dirname(__FILE__) . '../../../')."../templates";
            $template_content = str_replace('%APP_HOST%', str_replace(array('https:', 'http:'), array('', ''), APP_HOST), file_get_contents("$templates_dir/email_capture.html"));
            file_put_contents("$scripts_dir/email_capture.js", $template_content);
        }
        
        // we check first if we have already installed the script to store, if not we create it otherwise we skip this step
        if(!$this->isScriptExists($ShopifyURL, $AccessToken)) {
            // encode the data
            $src = str_replace(array('https:', 'http:'), array('', ''), APP_HOST)."/scripts/email_capture.js?store_id=".$StoreID;
            $data = json_encode(array("script_tag" => array("src" => $src, "event" => "onload")));

            // the curl url
            $curl_url = "https://$ShopifyURL/admin/script_tags.json";

            // set curl options
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curl_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            // execute curl
            $response = json_decode(curl_exec($ch));

            // close curl
            curl_close($ch);

            return $response;
        }
    }


    /**
     * this method is used to add new customer records to the shopify account
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @param varchar $Email the email of the user
     */
    public function addCustomer($ShopifyURL, $AccessToken, $Email, $FirstName, $LastName) {
        // encode the data
        $data = json_encode(array("customer" => array("first_name" => $FirstName, "last_name" => $LastName, "email" => $Email, "verified_email" => true, "accepts_marketing" => true)));

        // the curl url
        $curl_url = "https://$ShopifyURL/admin/customers.json";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);
    }


    /**
     * this method is used to make a test call whether the AccessToken that we had on record is still working
     * @return boolean
     */
    public function getShopInfo($ShopifyURL, $AccessToken) {
        $curl_url = "https://$ShopifyURL/admin/shop.json";
        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);

        return $response;
    }


    /**
     * this method is used to get the product list from a collection
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @return json data
     */
    public function getProductsFromCollection($ShopifyURL, $AccessToken, $CollectionID) {
        // the curl url
        $curl_url = "https://$ShopifyURL/admin/collects.json?collection_id=$CollectionID";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);

        return $response;
    }


    /**
     * this method is used to get the product list from a collection
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @return json data
     */
    public function getProductInfo($ShopifyURL, $AccessToken, $ProductID) {
        // the curl url
        $curl_url = "https://$ShopifyURL/admin/products/$ProductID.json";

        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);

        return $response;
    }


    /**
     * this method is used to create webhooks for app/uninstalled event, so that we 
     * can track stores who uninstalled the app and deactivate their email pirate
     * @param varchar $ShopifyURL the store URL of store owner
     * @param varchar $AccessToken the access token returned by Shopify
     * @return json
     */
    public function createWebhooks($ShopifyURL, $AccessToken) {
        $curl_url = "https://$ShopifyURL/admin/webhooks.json";
        $data = json_encode(array('webhook' => array('topic' => 'app/uninstalled', 'address' => APP_HOST."/uninstall?shop=$ShopifyURL", 'format' => 'json')));
        // set curl options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "X-Shopify-Access-Token: $AccessToken"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // execute curl
        $response = json_decode(curl_exec($ch));

        // close curl
        curl_close($ch);

        return $response;
    }

    public function getAppKey() {
        return $this->_APP_KEY;
    }


    public function getAppSecret() {
        return $this->_APP_SECRET;
    }

}