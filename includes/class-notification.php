<?php
class Smsalert_WooCoommerce_Notification {
    public static function send_sms_woocommerce_order_status_pending($order_id) {
        self::send_customer_notification($order_id, "pending");
    }
    public static function send_sms_woocommerce_order_status_failed($order_id) {
        self::send_customer_notification($order_id, "failed");
    }
    public static function send_sms_woocommerce_order_status_on_hold($order_id) {
        self::send_customer_notification($order_id, "on-hold");
    }
    public static function send_sms_woocommerce_order_status_processing($order_id) {
        self::send_customer_notification($order_id, "processing");
    }
    public static function send_sms_woocommerce_order_status_completed($order_id) {
        self::send_customer_notification($order_id, "completed");
    }
    public static function send_sms_woocommerce_order_status_refunded($order_id) {
        self::send_customer_notification($order_id, "refunded");
    }
    public static function send_sms_woocommerce_order_status_cancelled($order_id) {
        self::send_customer_notification($order_id, "cancelled");
    }
    public static function send_sms_woocommerce_order_status_changed($order_id, $old_status, $new_status) {
        $log = new Smsalert_WooCoommerce_Logger();
        $log->add('SMS Alerts', 'Order status changed": old status: '.$old_status.' , new status: '.$new_status);
    }
     public static function woocommerce_payment_complete($order_id) {
        $log = new Smsalert_WooCoommerce_Logger();
        $log->add('SMS Alerts', 'Payment completed');
    }
     public static function woocommerce_payment_complete_order_status($order_id) {
        $log = new Smsalert_WooCoommerce_Logger();
        $log->add('SMS Alerts', 'Completed order status');
    }
    public static function send_customer_notification($order_id, $status) {
        if( !in_array( $status, self::smsalert_woocommerce_get_option( 'smsalert_woocommerce_send_sms', 'smsalert_setting', array() ) ) ) return;
        $log = new Smsalert_WooCoommerce_Logger();
		$order_details = new WC_Order($order_id);		
		$order = wc_get_order( $order_id );
		$order_data = $order->get_data();
		$items = [];
		$counter = 0;
		
		foreach( $order->get_items() as $item_id => $item_data )
		{
			
			$items[$counter]['item_id'] = $item_id;
			$items[$counter]['product_name'] = $item_data['name'];
			$items[$counter]['item_type'] = $item_data['type']; 
			$items[$counter]['product_id'] = $item_data['product_id'];
			$items[$counter]['variation_id'] = $item_data['variation_id'];
			$items[$counter]['quantity'] = $item_data['qty'];
			$items[$counter]['tax_class'] = $item_data['tax_class'];
			$items[$counter]['line_subtotal'] = $item_data['line_subtotal'];
			$items[$counter]['line_subtotal_tax'] = $item_data['line_subtotal_tax'];
			$items[$counter]['line_total'] = $item_data['line_total'];
			$items[$counter]['line_total_tax'] = $item_data['line_total_tax'];	

			$counter++;
		}

		$order_data['items'] = $items;

		$order_data['store_name'] = get_option('blogname');

		$order_data['store_email'] = get_option('admin_email');

        $message = json_encode($order_data);
        require_once plugin_dir_path(dirname(__FILE__)). 'lib/autoload.php';

        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneNumberUtil->parse($order_details->billing_phone, $order_details->billing_country);

        if($phoneNumberUtil->isValidNumber($phoneNumber) && ($phoneNumberUtil->getNumberType($phoneNumber) == 1 || $phoneNumberUtil->getNumberType($phoneNumber) == 2)) {
            $customer_phone_no = $phoneNumberUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
            $customer_phone_no = self::phone_number_processing($customer_phone_no);

            $log->add('SMS Alerts', 'Customer\'s billing phone number ('.$order_details->billing_phone.') in country ('.$order_details->billing_country.') converted to '.$customer_phone_no);

            self::send_sms($customer_phone_no, $message);
        } else {
            $log->add('SMS Alerts', 'Customer\'s billing phone number ('.$order_details->billing_phone.') not a valid mobile number in country ('.$order_details->billing_country.'), not sending SMS.');
        }
    }
  

