<?php
class Smsalert_Hook {    
    public function add_action($hook_actions) {
        foreach ( $hook_actions as $hook ) {
            add_action( $hook['hook'], $hook['function_to_be_called'], $hook['priority'], $hook['accepted_args']);
        }
    }    
}
$hook_actions = array();
$hook_actions[] = array('hook' => 'woocommerce_order_status_pending', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_pending', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_failed', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_failed', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_on-hold', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_on_hold', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_processing', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_processing', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_completed', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_completed', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_refunded', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_refunded', 'priority' => 10, 'accepted_args' => 1);
$hook_actions[] = array('hook' => 'woocommerce_order_status_cancelled', 'function_to_be_called' => 'Smsalert_WooCoommerce_Notification::send_sms_woocommerce_order_status_cancelled', 'priority' => 10, 'accepted_args' => 1);
$hook = new Smsalert_Hook();
$hook->add_action($hook_actions);
?>