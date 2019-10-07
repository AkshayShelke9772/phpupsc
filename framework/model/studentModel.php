<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/upscMcqsAPI/includes/db/db_connect.php';

class studentModel
{

    private $con;

    // function create connection
    function __construct() {

        $db = new DB_CONNECT();
        $this->con = $db->connect();
//        $this->close = $db->close();
    }

    function __destruct() {
        $db = new DB_CONNECT();
        $this->close = $db->close();
    }

    // function register the user
    function register($data, $is_Admin) {

        $res = (object) ['status' => FALSE, 'message' => 'User Could Not be register', 'data' => null];
        try {

            $stud_id = $this->_getMaxTableId($this->con, 'student_master', 'student_id');
            $name = trim($data->name);
            $email = trim($data->email);
            $contact = trim($data->contact);
            $password = strlen($data->password) > 0 ? sha1($data->password) : 'null';
            $nickname = isset($data->nickname) ? trim($data->nickname) : 'null';
            $address = isset($data->address) ? trim($data->address) : 'null';
            $pin_code = isset($data->pin_code) ? ($data->pin_code) : 'null';
            $created_date = date('Y-m-d H:i:s');
            $updated_date = date('Y-m-d H:i:s');
            $status = 'A';
            $is_Admin = $data->is_admin;

    //  { check user is already existed with email or phone and permenent_otp_verified is 1     
            $sql = "select * from student_master where (email_id ='$email' or phone_number='$contact')";
            $result = $this->con->query($sql);
            $result->execute();
            
            if($result->rowCount() === 1){ 
                    
                // check is permenent_otp_verified = 1 or 0 
                // if 1 then user is already success register
                // if 0 then user is register but not verified otp yet 
                if(!$this->isUserVerifiedPermanently($email,$contact)){ 
                    
                     // permenent_otp is not verified yet so need to Verify otp now 
                     $stud_id=$result->fetchAll()[0]["student_id"];
                     
                     $sql = "update student_master set email_id='$email' , phone_number='$contact' where (email_id ='$email' or phone_number='$contact')";
                     $result = $this->con->query($sql);
                     $result->execute();
                     
                     $res->status = true;
                     $res->data['stud_id'] = $stud_id;
                     $res->message = "You are already registered, Verification mandatory to login , please verify OTP"; 
                     return $res;
                } 
                else{
                    $res->status = false;
                    $res->message = "The Mobile-Number / Email-Id already registered";
                    return $res;
                }   

            }
            // user is not registered with this email-id or phone number yet
            else{
                $sql = "INSERT INTO `student_master`(
                    `student_id`, `full_name`, `email_id`, `phone_number`, `nick_name`,
                    `address`, `password`,`is_admin`, `pin_code`, 
                    `created_date`, `updated_date`, `status`) VALUES (
                        $stud_id, '$name', '$email', $contact, '$nickname',
                        '$address', '$password',$is_Admin, $pin_code, 
                        '$created_date', '$updated_date', '$status')";

                if ($this->con->query($sql)) {
                    // var_dump($this->con->lastInsertId());die;
                    $res->status = true;
                    $res->message = 'User Registered Successfully , Please verify Otp';
                    $res->data['stud_id'] = $stud_id;
                } else {
                    $res->status = false;
                    $res->message = 'Could not save data';
                }
            } //

        } catch (PDOException $e) {
            // if (strpos($e->getMessage(), 'phone_number') !== false || strpos($e->getMessage(), 'email_id') !== false) {
                $res->status = false;
                $res->message = "The Mobile-Number / Email-Id already registered";
            // }
        }
        $this->close = null;
        return $res;
    }
	
	
	function isUserVerifiedPermanently($email,$mobile=null){
        $sql = "select * from student_master where (email_id='$email' or phone_number='$email' or email_id='$mobile' or phone_number='$mobile') and permanent_otp_verified = 1";
        $result = $this->con->prepare($sql);
        $result->execute();
        if($result->rowCount() > 0 ){ return true;}
        else {return false;}
    }

//     simple login :
//     is_by_social_media_login field 0 

// google ->

//     1. exist with client id and email
//         yes:
//          successfull login & is_by_social_media_login field 1
            
//         only email:
//             if user already exist but client id not match or not found and only email match 
//                  client id update , login_by update , login , is_by_socila_media_login field 1 , 
//                  profile_url update

//     2. not exist 
//         client id and email register.
//             full_name , email, client-id , provider g/f , profile url , nickname.
//             is_by_socila_media_login field 1

    function socialLogin($data){

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch', 'data' => null];

        if(!isset($data->email_id)){ return $res; }

        $full_name = $data->full_name;
        $nick_name = $data->nick_name;
        $login_by = $data->login_by;
        $email_id = $data->email_id;
        $is_by_social_media_login = 1;
        $client_id = $data->client_id;
        $profile_image = $data->profile_image;
        $is_admin_social_login = $data->is_admin_social_login;

        $sql = "select * from student_master where (email_id='$email_id' or phone_number='$email_id')";
        $result = $this->con->prepare($sql);
        if ($result->execute()) {


            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) { //user existed login with social media and update the social media only

                $update_sql = "update student_master set permanent_otp_verified=1,full_name='$full_name' , nick_name='$nick_name' , login_by='$login_by' , client_id='$client_id' , is_by_social_media='$is_by_social_media_login' , profile_image='$profile_image' where email_id='$email_id'";

                $update_result = $this->con->prepare($update_sql);
                $update_result->execute();
                if($update_result->rowCount() >= 0 || $update_result->rowCount() <= 1)
                {
                    $get_data_after_update = "select * from student_master where (email_id='$email_id' or phone_number='$email_id')";
                    $obj = (object) [];
                    foreach ($this->con->query($get_data_after_update) as $row) {
                        $obj->user_id = $row['student_id'];
                        $obj->full_name = $row['full_name'];
                        $obj->email_id = $row['email_id'];
                        $obj->phone_number = $row['phone_number'];
                        $obj->nick_name = $row['nick_name'];
                        $obj->address = $row['address'];
                        $obj->profile_image =$row['profile_image'];
                        $obj->is_verify_otp = $row['is_verify_otp'];
                        $obj->password = $row['password'];
                        $obj->is_admin = $row['is_admin'];
                        $obj->pin_code = $row['pin_code'];
                        $obj->is_by_social_media = $row['is_by_social_media'];
                        $obj->login_by = $row['login_by'];
                        $obj->client_id = $row['client_id'];
                        $obj->Status = $row['Status'];
                    }
                $res->status = true;
                $res->message = 'Student Login Successfull with social media';
                $res->data['user_data'] = $obj;
            }else{
                $res->status = false;
                $res->message = "User can't login with this method right now , please go mannually";
            }

         } else { // user not register yet , register user with social media details
                     

                    try {
                        $stud_idd = $this->_getMaxTableId($this->con, 'student_master', 'student_id');
                        $name = trim($full_name);
                        $email = trim($email_id);
                        $nickname = trim($nick_name);
                        $is_Admin = $is_admin_social_login;
                        $created_date = date('Y-m-d H:i:s');
                        $updated_date = date('Y-m-d H:i:s');
                        $status = 'A';
                        $is_by_social_media = 1;
                        $profileImage = $profile_image;

                        // if user is not registered with our website password set to 0 firs time
                        $sql = "INSERT INTO `student_master`(`student_id`, `password`,`profile_image`, `full_name`, `email_id`, `nick_name`,`is_admin`,`is_by_social_media`,`login_by`, `client_id`, `created_date`, `updated_date`, `status`) VALUES ($stud_idd,'0','$profileImage','$name', '$email', '$nickname', $is_Admin, $is_by_social_media, '$login_by','$client_id', '$created_date', '$updated_date', '$status')";

                        if ($this->con->query($sql)) {
                            
                            $res->status = true;
                            $res->message = 'Student Registered Successfully';
                            @$obj->user_id = $stud_idd;
                            $obj->full_name = $name;
                            $obj->email_id = $email;
                            $obj->phone_number = NULL;
                            $obj->nick_name = $nickname;
                            $obj->address = NULL;
                            $obj->profile_image = $profileImage;
                            $obj->is_admin = $is_admin_social_login;
                            $obj->pin_code = NULL;
                            $obj->is_by_social_media = $is_by_social_media;
                            $obj->login_by = $login_by;
                            $obj->Status = $status;
                        
                            $res->data['user_data'] = $obj;
                            $this->close = null;

                        }else{
                            $res->status = false;
                            $res->message = 'Student cant register with the social login , please try again';
                        } 
                    }catch (PDOException $e) {
                            $res->status = false;
                            $res->message = var_dump($this->con->errorInfo());die;;
                        }//catch end
                    } //else end
        } //first if end
        return $res;
    }



