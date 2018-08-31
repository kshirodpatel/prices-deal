<?php
/*
Plugin Name: WooCommerce Price Deals Of The Day
Plugin URI: http://magnigenie.com
Description: This plugin allows you to set price deals for the products
Version: 1.0
Author: Kshirod
Author URI: http://magnigenie.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct file access
! defined( 'ABSPATH' ) AND exit;

define('PDOD_FILE', __FILE__);
define('PDOD_PATH', plugin_dir_path(__FILE__));
define('PDOD_BASE', plugin_basename(__FILE__));

add_action('plugins_loaded', 'pdod_load_textdomain');

function pdod_load_textdomain() {
	load_plugin_textdomain( 'pdod', false, dirname( plugin_basename( __FILE__ ) ). '/lang/' );
}

require PDOD_PATH . '/includes/class-pdod.php';

new Price_Deals_Of_The_Day();