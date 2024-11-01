<?php
/*
Plugin Name: SMS Alert Order Notifications
Plugin URI:  https://smsalerts.io
Description: SMS Alerts Order SMS Notification for WooCommerce
Version:     1.1.1
Author:      Mobikasa Pvt. Ltd
Author URI:  https://mobikasa.com
License:     xxxx
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Domain Path: /languages
Text Domain: smsalerts-woocommerce
*/
if ( ! defined( 'WPINC' ) ) {
    die;
}
require_once plugin_dir_path( __FILE__ ) . 'includes/class-hook.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-logger.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-notification.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-setting.php';
require_once plugin_dir_path( __FILE__ ) . 'lib/class.settings-api.php';
new Smsalert_Setting();
?>