//     simple login :
//     is_by_social_media_login field 0 

// google ->

//     1. exist with client id and email
//         yes:
//          successfull login & is_by_social_media_login field 1
            
//         only email:
//             if user already exist but client id not match or not found and only email match 
//                  client id update , login_by update , login , is_by_socila_media_login field 1 , 
//                  profile_url update

//     2. not exist 
//         client id and email register.
//             full_name , email, client-id , provider g/f , profile url , nickname.
//             is_by_socila_media_login field 1

    // function socialLogin($data){

    //     $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch', 'data' => null];

    //     if(!isset($data->email_id)){ return $res; }

    //     $full_name = $data->full_name;
    //     $nick_name = $data->nick_name;
    //     $login_by = $data->login_by;
    //     $email_id = $data->email_id;
    //     $is_by_social_media_login = 1;
    //     $client_id = $data->client_id;
    //     $profile_image = $data->profile_image;
    //     $is_admin_social_login = $data->is_admin_social_login;

    //     $sql = "select * from student_master where (email_id='$email_id' or phone_number='$email_id')";
    //     $result = $this->con->prepare($sql);
    //     if ($result->execute()) {


    //         $number_of_rows = $result->fetchColumn();
    //         if ($number_of_rows > 0) { //user existed login with social media and update the social media only

    //             $update_sql = "update student_master set permanent_otp_verified=1,full_name='$full_name' , nick_name='$nick_name' , login_by='$login_by' , client_id='$client_id' , is_by_social_media='$is_by_social_media_login' , profile_image='$profile_image' where email_id='$email_id'";

    //             $update_result = $this->con->prepare($update_sql);
    //             $update_result->execute();
    //             if($update_result->rowCount() >= 0 || $update_result->rowCount() <= 1)
    //             {
    //                 $get_data_after_update = "select * from student_master where (email_id='$email_id' or phone_number='$email_id')";
    //                 $obj = (object) [];
    //                 foreach ($this->con->query($get_data_after_update) as $row) {
    //                     $obj->user_id = $row['student_id'];
    //                     $obj->full_name = $row['full_name'];
    //                     $obj->email_id = $row['email_id'];
    //                     $obj->phone_number = $row['phone_number'];
    //                     $obj->nick_name = $row['nick_name'];
    //                     $obj->address = $row['address'];
    //                     $obj->profile_image =$row['profile_image'];
    //                     $obj->is_verify_otp = $row['is_verify_otp'];
    //                     $obj->password = $row['password'];
    //                     $obj->is_admin = $row['is_admin'];
    //                     $obj->pin_code = $row['pin_code'];
    //                     $obj->is_by_social_media = $row['is_by_social_media'];
    //                     $obj->login_by = $row['login_by'];
    //                     $obj->client_id = $row['client_id'];
    //                     $obj->Status = $row['Status'];
    //                 }
    //             $res->status = true;
    //             $res->message = 'Student Login Successfull with social media';
    //             $res->data['user_data'] = $obj;
    //         }else{
    //             $res->status = false;
    //             $res->message = "User can't login with this method right now , please go mannually";
    //         }

    //      } else { // user not register yet , register user with social media details
                     

    //                 try {
    //                     $stud_idd = $this->_getMaxTableId($this->con, 'student_master', 'student_id');
    //                     $name = trim($full_name);
    //                     $email = trim($email_id);
    //                     $nickname = trim($nick_name);
    //                     $is_Admin = $is_admin_social_login;
    //                     $created_date = date('Y-m-d H:i:s');
    //                     $updated_date = date('Y-m-d H:i:s');
    //                     $status = 'A';
    //                     $is_by_social_media = 1;
    //                     $profileImage = $profile_image;

    //                     // if user is not registered with our website password set to 0 firs time
    //                     $sql = "INSERT INTO `student_master`(`student_id`, `password`,`profile_image`, `full_name`, `email_id`, `nick_name`,`is_admin`,`is_by_social_media`,`login_by`, `client_id`, `created_date`, `updated_date`, `status`) VALUES ($stud_idd,'0','$profileImage','$name', '$email', '$nickname', $is_Admin, $is_by_social_media, '$login_by','$client_id', '$created_date', '$updated_date', '$status')";

    //                     if ($this->con->query($sql)) {
                            
    //                         $res->status = true;
    //                         $res->message = 'Student Registered Successfully';
    //                         @$obj->user_id = $stud_idd;
    //                         $obj->full_name = $name;
    //                         $obj->email_id = $email;
    //                         $obj->phone_number = NULL;
    //                         $obj->nick_name = $nickname;
    //                         $obj->address = NULL;
    //                         $obj->profile_image = $profileImage;
    //                         $obj->is_admin = $is_admin_social_login;
    //                         $obj->pin_code = NULL;
    //                         $obj->is_by_social_media = $is_by_social_media;
    //                         $obj->login_by = $login_by;
    //                         $obj->Status = $status;
                        
    //                         $res->data['user_data'] = $obj;
    //                         $this->close = null;

    //                     }else{
    //                         $res->status = false;
    //                         $res->message = 'Student cant register with the social login , please try again';
    //                     } 
    //                 }catch (PDOException $e) {
    //                         $res->status = false;
    //                         $res->message = var_dump($this->con->errorInfo());die;;
    //                     }//catch end
    //                 } //else end
    //     } //first if end
    //     return $res;
    // }



    function saveQuestions($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $qu_id = $this->_getMaxTableId($this->con, 'question_masters', 'id');
        $sub_cat_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $question = trim($data->question);
        $description = trim($data->description);
        $option1 = trim($data->option1);
        $option2 = trim($data->option2);
        $option3 = trim($data->option3);
        $option4 = trim($data->option4);
        $option5 = trim($data->option5);
        $answer = trim($data->answer);
        $answer_description = trim($data->answer_description);
        $created_date = date('Y-m-d H:i:s');
        $updated_date = date('Y-m-d H:i:s');
        $status = 'A';
        $stmt = $this->con->prepare("INSERT INTO `question_masters`(`id`, `sub_category_id`, `question`, `description`, `option1`, `option2`,`option3`, `option4`, `option5`, `answer`,`answer_description`, `status`, `created_date`) VALUES ('$qu_id', $sub_cat_id, '$question', '$description', '$option1', '$option2', '$option3', '$option4', '$option5', '$answer', '$answer_description', '$status', '$created_date')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions added Successfully';
            $res->data['id'] = $qu_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function addUsersQuestion($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $qu_id = $this->_getMaxTableId($this->con, 'users_question', 'question_id');
        $user_name = trim($data->user_name);
        $user_email = trim($data->user_email);
        $question = trim($data->question);
        $created_date = date('Y-m-d H:i:s');
        $stmt = $this->con->prepare("INSERT INTO `users_question`(`question_id`, `user_name`, `user_email`, `question`,`created_date`) VALUES ('$qu_id', '$user_name','$user_email', '$question', '$created_date')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions added Successfully';
            $res->data['question_id'] = $qu_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function submitExam($data) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_student_mapper', 'id');
        $created_date = date('Y-m-d H:i:s');
        $attempted_date = date('Y-m-d H:i:s');
        $total_time_taken = date('Y-m-d H:i:s', strtotime($data->total_time_taken));
        $created_by = 1;
        $status = 'A';
        $result = 0.00;
        $student_id = $auth_obj->user->user_id;
        $stmt = $this->con->prepare("INSERT INTO `exam_student_mapper`(`id`, `exam_id`, `student_id`, `attempted_date`, `result_exam`, `total_time_all_que`, `created_date`, `created_by`) VALUES ('$id', '$data->exam_id', '$student_id', '$attempted_date', '$result', '$total_time_taken', '$created_date', '$created_by')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Submited Successfully';
            $res->data['id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function addPaymentRequest($data, $user_id) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $upcpayrq_id = $this->_getMaxTableId($this->con, 'users_exam_payment_request', 'upcpayrq_id');
        $created_date = date('Y-m-d H:i:s');
        $student_id = $auth_obj->user->user_id;
        $discount = 0.0;
        $currency = 'INR';
        $order_id = 'null';
        $notes = 'null';
        $stmt = $this->con->prepare("INSERT INTO `users_exam_payment_request`(`upcpayrq_id`, `user_id`, `exam_id`, `order_id`, `discount`, `amount`, `currency`, `status`, `notes`, `created_date`) VALUES ($upcpayrq_id, $user_id,$data->exam_id, $order_id, $discount, '$data->amount', '$currency', 'created', $notes, '$created_date')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Payment Request Saved Successfully';
            $res->data['upcpayrq_id'] = $upcpayrq_id;
        } else {
            $res->status = false;
            $res->message = 'Could not add payment request';
        }
        $this->close = null;
        return $res;
    }

    // function savePaymentDetails($payment_id, $payRqId, $data, $desc, $exam_id) {
    //     global $auth_obj;
    //     $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
    //     $upcpay_id = $this->_getMaxTableId($this->con, 'users_exam_payments', 'upcpay_id');
    //     $created_date = date('Y-m-d H:i:s');
    //     $discount = 0.0;
    //     $currency = 'INR';
    //     $order_id = 'null';
    //     $refund_status = is_null($data->refund_status) ? 'null' : $data->refund_status;
//        $notes = is_null($data->notes) ? 'null' : $data->notes;
    //     $notes = 'null';
    //     $card_id = is_null($data->card_id) ? 'null' : $data->card_id;
    //     $bank = is_null($data->bank) ? 'null' : $data->bank;
    //     $wallet = is_null($data->wallet) ? 'null' : $data->wallet;
    //     $vpa = is_null($data->vpa) ? 'null' : "$data->vpa";
    //     $desc = is_null($desc) ? 'null' : $desc;
    //     $email = is_null($data->email) ? 'null' : $data->email;
    //     $contact = is_null($data->contact) ? 'null' : $data->contact;
    //     $fee = is_null($data->fee) ? 'null' : $data->fee;
    //     $tax = is_null($data->tax) ? 'null' : $data->tax;
    //     $error_code = is_null($data->error_code) ? 'null' : $data->error_code;
    //     $error_description = is_null($data->error_description) ? 'null' : $data->error_description;
    //     $db_date = date('Y-m-d H:i:s', ($data->created_at));
    //     $stmt = $this->con->prepare("INSERT INTO `users_exam_payments`(`upcpay_id`, `upcpayrq_id`, `exam_id`, `pay_id`, `amount`, `currency`, `status`,`method`, `description`,`refund_status`, `captured`,`card_id`, `bank`, `wallet`,`vpa`, `email`,`contact`,`notes`,`fee`, `tax`,`error_code`, `error_description`,`created_at`, `db_date`) VALUES ($upcpay_id, $payRqId, $exam_id, '$payment_id',$data->amount,'$currency','$data->status','$data->method', $desc, $refund_status, '$data->captured',$card_id, $bank,$wallet, '$vpa', '$email', $contact, $notes, $fee, $tax, $error_code, $error_description, '$created_date', '$db_date' )");
    //     if ($stmt->execute()) {
    //         $res->status = true;
    //         $res->message = 'Payment Request Saved Successfully';
    //         $res->data['upcpay_id'] = $upcpay_id;
    //     } else {
    //         $res->status = false;
    //         $res->message = 'Could not add payment request';
    //     }
    //     $this->close = null;
    //     return $res;
    // }



    //new code of savepaymentdetails by priyanka ma'am on 4-10-2019
    function savePaymentDetails($payment_id, $payRqId, $data, $desc, $exam_id) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $upcpay_id = $this->_getMaxTableId($this->con, 'users_exam_payments', 'upcpay_id');
        $created_date = date('Y-m-d H:i:s');
        $discount = 0.0;
        $currency = 'INR';
        $order_id = 'null';
        $refund_status = is_null($data->refund_status) ? '' : $data->refund_status;
//        $notes = is_null($data->notes) ? 'null' : $data->notes;
        $notes = 'null';
        $card_id = is_null($data->card_id) ? '' : $data->card_id;
        $bank = is_null($data->bank) ? '' : $data->bank;
        $wallet = is_null($data->wallet) ? '' : $data->wallet;
        $vpa = is_null($data->vpa) ? '' : "$data->vpa";
        $desc = is_null($desc) ? '' : $desc;
        $email = is_null($data->email) ? '' : $data->email;
        $contact = is_null($data->contact) ? '' : $data->contact;
        $fee = is_null($data->fee) ? '' : $data->fee;
        $tax = is_null($data->tax) ? '' : $data->tax;
        $error_code = is_null($data->error_code) ? '' : $data->error_code;
        $error_description = is_null($data->error_description) ? '' : $data->error_description;
        $db_date = date('Y-m-d H:i:s', ($data->created_at));
        $stmt = $this->con->prepare("INSERT INTO `users_exam_payments`(`upcpay_id`, `upcpayrq_id`, `exam_id`, `pay_id`, `amount`, `currency`, `status`,`method`, `description`,`refund_status`, `captured`,`card_id`, `bank`, `wallet`,`vpa`, `email`,`contact`,`notes`,`fee`, `tax`,`error_code`, `error_description`,`created_at`, `db_date`) VALUES ($upcpay_id, $payRqId, $exam_id, '$payment_id',$data->amount,'$currency','$data->status','$data->method', '$desc', '$refund_status', '$data->captured','$card_id', '$bank', '$wallet', '$vpa', '$email', '$contact', '$notes', '$fee', '$tax', '$error_code', '$error_description', '$created_date', '$db_date' )");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Payment Request Saved Successfully';
            $res->data['upcpay_id'] = $upcpay_id;
        } else {
            $res->status = false;
            $res->message = 'Could not add payment request';
        }
        $this->close = null;
        return $res;
    }

    function giveFeedbackToExam($data) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'feedback_master', 'id');
        $created_date = date('Y-m-d H:i:s');
        $created_by = 1;
        $status = 'A';
        $user_id = $auth_obj->user->user_id;
        $stmt = $this->con->prepare("INSERT INTO `feedback_master`(`id`, `feedback_id`, `exam_id`, `student_id`, `comment`, `rating`, `created_date`, `created_by`) VALUES ($id, $data->qu_id, $data->exam_id, $user_id, '$data->comment', $data->rating, '$created_date', $created_by)");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Feedback Added Successfully';
            $res->data['id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }
	
    function saveRatings($exam_id, $rating) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_ratings', 'id');
        $created_date = date('Y-m-d H:i:s');
        $user_id = $auth_obj->user->user_id;
        $stmt = $this->con->prepare("INSERT INTO `exam_ratings`(`id`, `exam_id`, `rating`, `user_id`,`created_date`) VALUES ($id, $exam_id, $rating, $user_id, '$created_date')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'ratings Added Successfully';
            $res->data['id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }


    // function register the user
     function getUserData($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch for this user , Please try again', 'data' => null,'statusCode'=> NULL];
        $mobile = trim($data->mobile);

        $sql = "select * from student_master where (email_id='$mobile' or phone_number='$mobile')";
        $result = $this->con->prepare($sql);
        $result->execute();
        // var_dump($result->rowCount());die;
            

        if ($result->rowCount() > 0 && $result->fetch()["password"] === sha1($data->password)) {
            
            if($this->isUserVerifiedPermanently($mobile)){
                
                $number_of_rows = $result->rowCount();
                if ($number_of_rows > 0) {

                    $obj = (object) [];
                    foreach ($this->con->query($sql) as $row) {
                        $obj->user_id = $row['student_id'];
                        $obj->full_name = $row['full_name'];
                        $obj->email_id = $row['email_id'];
                        $obj->phone_number = $row['phone_number'];
                        $obj->nick_name = $row['nick_name'];
                        $obj->address = $row['address'];
                        $obj->profile_image =$row["profile_image"];
                        $obj->is_verify_otp = $row['is_verify_otp'];
                        $obj->is_admin = $row['is_admin'];
                        $obj->pin_code = $row['pin_code'];
                        $obj->is_by_social_media = $row['is_by_social_media'];
                        $obj->login_by = $row['login_by'];
                        $obj->client_id = $row['client_id'];
                        $obj->Status = $row['Status'];
                    }
                    $res->status = true;
                    $res->message = 'Got Data Successfully';
                    $res->data['user_data'] = $obj;
                }else{ //username & password & otp verification found correct but data couldn't fetch 
                    // from database
                    return $res;
                } 
            } else { //valid username & pass but otp not verified

                $result->execute();
                $r = $result->fetch();

                $res->status = false;
                $res->statusCode = 201;
                $res->stud_id = $r["student_id"];
                $res->mobile = $r["phone_number"];
                $res->email = $r["email_id"];
                $res->message = 'User need to verify the account , please verify';
                $res->data['user_data'] = null;

            }
        }else{ //invalid username or password
            $res->status = false;
            $res->message = 'Invalid Username or Password';
            $res->data['user_data'] = null;
        } 
    return $res;
    }

    function getUserDataById($user_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch', 'data' => null];
        $sql = "select * from student_master where (student_id='$user_id')";
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->user_id = $row['student_id'];
                    $obj->full_name = $row['full_name'];
                    $obj->email_id = $row['email_id'];
                    $obj->phone_number = $row['phone_number'];
                    $obj->nick_name = $row['nick_name'];
                    $obj->address = $row['address'];
                    $obj->is_verify_otp = $row['is_verify_otp'];
                    $obj->password = $row['password'];
                    $obj->is_admin = $row['is_admin'];
                    $obj->pin_code = $row['pin_code'];
                    $obj->Status = $row['Status'];
                    $obj->total_coins = $row['total_coins'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['user'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['user'] = null;
            }
        }
        return $res;
    }

    // function register the user
  
  
  
  
    function forgotPassword($data) {

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch', 'data' => null];
        
        $username =  $data->mobile;

        $sql = "select * from student_master where (email_id='$username' or phone_number='$username')";
        $result = $this->con->prepare($sql);

        if ($result->execute() && $result->rowCount()>0) 
        {
            $update_is_verify_at_zero = "update student_master set is_verify_otp=0 where (email_id='$username' or phone_number='$username')";
            $r = $this->con->prepare($update_is_verify_at_zero);
            $r->execute();
 
            foreach($this->con->query(($sql)) as $row){
                $res->stud_id = $row["student_id"];
                $res->stud_username = $username;
            };
            $res->status = true;
            
        }else{
            $res->status = false;
            $res->message = "Username does not exist";
        }

        return $res;

    }




    // function resetPassword($data){
    //     $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];

    //     $username =  $data->mobile;
    //     $user_new_password=sha1($data->user_new_password);

    //     $sql = "update student_master set password='$user_new_password' where (email_id='$username' or phone_number='$username')";
    //     $result = $this->con->prepare($sql);

    //     if ($result->execute()) 
    //     {
            
    //         if($result->rowCount() >= 0 || $result->rowCount()<=1){
    //             $res->status = true;
    //             $res->message = "password changed successfull";
    //         }else{
    //             $res->status = false;
    //             $res->message = "Password could not be update , Please try  Again";
    //         }
    //     }else{
    //         $res->status = false;
    //         $res->message = "Probleme to update your password please try again";
    //     } 
    //     return $res;
    // }
    // reset passw


    function resetPassword($data){
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];

        $username =  $data->mobile;
        $user_new_password=sha1($data->user_new_password);

        $sql = "update student_master set password='$user_new_password' where (email_id='$username' or phone_number='$username')";
        $result = $this->con->prepare($sql);

        if ($result->execute()) 
        {
            
            if($result->rowCount() >= 0 || $result->rowCount()<=1){
                $res->status = true;
                $res->message = "password changed successfull";
            }else{
                $res->status = false;
                $res->message = "Password could not be update , Please try  Again";
            }
        }else{
            $res->status = false;
            $res->message = "Probleme to update your password please try again";
        } 
        return $res;
    }
    // reset passw


// save otp in database
    function saveOtp($userId, $otp) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $otp = trim($otp);
        $valid_time = (date('Y-m-d H:i:s'));
//        $stmt = "update student_master set otp= " . $otp . ", Valid_Time= " . $valid_time . " where student_id= " . $userId . ";";
        $stmt = sprintf('update student_master set otp=%s, Valid_Time = "%s" where student_id=%s ', $otp, $valid_time, $userId);
        if ($this->con->query($stmt)) {
            $res->status = true;
            $res->message = 'OTP Saved Successfully';
        } else {
            $res->status = false;
            $res->message = 'Could not save otp';
        }
        $this->con = null;
        return $res;
    }

    
	
	    function verifyOtp($data) {

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        
        $mobile = $data->mobile;
        $otp = trim($data->otp);
        $sql = "select * from student_master where (email_id='$mobile' or phone_number='$mobile') and otp='$otp' and is_verify_otp = 0";
        $result = $this->con->prepare($sql);
        if ($result->execute() && $result->rowCount() > 0) {

            $number_of_rows = $result->fetchColumn();

            if ($number_of_rows > 0) {

                foreach ($this->con->query($sql) as $row) {
                    $last = $row["Valid_Time"];
                    $now = (date('Y-m-d H:i:s'));
                    $diff = strtotime($now) - strtotime($last);
                    $minutes = $diff / 60;
                }
                if ($minutes < 10) {
                    $updateSql = "update student_master set permanent_otp_verified=1 , is_verify_otp = 1 where phone_number='$mobile' or email_id='$mobile'";
                    $sql1 = $this->con->prepare($updateSql);
                    if ($sql1->execute()) {
                        $res->status = true;
                        $res->message = 'Verify OTP successfully';
                    } else {
                        $res->status = false;
                        $res->message = 'Could not update OTP status';
                    }
                } else {

                    // if($isForRegister){
                    //     $deletesql = "delete from student_master where phone_number='$mobile' and otp='$otp'";
                    //     $sql2 = $this->con->prepare($deletesql);
                    //     if ($sql2->execute()) {
                    //         $res->status = false;
                    //         $res->message = 'Timeout. Please fill the form again'; //otp expire
                    //     }
                    // }else{
                        $res->status = false;
                        $res->message ="OTP Verification timeout Please try again or resend it";
                    // }
                }
            } else {
                $res->status = false;
                $res->message = 'Invalid OTP , Please try again or resend it';
            }
        } else {
            $res->status = false;
            $res->message = 'Invalid OTP , Please try again or resend it'; //Invalid OTP
        }
        return $res;

    }




    function changePassword($userId, $newPassword) {

        $sql = "select * from forgotPass where otp=' $otp

                 '";
        $result = mysqli_query($this->con, $sql);

        if (mysqli_num_rows($result) > 0) {
            
        }

        $UserId = mysqli_real_escape_string($this->con, $userId);
        $newPassword = mysqli_real_escape_string($this->con, $newPassword);

        $stmt = $this->con->prepare("UPDATE users set password=? where UserId=?");
        $stmt->bind_param("si", $newPassword, $userId);
    }

    function getExamResultByStudentId($stud_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("select es.`id`, es.`attempted_date`, es.`result_exam`, es.`total_time_all_que`, s.`full_name`, s.`phone_number`, e.`exam_name` FROM `exam_student_mapper` es INNER JOIN `exam_master` e
 ON es.`exam_id` =  e.`exam_id` INNER JOIN `student_master` s ON es.`student_id` =  s.`student_id` where es.`student_id` = %s", $stud_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->total_time_all_que = $row['total_time_all_que'];
                    $obj->full_name = $row['full_name'];
                    $obj->phone_number = $row['phone_number'];
                    $obj->exam_name = $row['exam_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_result_set'] = $arr;
            }
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['exam_result_set'] = [];
        }
        return $res;
    }



    
    function getChartByDifficultyLevels($exam_id){


        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];

        $arr = array('easy','very_easy','moderate','difficult','very_difficult');
        $arr1= array();


        foreach($arr as $i){
        
        $sql = "Select count(q.question) as total From exam_question_mapper as eq
                INNER JOIN exam_master as e on e.exam_id = eq.exam_id 
                INNER JOIN question_master q on eq.que_id = q.id and q.difficullty_level='$i'
                where e.exam_id='$exam_id'";

        $result = $this->con->prepare($sql);

        if ($result->execute()) {
            
            $number_of_rows = $result ->rowCount();
            $data = $result->fetchAll();

            if ($number_of_rows > 0) {
               
                array_push($arr1,$data[0]['total']);
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->exam_id = $exam_id;
                $res->difficulty_levels = $arr;
                $res->each_level_count = $arr1;
        
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['data'] = [];
                return $res;
            }
            
        }
    }//foreach end

    return $res;

    }







    function getChartByKewords($exam_id){

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];

        $arr = array();
        
        $sql = "Select q.keywords as keywords From exam_question_mapper as eq
                INNER JOIN exam_master as e on e.exam_id = eq.exam_id 
                INNER JOIN question_master q on eq.que_id = q.id
                where e.exam_id='$exam_id'";

        $result = $this->con->prepare($sql);

        if ($result->execute()) {
            
            $number_of_rows = $result ->rowCount();
            $data = $result->fetchAll();

            if ($number_of_rows > 0) {
               
                // array_push($arr1,$data[0]['total']);
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->exam_id = $exam_id;

                // seperate the , from resppnse and add it into array
                // response is ["java,php" , "php" , "java,dotnet"] of keywords
                // convert into [0=> ["java","php"], 1=>["php"],2=> ["java","dotnet"] ]
                foreach($data as $i){
                    array_push($arr,explode(",",$i["keywords"]));
                }

                // two dimentional to one
                //convert ["java","php","php","java","dotnet"]
                $arr = call_user_func_array('array_merge', $arr);

                //sort the array ascending order 
                //["dotnet","java","java","php","php"]
                sort($arr);

                // remove duplications from an array
                //["dotnet",java","php"]
                $arr = array_filter($arr);

                // logic to count the each value number of occurances in an array
                $a = [] ; $b = []; $prev = null; $i=0;
                sort($arr);

                //get only unique values in array
                //it returns the array unique values in object format
                $arr1 =  array_unique($arr);

                //convert an object into array
                $res->data["keywords"] = array_merge($arr1,array());
                
                for ( $i = 0; $i < count($arr); $i++ ) {
                    if ( $arr[$i] !== $prev ) {
                        array_push($a,$arr[$i]);
                        array_push($b,1);
                    } else {
                        $b[count($b)-1]++;
                    }
                    $prev = $arr[$i];
                }
                // count each of item occurance present in that array
                $res->data["keywordwise_count"] = $b; 
                
                
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['data'] = [];
                return $res;
            }
            
        }
        

    return $res;

    }