    public static function replace_order_keyword($message, $order_details, $user_type, $order_status) {
        $items = $order_details->get_items();
        foreach ( $items as $item ) {
            $product_name .= ', '.$item['name'];
        }
        if($product_name) {
            $product_name = substr($product_name, 2);
        }

        $search = array('[shop_name]', '[order_id]', '[order_currency]', '[order_amount]', '[order_status]', '[order_product]', '[billing_first_name]', '[billing_last_name]', '[billing_phone]', '[billing_email]', '[billing_company]', '[billing_address]', '[billing_country]', '[billing_city]', '[billing_state]', '[billing_postcode]', '[payment_method]');
        $replace = array(get_bloginfo('name'), $order_details->get_order_number(), $order_details->get_order_currency(), $order_details->get_total(), ucfirst($order_details->get_status()), $product_name, $order_details->billing_first_name, $order_details->billing_last_name, $order_details->billing_phone, $order_details->billing_email, $order_details->billing_company, $order_details->billing_address_1, $order_details->billing_country, $order_details->billing_city, $order_details->billing_state, $order_details->billing_postcode, $order_details->payment_method_title);
        $message = str_replace($search, $replace, $message);

        $additional_billing_fields_array = self::get_additional_billing_fields();
        foreach ($additional_billing_fields_array as $field) {
            $post_data = get_post_meta( $order_details->get_order_number(), $field, true);
            $message = str_replace('['.$field.']', $post_data, $message);
        }

        return $message;
    }

    public static function send_sms($phone_no, $message) {
        require_once plugin_dir_path(dirname(__FILE__)) . 'lib/SmsAlerts.php';

        $log = new Smsalert_WooCoommerce_Logger();
        $api_key = self::smsalert_woocommerce_get_option("smsalert_woocommerce_api_key", 'smsalert_setting', '');
        $api_secret = self::smsalert_woocommerce_get_option("smsalert_woocommerce_api_secret", 'smsalert_setting', '');
        $sms_from = self::smsalert_woocommerce_get_option("smsalert_woocommerce_sms_from", 'smsalert_setting', '');

        if($api_key == '' || $api_key == '') return;
        if($sms_from == '') $sms_from = 'SMS';

        $log->add('SMS Alerts', 'Sending SMS to '.$phone_no.', message: '.$message);

        try {
            $smsalert_rest = new SmsAlert($api_key, $api_secret);
            $rest_response = $smsalert_rest->sendSMS($sms_from, $phone_no, $message);

            $log->add('SMS Alerts', 'SMS response from SMS gateway: ' .$rest_response);
        } catch (Exception $e) {
            $log->add('SMS Alerts', 'Failed sent SMS: ' . $e->getMessage());
        }
    }

    public static function smsalert_woocommerce_get_option($option, $section, $default = '') {

        $options = get_option( $section );

        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }

        return $default;
    }

    private function phone_number_processing($phone_no)
    {
        $updated_phone_no = '';
        if($phone_no != '') {
            $phone_no_array = explode(",", $phone_no);
            foreach($phone_no_array as $number) {
                if($number != '') {
                    $number = preg_replace("/[^0-9,.]/", "", $number);
                    $updated_phone_no .= ','.$number;
                }
            }
            $updated_phone_no = substr($updated_phone_no, 1);
        }
        return $updated_phone_no;
    }

    function get_additional_billing_fields() {
        $default_billing_fields = array(
            'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state',
            'billing_country', 'billing_postcode', 'billing_phone', 'billing_email'
        );
        $additional_billing_field = array();
        $billing_fields = array_filter(get_option('wc_fields_billing', array()));
        foreach($billing_fields as $field_key => $field_info) {
            if(!in_array($field_key, $default_billing_fields) && $field_info['enabled']) {
                array_push($additional_billing_field, $field_key);
            }
        }
        return $additional_billing_field;
    }
}

?>