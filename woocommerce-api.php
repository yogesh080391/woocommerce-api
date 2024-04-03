<?php
/*
Plugin Name: Woocommerce API
Description: Woocommerce Custom API.
Version: 1.0.0
Author: 610 Weblab 
Requires at least: 6.1
Requires PHP: 7.1
*/

defined('ABSPATH') or die("You can't access this file");

define('WOO_API_PLUGIN_NAME', plugin_basename(__FILE__));
define('WOO_API_PLUGIN_PATH', __DIR__);
define('WOO_API_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once __DIR__.'/includes/WoocommerceAPI.php';
require_once __DIR__.'/includes/ProductAPI.php';
require_once __DIR__.'/includes/OrderAPI.php';

$errors = WoocommerceAPI::preInitCheckErrors();
if (count($errors)) 
{
    add_action('admin_notices', static function () use ($errors) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . $error . '</p></div>';
        }
    });
}
else
{
	$WoocommerceAPI = WoocommerceAPI::GetInstance();
	register_activation_hook(__FILE__, [$WoocommerceAPI, 'activate']);
	$WoocommerceAPI->InitPlugin();
}