function scoreHistoryOne($user_id){

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];

        $arr = array();
        
        // $sql = "Select q.keywords as keywords From exam_question_mapper as eq
        //         INNER JOIN exam_master as e on e.exam_id = eq.exam_id 
        //         INNER JOIN question_master q on eq.que_id = q.id
        //         where e.exam_id='$exam_id'";

        $sql = "select count(edrm.id) from exam_details_result_mapper edrm
                INNER JOIN  exam_student_mapper esm on edrm.exam_stud_map_id = esm.id 
                INNER JOIN student_master sm on sm.student_id = esm.student_id 
                where sm.student_id=$user_id ORDER BY esm.attempted_date DESC";

        $result = $this->con->prepare($sql);

        if ($result->execute()) {
            
            $number_of_rows = $result ->rowCount();
            $data = $result->fetchAll();

            // var_dump(json_encode($number_of_rows));die;

            if ($number_of_rows > 0) {
               
                // array_push($arr1,$data[0]['total']);
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data = $data;

                
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['data'] = [];
                return $res;
            }
            
        }
        

    return $res;

    }



    

    function getExamResult() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $sql = sprintf("select es.`id`, es.`attempted_date`, es.`result_exam`, es.`total_time_all_que`, s.`full_name`, s.`phone_number`, e.`exam_name` FROM `exam_student_mapper` es INNER JOIN `exam_master` e
 ON es.`exam_id` =  e.`exam_id` INNER JOIN `student_master` s ON es.`student_id` =  s.`student_id` where es.`attempted_date` > '%s' AND  es.`attempted_date` < '%s'", $today_start, $today_end);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->total_time_all_que = $row['total_time_all_que'];
                    $obj->full_name = $row['full_name'];
                    $obj->phone_number = $row['phone_number'];
                    $obj->exam_name = $row['exam_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_result_set'] = $arr;
            }
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['exam_result_set'] = [];
        }
        return $res;
    }

    function getExamResultInDetails() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $sql = sprintf("select es.`id`, es.`attempted_date`, es.`result_exam`, es.`total_time_all_que`, s.`full_name`, s.`phone_number`, e.`exam_name` FROM `exam_student_mapper` es INNER JOIN `exam_master` e
 ON es.`exam_id` =  e.`exam_id` INNER JOIN `student_master` s ON es.`student_id` =  s.`student_id` where es.`attempted_date` > '%s' AND  es.`attempted_date` < '%s'", $today_start, $today_end);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->total_time_all_que = $row['total_time_all_que'];
                    $obj->full_name = $row['full_name'];
                    $obj->phone_number = $row['phone_number'];
                    $obj->exam_name = $row['exam_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_result_set'] = $arr;
            }
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['exam_result_set'] = [];
        }
        return $res;
    }

    function getQuestionSetOfDailyMcqs() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "select * from question_masters where status = 'A'";
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->description = $row['description'];
                    $obj->answer = $row['answer'];
                    $obj->question = (object) ['options' => []];
                    $obj->question->que_name = $row['question'];
                    $obj->question->options[] = $row['option1'];
                    $obj->question->options[] = $row['option2'];
                    $obj->question->options[] = $row['option3'];
                    $obj->question->options[] = $row['option4'];
                    $obj->question->options[] = $row['option5'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_set'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_set'] = $arr;
            }
        }
        return $res;
    }

    function getWeeklyTwentyMCQs($data) {
        $exam_type_id = $data->exam_type_id;
        $today = date('Y-m-d');
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "Select qm.`id`, qm.`question`, qm.`option1`, qm.`option2`, qm.`option3`, qm.`option4`, qm.`option5`, emt.`duration` from `exam_question_mapper` eqm INNER JOIN `question_master` qm ON qm.`id` = eqm.`que_id` INNER JOIN `exam_master` em ON em.`exam_id` = eqm.`exam_id` INNER JOIN `exam_type_master` emt ON emt.`id` = em.`exam_type_id` WHERE em.`exam_type_id` = " . $exam_type_id . ' AND em.`is_publish` = 1';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
//                  $obj->sub_category_id = $row['sub_category_id'];
//                  $obj->description = $row['description'];
//                  $obj->answer = $row['answer'];
                    $obj->duration = $row['duration'];
                    $obj->question = (object) ['options' => []];
                    $obj->question->que_name = $row['question'];
                    $obj->question->options[] = $row['option1'];
                    $obj->question->options[] = $row['option2'];
                    $obj->question->options[] = $row['option3'];
                    $obj->question->options[] = $row['option4'];
                    $obj->question->options[] = $row['option5'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_set'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_set'] = $arr;
            }
        }
        return $res;
    }

    function getExamQuestionsByExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "Select qm.`id`, et.`duration`, qm.`question`, qm.`option1`, qm.`option2`, qm.`option3`, qm.`option4`, qm.`option5` from `exam_question_mapper` eqm INNER JOIN `question_master` qm ON qm.`id` = eqm.`que_id` 
