<?php

/**
 * 
 */
class SendMail {

    private $mail;

    function __construct() {
        require_once dirname(__FILE__) . '/phpMail/PHPMailerAutoload.php';
        $this->mail = new PHPMailer;
        $this->mail->SMTPDebug = 3;                               // Enable verbose debug output

        $this->mail->isSMTP(true);                                      // Set mailer to use SMTP
        $this->mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $this->mail->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mail->Username = 'shelkeakshay.2016@gmail.com';                 // SMTP username
        $this->mail->Password = 'SaS@2017';                           // SMTP password
        $this->mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $this->mail->Port = 587;                                    // TCP port to connect to
        // $this->mail->addAddress($email,'Hello Ak');     // Add a recipient
        // $this->mail->addReplyTo('akshayshelke@eracal.com', 'Information');
        // $this->mail->addCC('cc@example.com');
        // $this->mail->addBCC('bcc@example.com');


        $this->mail->isHTML(true);                                  // Set email format to HTML
    }

// end constructor

    function sendOTP($email, $otp) {

        $sub = "Do Not share OTP with any one";
        $this->mail->setFrom('shelkeakshay.2016@gmail.com', 'OTP(One Time Password) From Eracal Softwares');
        $this->mail->addAddress($email, $sub);
        $this->mail->Subject = 'OTP from Eracal Softwares Pvt. Ltd. :';
        $this->mail->Body = '<br><br> <b>OTP(One Time Password) to change password :</b> ' . $otp . '<br> <b>This OTP is valid Up to 30 minutes only';

        if (!$this->mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}

?>