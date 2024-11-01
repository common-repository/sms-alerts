<?php
class Smsalert_Setting {
    private $settings_api;
    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
       add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {
        $this->settings_api->set_sections( $this->get_settings_sections() );
       $this->settings_api->set_fields( $this->get_settings_fields() );
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_options_page( 'SMS Alerts', 'SMS Alerts', 'manage_options', 'smsalert-woocoommerce-setting', array($this, 'plugin_page') );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'smsalert_setting',
                'title' => __( 'SMS Alerts Setting', 'smsalert-woocoommerce' )
            )

        );
        return $sections;
    }

    function get_settings_fields() {		
             $settings_fields = array(
            'smsalert_setting' => array(
                array(
                    'name'              => 'smsalert_woocommerce_api_key',
                    'label'             => __( 'Merchant Id', 'smsalert-woocoommerce' ),
                    'desc'              => __( 'View Your SMS Alerts Account Merchant ID key by Registering <a href="https://smsalerts.io/register" target="_blank">here</a>', 'smsalert-woocoommerce' ),
                    'type'              => 'text',
                ),
                array(
                    'name'              => 'smsalert_woocommerce_api_secret',
                    'label'             => __( 'Access Key', 'smsalert-woocoommerce' ),
                    'desc'              => __( 'View Your SMS Alerts Account Access Key API <a href="https://smsalerts.io/settings" target="_blank">here</a>', 'smsalert-woocoommerce' ),
                    'type'              => 'text',
                ),
                array(
                    'name'    => 'smsalert_woocommerce_send_sms',
                    'label'   => __( 'Send notification on', 'smsalert-woocoommerce' ),
                    'desc'    => __( 'Choose when to send a sms notification to your customers<br>View/Edit your Transaction SMS template <a href="https://smsalerts.io/" target="_blank">click here</a>.', 'smsalert-woocoommerce' ),
                    'type'    => 'multicheck',
                    'options' => array(
                        'pending'   => ' Pending',
                        'on-hold'   => ' On-hold',
                        'processing' => ' Processing',
                        'completed'  => ' Completed',
                        'cancelled'  => ' Cancelled',
                        'refunded'  => ' Refunded',
                        'failed'  => ' Failed'
                    )
                )       
               
            )

        );
        return $settings_fields;
    }   

       function plugin_page() {
        echo '<div class="wrap">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
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