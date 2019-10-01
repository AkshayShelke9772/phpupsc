<?php

include($_SERVER['DOCUMENT_ROOT'] . '/upscMcqsAPI/framework/controller/studentController.php');

class helper {

    public function getAuthorization($user_data) {
        $_SESSION['user_data'] = $user_data;
        $user_id = $user_data->user_id;
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $obj = (object) ['user_id' => $user_id, 'date' => $date, 'time' => $time];
        $json_obj = json_encode($obj);
        $encryptObj = $this->encrypt($json_obj);
        return $encryptObj;
    }

    public function encrypt($param) {
        $str = base64_encode($param);
        return $str;
    }

    public function decrypt($param) {
        $str = base64_decode($param);
        return $str;
    }

    public function getUserData($token) {
        $auth_obj = (object) ['user' => null];
        global $auth_obj;
        $cont = new studentController();
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $data = $this->decrypt($token);
        $userData = json_decode($data);
        if (!is_null($userData)) {
            $user_id = $userData->user_id;
            // get user data by user_id
            $userRs = $cont->getUserDataById($user_id);
            if ($userRs->statusCode == 200) {
                $user = $userRs->data['user'];
                @$auth_obj->user = $user;
                @$auth_obj->user->user_role = 'admin';
                if ($user->is_admin == '0') {
                    $auth_obj->user->user_role = 'student';
                }
                $res->statusCode = 200;
                $res->message = 'Got Data';
                $res->data['user'] = $user;
            } else {
                $res->statusCode = 403;
                $res->message = 'Could not get user data';
            }
        } else {
            $res->statusCode = 403;
            $res->message = 'Invalid Token';
        }
        return $res;
    }

    public function generateRandomOtp() {
        $iDigits = "135792468";
        $iOtp = "";

        for ($i = 1; $i <= 4; $i++) {
            $iOtp .= substr($iDigits, (rand() % (strlen($iDigits))), 1);
        }
        return $iOtp;
    }

}
