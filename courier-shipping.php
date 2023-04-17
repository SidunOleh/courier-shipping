<?php

/*
Plugin Name: Couriere Shipping
Description: Couriere shipping method for Woocommerce
Author: Sidun Oleh
*/

defined( 'ABSPATH' ) or die;

/*
Plugin run
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/courier-shipping.php';
$courier_shipping = new Courier_Shipping();
$courier_shipping->run();