<?php
/*
 * Integration with SMS Alerts
 *
 * Class methods:
 *      sendSMS($to, $from, $message, $message_type, $dlr_url, $udh)
 *      receiveDLR()
 *      receiveMO()
 *      messageStatus($msgid)
 */

class SmsAlert {
    private $api_key = '';
    private $api_secret = '';

    // REST API URL
    public $rest_base_url = 'https://smsalerts.io/apisendsms';

    private $rest_commands = array (
            'send_sms' => array('url' => '/sms', 'method' => 'POST'),
            'get_message_status' => array('url' => '/report/message', 'method' => 'GET')

    );
    public $response_format = 'json';
    public $message_type_option = array('7-bit' => 1, '8-bit' => 2, 'Unicode' => 3);
    public function __construct($api_key = null, $api_secret = null)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    function sendSMS($from, $to, $message, $message_type = null, $dlr_url = null, $udh = null)
    {
        // Auto detect message type
        // Convert to UTF-16 hexadecimal if it's unicode SMS
        if ($message_type === null) {
            $unicode = $this->checkIsUnicode($message);
            if($unicode) {
                $message = $this->unicodeConversion($message);
                $message_type = $this->message_type_option['Unicode'];
            } else {
                $message_type = $this->message_type_option['7-bit'];
            }
        }

        // Send request to MobikasaSMS gateway
        $params = array(
                'smsalert-from' => $from,
                'smsalert-to' => $to,
                'smsalert-text' => $message,
                'smsalert-coding' => $message_type,
                'smsalert-charset' => 'UTF-8',
                'smsalert-dlr-mask' => 1,
                'smsalert-dlr-url' => $dlr_url,
                'smsalert-udh' => $udh
        );

        return $this->invokeApi ('send_sms', $params);
    }

    public function receiveDLR($data)
    {
        $delivery_status = array(1 => 'Success', 2 => 'Failed', 3 => 'Expired');

        $delivery_report_data = new stdClass();
        $delivery_report_data->from = $data['smsalert-from'];
        $delivery_report_data->to = $data['smsalert-to'];
        $delivery_report_data->dlr_status = $delivery_status[$data['smsalert-dlr-status']];
        $delivery_report_data->msgid = $data['smsalert-msgid'];
        $delivery_report_data->error_code = $data['smsalert-error-code'];
        $delivery_report_data->dlr_received_time = date('Y-m-d H:i:s');

        return $delivery_report_data;
    }

    public function receiveMO($data)
    {
        $mo_message = new stdClass();
        $mo_message->from = $data['smsalert-from'];
        $mo_message->to = $data['smsalert-to'];
        $mo_message->keyword = $data['smsalert-keyword'];
        $mo_message->text = $data['smsalert-text'];
        $mo_message->coding = $data['smsalert-coding'];
        $mo_message->time = $data['smsalert-time'];

        if($mo_message->coding == $this->message_type_option['Unicode']) {
            $mo_message->keyword = $this->utf16HexToUtf8($mo_message->keyword);
            $mo_message->text = $this->utf16HexToUtf8($mo_message->text);
        }

        return $mo_message;
    }

    public function messageStatus($msgid)
    {
        $params = array('smsalert-msgid' => $msgid);
        return $this->invokeApi ('get_message_status', $params);
    }

    public function accountBalance()
    {
        return $this->invokeApi ('get_balance');
    }

    public function accountPricing($mcc = null, $mnc = null)
    {
        $params = array();
        if($mcc) {
            $params['smsalert-mcc'] = $mcc;
        }
        if($mnc) {
            $params['smsalert-mnc'] = $mnc;
        }
        return $this->invokeApi ('get_pricing', $params);
    }

    private function invokeApi ($command, $params = array())
    {
        
        // Get REST URL and HTTP method
        $command_info = $this->rest_commands[$command];
        $url = $this->rest_base_url;
        $method = $command_info['method'];

        // Build the post data
        $params = array_merge($params, array('smsalert-api-key' => $this->api_key, 'smsalert-api-secret' => $this->api_secret, 'smsalert-resp-format' => $this->response_format));

        $rest_request = curl_init();
        if($method == 'POST') {
            curl_setopt($rest_request, CURLOPT_URL, $url);
            curl_setopt($rest_request, CURLOPT_HEADER, true);
            curl_setopt($rest_request, CURLOPT_POST, $method == 'POST' ? true: false);
            curl_setopt($rest_request, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            $query_string = '';
            foreach($params as $parameter_name => $parameter_value) {
                $query_string .= '&'.$parameter_name.'='.$parameter_value;
            }
            $query_string = substr($query_string, 1);
            curl_setopt($rest_request, CURLOPT_URL, $url.'?'.$query_string);
        }
        curl_setopt($rest_request, CURLOPT_REFERER, get_site_url());
        curl_setopt($rest_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rest_request, CURLOPT_SSL_VERIFYPEER, false);
        $rest_response = curl_exec($rest_request);
        curl_close ($rest_request);

        return $rest_response;
    }

    private function checkIsUnicode($message)
    {
        // Check if message contains non GSM characters
        $gsm_greek_character_ascii_value = ',132,133,134,135,137,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,163,164,165,166,167,168,169,172,177,178,182,184,185,188,191,194,195,206,226,130,172,';

        $string_length = strlen($message);
        for ($counter = 0; $counter < $string_length; $counter++) {
            $character_code = ord($message[$counter]);
            if($character_code > 127 && !preg_match('#,'.$character_code.',#', $gsm_greek_character_ascii_value)) {
                return true;
            }
        }
        return false;
    }

    private function unicodeConversion($message)
    {
        // Convert UTF-8 string to UTF-16 hexadecimal
        $message = mb_convert_encoding($message, 'UTF-16', 'UTF-8');
        $message = $this->strToHex($message);
        return $message;
    }


    private function strToHex($string)
    {
        $hex_string = '';
        $string_length = strlen($string);
        for ($counter = 0; $counter < $string_length; $counter++) {
            $ascii_value = ord($string[$counter]);
            $hexCode = dechex($ascii_value);
            $hex_string .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex_string);
    }

    private function utf16HexToUtf8($string)
    {
        if(strlen($string) % 4) {
            $string = '00'.$string;
        }

        $converted_string = '';
        $string_length = strlen($string);
        for($counter = 0; $counter < $string_length; $counter += 4) {
            $converted_string .= "&#".hexdec(substr($string, $counter, 4)).";";
        }
        $converted_string = mb_convert_encoding($converted_string, "UTF-8", "HTML-ENTITIES");

        return $converted_string;
    }
}
?>