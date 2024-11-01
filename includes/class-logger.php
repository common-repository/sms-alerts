<?php
class Smsalert_WooCoommerce_Logger {    
    private $_handles = array();
    private $log_directory;
            
    public function __construct() {
        $this->_handles = array();
        
        $upload_dir = wp_upload_dir();
        $this->log_directory = $upload_dir['basedir'] . '/smsalert-woocommerce-logs/';
        
        wp_mkdir_p($this->log_directory);
    }            

    private function open($handle) {
        if (isset($this->_handles[$handle])) {
            return true;
        }

        if ($this->_handles[$handle] = @fopen($this->log_directory.$handle.'.log', 'a')) {
            return true;
        }
        return false;
    }

    public function add($handle, $message) {
        if ($this->open($handle)) {
            $current_datetime = date('Y-m-d H:i:s');
            @fwrite($this->_handles[$handle], $current_datetime . " " . $message . "\n");
        }
    }
}
?>