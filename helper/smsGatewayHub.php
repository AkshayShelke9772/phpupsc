<?php

class SmsGatewayHubSender {

    private $senderId;
    private $smsUrl;
    private $smsApiKey;
    private $isFlashMsg = false;
    private $messageType = 'Transactional';

    function __construct() {
//    $this->senderId = 'PEOple';
        $this->senderId = 'jabmet';
        $this->smsUrl = 'https://api.msg91.com/api/sendhttp.php';
        $this->smsApiKey = '287272AmwJly4x5d3eaca8';
    }

    public function sendSms($message, $smsNumbers) {

        $ch = curl_init();

        $post_obj = (object) array(
                    'Account' => (object) array(
                        'APIKey' => $this->smsApiKey,
                        'SenderId' => $this->senderId,
                        'Channel' => '2',
                        'DCS' => '0'
                    ),
                    'Messages' => array(),
        );

//        foreach ($smsNumbers as $smsnk => $smsnv) {
//            $receiver = (object) array(
//                        'Number' => $smsnv,
//                        'Text' => $message
//            );
//        }
        $Url = $this->smsUrl . '?mobiles=' . $smsNumbers . '&authkey=' . $this->smsApiKey . '&sender=' . $this->senderId . '&message=' . $message;
        $json_object = json_encode($post_obj);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_object);


        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        $sms_res = true;
//        $xml_obj = simplexml_load_string($server_output);
//        if ($xml_obj) {
//            $error_code = (string) $xml_obj->ErrorCode;
//            if ($error_code !== '000')
//                $sms_res = false;
//        } else {
//            $sms_res = false;
//        }
        return $sms_res;
    }

}
