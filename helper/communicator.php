<?php

require_once 'smsGatewayHub.php';

class communicator {

    public function sendOtp($otpCode) {
        $smsSender = new \SmsGatewayHubSender();
        return $smsSender->sendSms('Hello Priyanka', '8483974911');
    }

}

?>