INNER JOIN `exam_master` e ON e.`exam_id` = eqm.`exam_id` INNER JOIN `exam_type_master` et ON et.`id` = e.`exam_type_id` WHERE eqm.`exam_id` = " . $exam_id;
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->duration = $row['duration'];
					$obj->question = (object) ['options' => []];
                    $obj->question->que_name = $row['question'];
                    $obj->question->options[] = $row['option1'];
                    $obj->question->options[] = $row['option2'];
                    $obj->question->options[] = $row['option3'];
                    $obj->question->options[] = $row['option4'];
                    $obj->question->options[] = $row['option5'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_set'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_set'] = $arr;
            }
        }
        return $res;
    }

    function getExamStudMap($exam_id, $user_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "SELECT * FROM `exam_student_mapper` WHERE `exam_id` = " . $exam_id;
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_set'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['exam_set'] = [];
            }
        }
        return $res;
    }
	
    function getAllTestByStudentId($stu_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT exm.`attempted_date`, exm.`result_exam`, exm.`exam_id`, e.`exam_name` from  `exam_student_mapper` exm INNER JOIN `exam_master` e ON  e.`exam_id` = exm.`exam_id` INNER JOIN `exam_type_master` ex ON  ex.`id` = e.`exam_type_id` WHERE exm.`student_id` = %s', $stu_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $arr = [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->exam_id = $row['exam_id'];
                    $obj->name = $row['exam_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['my_test'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['my_test'] = [];
            }
        }
        return $res;
    }
    function getAllOrdersByStudentId($stu_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('select ep.`created_at` , ep.`amount`, ex.`id`, ex.`name` FROM `users_exam_payments` ep INNER JOIN `users_exam_payment_request` er ON er.`upcpayrq_id` = ep.`upcpayrq_id` AND er.`status` = "paid" INNER JOIN `exam_type_master` ex ON ex.`id` = ep.`exam_type_id` WHERE ep.`status` = "captured" AND er.`user_id` = %s', $stu_id);
        
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $arr = [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->date = $row['created_at'];
                    $obj->amount = $row['amount'];
                    $obj->name = $row['name'];
                    $obj->exam_type_id = $row['id'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['my_orders'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['my_orders'] = [];
            }
        }
        return $res;
    }



    function getExamByExamTypeAndValidity($exam_id) {
        $today = date('Y-m-d H:i:s');
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
     /*   $sql = sprintf("SELECT ex.`exam_id`,ex.`duration`,ex.`exam_name`, ex.`no_of_que` FROM `exam_master` ex WHERE ex.`exam_id` =  %s AND ex.`start_date` <= '%s' AND '%s' <= ex.`end_date`", $exam_id, $today, $today); */
		  $sql = sprintf("SELECT ex.`exam_id`,ext.`duration`,ex.`exam_name`, ex.`no_of_que` FROM `exam_master` ex INNER JOIN `exam_type_master` ext ON ex.`exam_type_id` = ext.`id`  WHERE ex.`exam_id` =  %s", $exam_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->exam_id = $row['exam_id'];
                    $obj->exam_name = $row['exam_name'];
                    $obj->no_of_que = $row['no_of_que'];
                    $obj->duration = $row['duration'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_list'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['exam_list'] = [];
            }
        }
        return $res;
    }

    function getTotalQuestionAttemptedByStudent($exam_id, $stu_id) {
        $today = date('Y-m-d');
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "SELECT q.`question_id`, q.`correct_answer`, q.`select_answer`,qm.`question`, qm.`option1`, qm.`option2`, qm.`option3`, qm.`option4`, qm.`option5`, qm.`answer`, qm.`answer_description` FROM `exam_details_result_mapper` q INNER JOIN `exam_student_mapper` em ON em.`id` = q.`exam_stud_map_id` AND em.`student_id` = " . $stu_id . " INNER JOIN `question_master` qm ON qm.`id` = q.`question_id` WHERE q.`exam_id` = " . $exam_id;
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->question_id = $row['question_id'];
                    $obj->correct_answer = $row['correct_answer'];
                    $obj->select_answer = $row['select_answer'];
                    $obj->answer = $row['answer'];
                    $obj->answer_description = $row['answer_description'];
                    $obj->question = (object) ['options' => []];
                    $obj->question->que_name = $row['question'];
                    $obj->question->options[] = $row['option1'];
                    $obj->question->options[] = $row['option2'];
                    $obj->question->options[] = $row['option3'];
                    $obj->question->options[] = $row['option4'];
                    $obj->question->options[] = $row['option5'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_set'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_set'] = $arr;
            }
        }
        return $res;
    }

    function getTotalExamQuestionsByExamId($exam_id, $stud_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "Select qm.`id`, qm.`question`, qm.`option1`, qm.`option2`, qm.`option3`, qm.`option4`, qm.`option5`, emt.`duration` from `exam_question_mapper` eqm INNER JOIN `question_master` qm ON qm.`id` = eqm.`que_id` INNER JOIN `exam_master` em ON em.`exam_id` = eqm.`exam_id` INNER JOIN `exam_type_master` emt ON emt.`id` = em.`exam_type_id` WHERE em.`exam_type_id` = " . $exam_type_id . ' AND em.`is_publish` = 1';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->keywords = $row['keywords'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['keywords'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['keywords'] = $arr;
            }
        }
        return $res;
    }

    function updateClientId($data, $user_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $updated_by = 1;
        $stmt = $this->con->prepare("update `student_master` set `login_by` = '$data->login_by',`client_id` = '$data->client_id' where `student_id` = '$user_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Client Id updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateOrderIdForPayRequest($orderId, $request_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_by = 1;
        $stmt = $this->con->prepare("update `users_exam_payment_request` set `order_id` = '$orderId' where `upcpayrq_id` = '$request_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Order Id updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateResultForStudent($id, $total_marks) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $updated_by = 1;
        $stmt = $this->con->prepare("update `exam_student_mapper` set `result_exam` = '$total_marks' where `id` = '$id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Result updated Successfully.';
        } else {
            $res->status = false;
            $res->message = 'Could not update result.';
        }
        $this->close = null;
        return $res;
    }

    function updateTotalCoins($total_coins) {
        global $auth_obj;
        $stu_id = $auth_obj->user->user_id;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $updated_by = 1;
        $stmt = $this->con->prepare("update `student_master` set `total_coins` = '$total_coins' where `student_id` = '$stu_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Coins updated Successfully.';
        } else {
            $res->status = false;
            $res->message = 'Could not update coins earned.';
        }
        $this->close = null;
        return $res;
    }

    
    function updateProfile($data) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $user_id = $auth_obj->user->user_id;
        $stmt = $this->con->prepare("update `student_master` set `full_name` = '$data->name', `nick_name` = '$data->nickname', `email_id` = '$data->email', `phone_number` = '$data->phone' where `student_id` = '$user_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'User Profile Updated Successfully';
            $res->data['user_data'] = [];
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateOrderDetails($orderId, $order) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $stmt = $this->con->prepare("update `users_exam_payment_request` set `status` = '$order->status', `attempts` = '$order->attempts' where `order_id` = '$orderId' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'User Profile Updated Successfully';
            $res->data['user_data'] = [];
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function saveExamQuestionAnswers($exam_stud_map_id, $data, $answer) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_details_result_mapper', 'id');
        $created_date = date('Y-m-d H:i:s');
        $attempted_date = date('Y-m-d H:i:s');
        $created_by = 1;
        $status = 'A';
        $total_time = isset($data->total_time_per_que) ? "$data->total_time_per_que" : "00:00:00";
        $selected_answer = isset($data->selected_answer) ? $data->selected_answer : '';
        $is_correct = $data->is_correct == 1 ? 1 : 0;
        $stmt = $this->con->prepare("INSERT INTO `exam_details_result_mapper`(`id`, `exam_stud_map_id`, `exam_id`, `question_id`, `correct_answer`, `select_answer`, `is_correct_wrong`,`attempted_date`,`total_time_per_que`, `created_date`, `created_by`, `Status`) VALUES ('$id', '$exam_stud_map_id', '$data->exam_id', '$data->question_id', '$answer', '$selected_answer', $is_correct, '$attempted_date', '$total_time', '$created_date', '$created_by', '$status');");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Details Submited Successfully';
            $res->data['id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function calculateTotalCorrectAnswer($exam_id, $student_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'SELECT count(es.`id`) as `correct_answer`, es.`exam_stud_map_id` FROM `exam_details_result_mapper` es INNER JOIN `exam_student_mapper` esm ON esm.`id` = es.`exam_stud_map_id` WHERE es.`exam_id` = ' . $exam_id . ' AND esm.`student_id` = ' . $student_id . ' AND es.`is_correct_wrong` = 1 GROUP by es.`exam_stud_map_id` order by es.`attempted_date` desc limit 1';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->correct_answer = $row['correct_answer'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['answer'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['answer'] = [];
            }
        }
        return $res;
    }

    function calculateTotalAttemptedQuestions($exam_id) {
        global $auth_obj;
        $stu_id = $auth_obj->user->user_id;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT count(ex.`id`) as `attempted_que` FROM `exam_details_result_mapper` ex INNER JOIN `exam_student_mapper` exm ON exm.`id` = ex.`exam_stud_map_id` WHERE ex.`exam_id` = %s AND exm.`student_id` = %s GROUP by ex.`exam_stud_map_id` order by ex.`attempted_date` desc limit 1', $exam_id, $stu_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->attempted_que = $row['attempted_que'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['answer'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['answer'] = [];
            }
        }
        return $res;
    }

    function getMyScoreForExamId($exam_id) {
        global $auth_obj;
        $stu_id = $auth_obj->user->user_id;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT s.`result_exam` FROM `exam_student_mapper` s WHERE s.`exam_id` = %s AND s.`student_id` = %s ', $exam_id, $stu_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->result_exam = $row['result_exam'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['result_exam'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['result_exam'] = [];
            }
        }
        return $res;
    }

    function getAverageScore($exam_id) {
        global $auth_obj;
        $stu_id = $auth_obj->user->user_id;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT AVG(`result_exam`) AS AverageResult, `exam_id` FROM exam_student_mapper WHERE `exam_id` = %s GROUP BY `exam_id`', $exam_id);
        
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->AverageResult = $row['AverageResult'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['AverageResult'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['AverageResult'] = [];
            }
        }
        return $res;
    }

    function getHighestExamScore($exam_id) {
        global $auth_obj;
        $stu_id = $auth_obj->user->user_id;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT MAX(`result_exam`) AS highest_score FROM exam_student_mapper WHERE `exam_id` = %s', $exam_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->highest_score = $row['highest_score'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['highest_score'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['highest_score'] = [];
            }
        }
        return $res;
    }

    function getTopStudents($exam_id, $limit) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $today = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        $sql = sprintf('SELECT es.`attempted_date`, es.`result_exam`, es.`student_id`, s.`full_name` FROM `exam_student_mapper` es INNER JOIN `student_master` s ON es.`student_id` = s.`student_id` WHERE `exam_id` = %s AND es.`attempted_date` > "%s" AND es.`attempted_date` < "%s" order by es.`result_exam` desc', $exam_id, $today, $end);
        if (is_null($limit)) {
            $sql .= ' limit 5 offset 0';
        }
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            $arr = [];
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->student_id = $row['student_id'];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->full_name = $row['full_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['student_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['student_list'] = [];
            }
        }
        return $res;
    }

    function getTotalStudentsByExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT e.`student_id`, e.`attempted_date`, e.`result_exam`, s.`full_name` FROM `exam_student_mapper` e INNER JOIN `student_master` s ON e.`student_id` = s.`student_id` WHERE e.`exam_id` = %s', $exam_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            $arr = [];
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->student_id = $row['student_id'];
                    $obj->attempted_date = $row['attempted_date'];
                    $obj->result_exam = $row['result_exam'];
                    $obj->full_name = $row['full_name'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['student_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['student_list'] = [];
            }
        }
        return $res;
    }

    function getPymtRqstDtlsByOrderId($orderId) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT p.*, s.`full_name` FROM `users_exam_payment_request` p INNER JOIN `student_master` s ON s.`student_id` = p.`user_id` WHERE p.`order_id` = "%s"', $orderId);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            $obj = (object) [];
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj->upcpayrq_id = $row['upcpayrq_id'];
                    $obj->order_id = $row['order_id'];
                    $obj->amount = $row['amount'];
                    $obj->currency = $row['currency'];
                    $obj->status = $row['status'];
                    $obj->full_name = $row['full_name'];
                    $obj->exam_id = $row['exam_id'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['pay_request'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['pay_request'] = [];
            }
        }
        return $res;
    }

    function getAverageRatingsByExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT AVG(rating) as `avg_rating` FROM `exam_ratings` WHERE exam_id = %s', $exam_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->avg_rating = $row['avg_rating'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['rating'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['rating'] = [];
            }
        }
        return $res;
    }


    private function _getMaxTableId($conn, $table_name, $column_name) {
        $sql = sprintf('Select Max(%s) as `max` From %s', $column_name, $table_name);
        $max_id = -1;
        if ($res = $conn->query($sql)) {
            $max_id = $res->fetchColumn();
            if (is_null($max_id)) {
                $max_id = 1;
            } else {
                ++$max_id;
            }
        }
        return $max_id;
    }

}
