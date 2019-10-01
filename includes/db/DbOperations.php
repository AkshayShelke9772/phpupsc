<?php

class DbOperations {

    private $con;

    // function create connection
    function __construct() {
        require_once dirname(__FILE__) . '/db_connect.php';
        $db = new DB_CONNECT();
        $this->con = $db->connect();
    }

    // function register the user
    function register($fname, $lname, $email, $phone, $address, $password) {
        $fname = mysqli_real_escape_string($this->con, $fname);
        $lname = mysqli_real_escape_string($this->con, $lname);
        $password = mysqli_real_escape_string($this->con, $password);
        $phone = mysqli_real_escape_string($this->con, $phone);
        $email = mysqli_real_escape_string($this->con, $email);
        $address = mysqli_real_escape_string($this->con, $address);

        $stmt = $this->con->prepare("INSERT INTO `users`(`FirstName`, `LastName`, `Email`, `Mobile`, `Address`, `Password`) VALUES (?,?,?,?,?,?);");

        $stmt->bind_param("ssssss", $fname, $lname, $email, $phone, $address, $password);

        if ($stmt->execute()) {
            return true;
        } else {
            return "Email Or Mobile Number already Exist";
        }
    }

    // function register the user
    function login($username, $password) {
        // return $username;
        $password = mysqli_real_escape_string($this->con, $password);
        $username = mysqli_real_escape_string($this->con, $username);

        $sql = "select * from users where (email='$username' or mobile='$username') and password='$password'";

        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {

            $row = $result->fetch_array();
            $dataArray = [
                'UserId' => $row["UserId"],
                'FirstName' => $row["FirstName"],
                'LastName' => $row["LastName"],
                'Phone' => $row["Mobile"],
                'Email' => $row["Email"],
                'Address' => $row["Address"],
            ];
            return $dataArray;
        } else {
            return false;
        }
    }

    // function register the user
    function forgotPassword($userId, $username) {
        // return $username;

        $userId = mysqli_real_escape_string($this->con, $userId);
        $username = mysqli_real_escape_string($this->con, $username);

        $sql = "select email from users where (email='$username' or mobile='$username') and UserId='$userId'";

        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = $result->fetch_array();

            $otp = rand(10000, 50000);
            $email = $row["email"];

            $dataArray = [
                'statusCode' => 200,
                'email' => $email
            ];
            return $dataArray;
        } else {
            $dataArray = [
                'statusCode' => 201,
                'message' => 'The Entered Username is not valid (Re-enter it)'
            ];
            return $dataArray;
        }
    }

// save otp in database
    function saveOtp($userId, $otp) {
        $userId = mysqli_real_escape_string($this->con, $userId);
        $otp = mysqli_real_escape_string($this->con, $otp);
        $valid_time = strtotime(date('Y-m-d H:i:s'));

        $sql = "select * from forgotpass where UserId='$userId'";
        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {
            $stmt = $this->con->prepare("update forgotpass set OTP=?,Valid_Time=? where UserId=?;");
            $stmt->bind_param("ssi", $otp, $valid_time, $userId);
            if ($stmt->execute()) {
                return true;
            } return false;
        } else {
            $stmt = $this->con->prepare("INSERT INTO `forgotpass`(`UserId`, `OTP`, `Valid_Time`) VALUES (?,?,?);");
            $stmt->bind_param("sss", $userId, $otp, $valid_time);

            if ($stmt->execute())
                return true;
            else
                return true;
        }
    }

    function verifyOtp($userId, $otp) {
        $userId = mysqli_real_escape_string($this->con, $userId);
        $otp = mysqli_real_escape_string($this->con, $otp);

        $sql = "select * from forgotPass where UserId='$userId' and otp='$otp'";
        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = $result->fetch_array();

            $last = $row["Valid_Time"];
            $now = strtotime(date('Y-m-d H:i:s'));
            $diff = $now - $last;
            $minutes = $diff / 60;

            if ($minutes < 30) {
                $sql = "update forgotPass set validUser=1 where UserId='$userId' and otp='$otp'";
                mysqli_query($this->con, $sql);
                return 200; //otp verify success
            } else {
                $sql = "delete from forgotPass where UserId='$userId' and otp='$otp'";
                mysqli_query($this->con, $sql);
                return 203; //otp expire
            }
        } else {
            return 201; // invalid otp
        }
    }

// end verify()

    function changePassword($userId, $newPassword) {

        $sql = "select * from forgotPass where otp='$otp'";
        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {
            
        }

        $UserId = mysqli_real_escape_string($this->con, $userId);
        $newPassword = mysqli_real_escape_string($this->con, $newPassword);

        $stmt = $this->con->prepare("UPDATE users set password=? where UserId=?");
        $stmt->bind_param("si", $newPassword, $userId);
//
//        if ($stmt->execute()) {
//            if ($stmt->affected_rows > 0)) {
//                return 200; //password updated
//            } else {
//                return 201; //New password does not same with previous password
//            }
//        } else {
//            return 203; // update failed
//        }
    }

}

?>