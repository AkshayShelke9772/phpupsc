<?php

require_once 'framework/model/studentModel.php';
require_once 'framework/model/adminModel.php';
require_once 'helper/communicator.php';
require_once 'helper/helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/upscMcqsAPI/libs/razorpay-php/Razorpay.php';

use Razorpay\Api\Api;

class studentController {

    function __construct() {
        
    }

    function login($data) {
        global $auth_obj;
        $auth_obj = (object) [];
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->getUserData($data);
        $helper = new helper();
        if ($response->status) {
            // check if the is_verfiy_otp is 1 or not
            $user_data = $response->data['user_data'];
            // check if password is null or not
            // if it is null means user regester with social media
            // if no user register through  our website
            if (strlen($user_data->password) == 0) {
                if (isset($user_data->client_id)) {
                    // update the client id and login by field  
                    $updateRs = $db->updateClientId($data, $user_data->user_id);
                    $res->statusCode = 200;
                    $res->message = 'Logged in Successfully';
                }
            } else
            if (($user_data->password === sha1($data->password))) {
                $res->statusCode = 200;
                $res->message = 'Logged in Successfully';
                unset($user_data->password);
                //  call the helper function for generating token;
                $token = $helper->getAuthorization($user_data);
                $res->data['user_data'] = $user_data;
                $res->data['user_data']->token = $token;
                $auth_obj->user = $res->data['user_data'];
            } else if ($user_data->is_verify_otp == '0') {
                // send OTP then verify 
                $otpCode = $helper->generateRandomOtp();
//                $sendOtpRs = new communicator($otpCode);
//                if ($sendOtpRs->status) {
                // save OTP in database
                $stud_id = $user_data->user_id;
                $savRs = $db->saveOtp($stud_id, $otpCode);
                if (!$savRs->status) {
                    $res->statusCode = $savRs->status ? 200 : 403;
                    $res->message = $savRs->message;
                }
//                }
            } else {
                $res->statusCode = 403;
                $res->message = 'Wrong Password';
            }
        } else {
            // check if the client id is set or not
            // if yes means user is logged in by social media
            // else user is logged in by our website 
            $regersterRs = $db->register($data, 0);
            $res->statusCode = $regersterRs->status ? 200 : 403;
            $res->message = $regersterRs->message;
            if ($regersterRs->status) {
                // send OTP then verify 
                $stud_id = $regersterRs->data['stud_id'];
                $otpCode = $helper->generateRandomOtp();
//                $sendOtpRs = new communicator($otpCode);
//                if ($sendOtpRs->status) 
                // save OTP in database
                $savRs = $db->saveOtp($stud_id, $otpCode);
                if (!$savRs->status) {
                    $res->statusCode = $savRs->status ? 200 : 403;
                    $res->message = $savRs->message;
                } else {
                    $data->user_id = $stud_id;
                    $token = $helper->getAuthorization($data);
                    $res->data['user_data'] = $data;
                    $res->data['user_data']->token = $token;
                    $auth_obj->user = $res->data['user_data'];
                }
//                }
            }
            // user is not registered 
//            $res->statusCode = 403;
//            $res->message = 'Your not register with us. Please register first';
        }
        return $res;
    }

    function register($methodname, $data, $is_Admin) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        if (isset($data->name) && isset($data->password)) {
            $db = new studentModel();
            $response = $db->register($data, $is_Admin);
            if ($response->status) {
                $res->statusCode = 200;
                $res->message = $response->message;
                // send OTP for the mobile number
                $hlpr = new helper();
                $otpCode = $hlpr->generateRandomOtp();
//                $sendOtpRs = new communicator($otpCode);
//                if ($sendOtpRs->status) {
                // save OTP in database
                $stud_id = $response->data['stud_id'];
                $savRs = $db->saveOtp($stud_id, $otpCode);
                if (!$savRs->status) {
                    $res->statusCode = $savRs->status ? 200 : 403;
                    $res->message = $savRs->message;
                }
//                }
            } else {
                $res->statusCode = 403;
                $res->message = $response->message;
            }
        } else {
            $res->statusCode = 403;
            $res->message = 'All Fields are mandatory';
        }
        return $res;
    }

    function submitExam($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $conn = new adminModel();
        $response = $db->submitExam($data);
        if ($response->status) {
            $id = $response->data['id'];
            // check if the exam is having penaulty or not
            $exam_id = $data->exam_id;
            $getExamData = $conn->getExamDetails($exam_id);
            $is_penaulty = 0;
            if ($getExamData->status) {
                $exam_data = $getExamData->data['detail'];
                $is_penaulty = $exam_data->is_penaulty;
            }
            if (count($data->questions) > 0) {
                // save exam details
                $total_marks = 0;
                foreach ($data->questions as $dk => $dv) {
                    // get correct answer and marks per question for calculating result
                    $getDataRs = $conn->getQuestionDetailsById($dv->question_id);
                    if ($getDataRs->status) {
                        $question_date = $getDataRs->data['question_set'];
                        $answer = $question_date->answer_seq_id;
                        $marks_per_que = $question_date->marks_per_que;
                        $dv->is_correct = 0;
                        if (isset($dv->selected_answer)) {
                            if ($answer == $dv->selected_answer) {
                                $dv->is_correct = 1;
                                $total_marks += $marks_per_que;
                            } else {
                                if ($is_penaulty) {
                                    $penaultyVal = $marks_per_que * 1 / 3;
                                    $total_marks -= $penaultyVal;
                                }
                                $dv->is_correct = 0;
                            }
                        }
                        $saveQue = $db->saveExamQuestionAnswers($id, $dv, $answer);
                        if ($saveQue->status) {
                            // update the result in exam_student_mapper table
                            $prev_coins = $auth_obj->user->total_coins;
                            $total_coins_earned = $prev_coins + $total_marks;
                            $updateTblRs = $db->updateResultForStudent($id, $total_marks);
                            if ($updateTblRs->status) {
                                // update total coins in student table
                                // if exam is weekly 20 mcqs then only update the total coins earned
                                if ($getExamData->data['detail']->exam_type_id == 1) {
                                    $updateCoins = $db->updateTotalCoins($total_coins_earned);
                                    if ($updateCoins->status) {
                                        $res->statusCode = 200;
                                        $res->message = $updateCoins->message;
                                    } else {
                                        $res->statusCode = 403;
                                        $res->message = $updateCoins->message;
                                    }
                                }
                                $res->statusCode = 200;
                                $res->message = $updateTblRs->message;
                            } else {
                                $res->statusCode = 403;
                                $res->message = $updateTblRs->message;
                            }
                            $res->statusCode = 200;
                            $res->message = $saveQue->message;
                        } else {
                            $res->statusCode = 403;
                            $res->message = $saveQue->message;
                        }
                    } else {
                        $res->statusCode = 403;
                        $res->message = 'Could not find data against this question';
                    }
                }
            } else {
                $res->statusCode = 403;
                $res->message = 'Invalid Question Set';
            }
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function giveFeedbackToExam($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        if (count($data->question_set) > 0) {
            foreach ($data->question_set as $dk => $dv) {
                $dv->exam_id = $data->exam_id;
                $dv->comment = $data->comment;
                $response = $db->giveFeedbackToExam($dv);
                if ($response->status) {
                    $res->statusCode = 200;
                    $res->message = $response->message;
                } else {
                    $res->statusCode = 403;
                    $res->message = $response->message;
                }
            }
        } else {
            $res->statusCode = 403;
            $res->message = 'Please give feedback first';
        }

        return $res;
    }

    function verifyOtp($methodname, $data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->verifyOtp($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }


    function forgotPassword($methodname, $data) {
        
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->forgotPassword($data);
        if ($response->status) {
            $stud_id = $response->stud_id; // get for save OTP
            $stud_username = trim($response->stud_username); //get for send OTP on email / mobile
            
            $helper = new helper();
            $otpCode = $helper->generateRandomOtp();
            $savOtpRs = $db->saveOtp($stud_id, $otpCode);

            if($savOtpRs->status){
                //OTP saved success in database
                $otpMessage ="Your OTP for UPSC Summaries ".$otpCode." Do not share with anyone! Thank You";

                if(is_numeric($stud_username)) //Check username is email or mobile
                { //username is mobile number

                    $SmsGatewayHubSender = new SmsGatewayHubSender();
                    if($SmsGatewayHubSender->sendSms($otpMessage,(Int)$stud_username)){
                        $res->statusCode=200;
                        $res->message = "OTP send succesfull to ".$stud_username." number , please verify OTP";
                    }else{
                        // OTP not send succes please try again
                        $res->statusCode=403;
                        $res->message = "OTP could not be send please try again  / You can try with registered Email-Id";
                    }
                }else{
                    //username is email
                    $sendmail = new SendMail();
                    if($sendmail->sendOTP($stud_username,$otpMessage)){
                        $res->statusCode=200;
                        $res->message = "OTP send succesfull to ".$stud_username." , please verify OTP";
                    }else{
                        // OTP not send succes please try again
                        $res->statusCode=403;
                        $res->message = "OTP could not be send please try again / You can try with registered Mobile Number";
                    }
                }   

            }else{
                // OTP not save so please try again
                $res->statusCode = 403;
                $res->message = $savOtpRs->message;
            }
        } else {
            //User not found to forgot password please try again 
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    
    function updateProfile($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $old_pwd = $data->old_password;
        $pwd = $auth_obj->user->password;
        if (sha1($old_pwd) === $pwd) {
            $response = $db->updateProfile($data);
            if ($response->status) {
                $userData = $_SESSION;
                $helper = new helper();
                $token = $helper->getAuthorization($userData['user']);
                // get updated user data
                $user_id = $auth_obj->user->user_id;
                $userRs = $db->getUserDataById($user_id);
                if ($userRs->status) {
                    $ud = $userRs->data['user'];
                    $response->data['user_data'] = $ud;
                }
                $response->data['user_data']->token = $token;
                $auth_obj->user = $response->data['user_data'];
                $res->statusCode = 200;
                $res->message = $response->message;
                $res->data['user_data'] = $response->data['user_data'];
                unset($res->data['user_data']->password);
            } else {
                $res->statusCode = 403;
                $res->message = $response->message;
            }
        } else {
            $res->statusCode = 403;
            $res->message = 'Could not proceed as old password is incorrect.';
        }
        return $res;
    }

    function addUsersQuestion($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->addUsersQuestion($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getAllQuestionList() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->getQuestionSetOfDailyMcqs();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['question_set'] = $response->data['question_set'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getCurrentExamResult($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $adminCore = new adminModel();
        $stud_id = $auth_obj->user->user_id;
        // first get total no of question against this exam
        $result_obj = (object) ['pie_chart_val' => '', 'bar_graph_val' => ''];
        $res_obj = (object) ['total_no_of_que' => 0, 'correct_answer' => 0, 'attempted' => 0, 'non_attempted' => 0];
        $getTotalRs = $adminCore->getExamDetails($data->exam_id);
        $exam_type_id = 1;
        if ($getTotalRs->status) {
            $details = $getTotalRs->data['detail'];
            $exam_type_id = $details->exam_type_id;
            $res_obj->total_no_of_que = $details->no_of_que;
        }
        // calculate correct answer                 
        $correctAnswRS = $db->calculateTotalCorrectAnswer($data->exam_id, $stud_id);
        if ($correctAnswRS->status) {
            $res_obj->correct_answer = $correctAnswRS->data['answer']->correct_answer;
        }
        // calculate total attempted questions
        $correctAnswRS = $db->calculateTotalAttemptedQuestions($data->exam_id, $stud_id);
        if ($correctAnswRS->status) {
            $res_obj->attempted = $correctAnswRS->data['answer']->attempted_que;
        }
        // calculate non-attempted que
        $non_attempted_que = $res_obj->total_no_of_que - $res_obj->attempted;
        $res_obj->non_attempted = $non_attempted_que;

        $result_obj->pie_chart_val = $res_obj;

        // 
        $bar_graph_obj = (object) ['Your_score' => [], 'average_score' => [], 'highest_score' => []];


        $result_obj->bar_graph_val = $bar_graph_obj;
        // get exam wise pasr 10 records of students
        // first get 10 exams in descending order by exam_type_id
        $getAllExamsRs = $adminCore->getExamListByExamType($exam_type_id);
        if ($getAllExamsRs->status) {
            $exams = $getAllExamsRs->data['exam_list'];
            if (count($exams) > 0) {
                foreach ($exams as $ex => $ev) {
                    // calculate total my score against each exam
                    $getTotalMyScoresRs = $db->getMyScoreForExamId($ev->exam_id);
                    if ($getTotalMyScoresRs->status) {
                        $resultData = $getTotalMyScoresRs->data['result_exam'];
                        $bar_graph_obj->Your_score[] = $resultData->result_exam;
                    }
                    //calculate total average score against each exam
                    $getAvgScoresRs = $db->getAverageScore($ev->exam_id);
                    if ($getAvgScoresRs->status) {
                        $avgData = $getAvgScoresRs->data['AverageResult'];
                        $bar_graph_obj->average_score[] = $avgData->AverageResult;
                    }
                    //calculate highest score against each exam
                    $getHighestScoresRs = $db->getHighestExamScore($ev->exam_id);
                    if ($getHighestScoresRs->status) {
                        $highestData = $getHighestScoresRs->data['highest_score'];
                        $bar_graph_obj->highest_score[] = $highestData->highest_score;
                    }
                }
            }
        }
        $res->statusCode = 200;
        $res->message = 'Got Data';
        $res->data = $result_obj;
        return $res;
    }

    function getRankingList($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => []];
        $db = new studentModel();
        $adminCore = new adminModel();
        $stud_id = $auth_obj->user->user_id;
        $getDetailRs = $adminCore->getExamDetails($data->exam_id);
        $exam_type_id = 1;
        if ($getDetailRs->status) {
            $details = $getDetailRs->data['detail'];
            $exam_type_id = $details->exam_type_id;
            $exam_end_date = $details->start_date;
            // get total students with scores who attempted this exam
            $total_stu = $db->getTotalStudentsByExamId($data->exam_id);
            if ($total_stu->status) {
                $allStu = $total_stu->data['student_list'];
                $all_india_ranking = [];
                $static_ranking = [];
                foreach ($allStu as $ak => $av) {
                    if (strtotime($exam_end_date) >= strtotime($av->attempted_date)) {
                        $all_india_ranking[] = $av;
                    } else {
                        $static_ranking[] = $av;
                    }
                }
            }
        }
        $res->statusCode = 200;
        $res->message = 'Got Data';
        $res->data['ranking'] = (object) ['all_india_rank' => $all_india_ranking, 'static_ranking' => $static_ranking];
        return $res;
    }

    function getKeywordWiseStudentExamREsult($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => []];
        $db = new studentModel();
        $adminCore = new adminModel();
        $stud_id = $auth_obj->user->user_id;
        $getDetailRs = $db->getTotalExamQuestionsByExamId($data->exam_id, $stud_id);
        var_dump($getDetailRs);
        die;
        $exam_type_id = 1;
        if ($getDetailRs->status) {
            $details = $getDetailRs->data['detail'];
            $exam_type_id = $details->exam_type_id;
            $exam_end_date = $details->start_date;
            // get total students with scores who attempted this exam
            $total_stu = $db->getTotalStudentsByExamId($data->exam_id);
            if ($total_stu->status) {
                $allStu = $total_stu->data['student_list'];
                $all_india_ranking = [];
                $static_ranking = [];
                foreach ($allStu as $ak => $av) {
                    if (strtotime($exam_end_date) >= strtotime($av->attempted_date)) {
                        $all_india_ranking[] = $av;
                    } else {
                        $static_ranking[] = $av;
                    }
                }
            }
        }
        $res->statusCode = 200;
        $res->message = 'Got Data';
        $res->data['ranking'] = (object) ['all_india_rank' => $all_india_ranking, 'static_ranking' => $static_ranking];
        return $res;
    }

    function getExamResultByStudentId($stud_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->getExamResultByStudentId($stud_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['exam_result_set'] = $response->data['exam_result_set'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getExamResultInDetails($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $adminCore = new adminModel();
        $stud_id = $auth_obj->user->user_id;
        // first get total no of question against this exam
        $result_obj = (object) ['numerical_values' => '', 'exam_marks' => 0, 'graph_values' => '', 'leaderboard' => '', 'score_history' => ''];
        $res_obj = (object) ['total_no_of_que' => 0, 'correct_answer' => 0, 'attempted' => 0, 'non_attempted' => 0];
        $getTotalRs = $adminCore->getExamDetails($data->exam_id);
        if ($getTotalRs->status) {
            $details = $getTotalRs->data['detail'];
            $res_obj->total_no_of_que = $details->no_of_que;
        }
        // calculate correct answer                 
        $correctAnswRS = $db->calculateTotalCorrectAnswer($data->exam_id, $stud_id);
        if ($correctAnswRS->status) {
            $res_obj->correct_answer = $correctAnswRS->data['answer']->correct_answer;
        }
        // calculate total attempted questions
        $correctAnswRS = $db->calculateTotalAttemptedQuestions($data->exam_id, $stud_id);
        if ($correctAnswRS->status) {
            $res_obj->attempted = $correctAnswRS->data['answer']->attempted_que;
        }
        // calculate non-attempted que
        $non_attempted_que = $res_obj->total_no_of_que - $res_obj->attempted;
        $res_obj->non_attempted = $non_attempted_que;

        $result_obj->numerical_values = $res_obj;

        // calculateleaderboard values for top5 students
        $getTopStuRs = $db->getTopStudents($data->exam_id, null);
        if ($getTopStuRs->status) {
            $student_list = $getTopStuRs->data['student_list'];
            $result_obj->leaderboard = $student_list;
            // calculate graph values for top5 students
            if (count($student_list) > 0) {
                foreach ($student_list as $sk => $sv) {
                    $student_id = $sv->student_id;
                    $correctRs = $db->calculateTotalCorrectAnswer($data->exam_id, $student_id);
                    if ($correctRs->status) {
                        $sv->correct_answer = $correctRs->data['answer']->correct_answer;
                    }
                    // calculate total attempted questions
                    $totalAttmptdRS = $db->calculateTotalAttemptedQuestions($data->exam_id, $student_id);
                    if ($totalAttmptdRS->status) {
                        $sv->attempted = $totalAttmptdRS->data['answer']->attempted_que;
                    }
                }
            }
        }

        // calculateleaderboard values for top5 students
        $getScoreHistoryRs = $db->getTopStudents($data->exam_id, 'all');
        if ($getScoreHistoryRs->status) {
            $list = $getScoreHistoryRs->data['student_list'];
            $result_obj->score_history = $list;
        }
        $res->statusCode = 200;
        $res->message = 'Got Data';
        $res->data = $result_obj;
        return $res;
    }

    function getExamResult() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->getExamResult();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['exam_result_set'] = $response->data['exam_result_set'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getUserDataById($user_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $response = $db->getUserDataById($user_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['user'] = $response->data['user'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getInfo() {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $user_id = $auth_obj->user->user_id;
        $response = $db->getUserDataById($user_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['user'] = $response->data['user'];
            $helper = new helper();
            $token = $helper->getAuthorization($res->data['user']);
            $res->data['user']->token = $token;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getWeeklyTwentyMCQs($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $exam_id = $data->exam_id;
        $user_id = $auth_obj->user->user_id;
        // first get exam by checking start date and end date
        $getRs = $db->getExamByExamTypeAndValidity($exam_id);
        if ($getRs->status) {
            $exam_list = $getRs->data['exam_list'];
            if ($exam_list) {
                // get questions against thid exam_id
                $getQueRs = $db->getExamQuestionsByExamId($exam_id);
                if ($getQueRs->status) {
                    $question_set = $getQueRs->data['question_set'];
                    $res->statusCode = 200;
                    $res->message = 'Got Data Successfully.';
                    $res->data = $question_set;
                } else {
                    $res->statusCode = 403;
                    $res->message = 'There is no any questions against this exam.';
                }
            } else {
                
            }
        } else {
            $res->statusCode = 403;
            $res->message = 'Sorry! There is no exam in this duration.';
        }

        return $res;
    }

    function getExamList($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $core = new adminModel();
        $response = $core->getExamList($data);
        if ($response->status) {
            $exam_list = $response->data['exam_list'];
//            if (count($exam_list) > 0) {
            // get questions against exam id
//                foreach ($exam_list as $ek => $ev) {
//                    $exam_id = $ev->exam_id;
//                    $getQuestionsRs = $core->getQuestionsAgainstExamId($exam_id);
//                    if ($getQuestionsRs->status) {
//                        $ev->questions = $getQuestionsRs->data['question_set'];
//                    } else {
//                        $ev->questions = [];
//                    }
//                }
//            }
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['exam_list'] = $response->data['exam_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getQuestionsSolutionListByExamId($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new studentModel();
        $exam_id = $data->exam_id;
        $stu_id = $auth_obj->user->user_id;
        $response = $db->getTotalQuestionAttemptedByStudent($exam_id, $stu_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['question_set'] = $response->data['question_set'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function initiatePayment($pd) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $core = new studentModel();
        $user_id = $auth_obj->user->user_id;
        $addRs = $core->addPaymentRequest($pd, $user_id);
        if ($addRs->status) {
            $request_id = $addRs->data['upcpayrq_id'];
            $api = new Api(RZRPAY_TEST_KEY, RZRPAY_SECURE_KEY);
            // Orders
            $order = $api->order->create(array('receipt' => $request_id, 'amount' => $pd->amount, 'currency' => 'INR', 'payment_capture' => TRUE)); // Creates order
            $orderId = $order['id'];
            if ($orderId) {
                // update payment request table
                $updateRs = $core->updateOrderIdForPayRequest($orderId, $request_id);
                $addRs->status = $updateRs->status;
            }
            $res->statusCode = $addRs->status ? 200 : 403;
            if ($addRs->status) {
                $res->message = $addRs->message;
                $res->data['orderId'] = $orderId;
            } else {
                $res->message = 'Could not initiate the payment';
            }
        }
        return $res;
    }

    function settlePayment($pd) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'msg' => 'Something Went Wrong', 'data' => null];
        $core = new studentModel();
        $user_id = $auth_obj->user->user_id;
        $getRs = $core->getPymtRqstDtlsByOrderId($pd->orderId);
        if ($getRs->status) {
            $payment_id = $pd->id;
            $payRq = $getRs->data['pay_request'];
            $payRqId = $payRq->upcpayrq_id;
            $api = new Api(RZRPAY_TEST_KEY, RZRPAY_SECURE_KEY);
            $payment = $api->payment->fetch($payment_id); // Returns a particular payment
            if ($payment) {
                $orderRs = $api->order->fetch($payment->order_id); // Returns a particular order
                if ($orderRs) {
                    if ($orderRs->status == 'paid') {
                        //Update request status to paid for order
                        $updateRs = $core->updateOrderDetails($orderRs->id, $orderRs);
                        if ($updateRs->status) {
                            $res->status = true;
                            $desc = $payment->description;
                            $saveRs = $core->savePaymentDetails($payment_id, $payRqId, $payment, $desc, $payRq->exam_id);
                            if (!$saveRs->status) {
                                $res->status = false;
                                $res->msg = 'Could not save payment record';
                            } else {
                                $res->status = true;
                                $res->msg = 'Payment added successfully ';
                            }
                        } else {
                            $res->status = false;
                            //Could not update payment status
                            $res->msg = 'Could not update payment status';
                        }
//                        $res->msg = 'Could not process payment request';
                    } else {
                        //If we have different order id in payment and order
                        $res->status = false;
                        $res->msg = 'Payment not settled, please try again';
                    }
                } else {
                    $res->status = false;
                    $res->msg = 'Could not complete payment, please try again';
                }
            } else {
                $res->status = false;
                $res->msg = 'Could not complete payment, please try again';
            }
            $res->statusCode = $res->status ? 200 : 403;
            return $res;
        }
    }

    private
            function _handleSettlePayment($conn, PaymentCore $pCore, UserCore $uCore, $order, $payment, $payRq) {
        global $auth_obj;
        $res = new \CoreRes();
        try {
            $allOk = true;
            $res->data = ['user_id' => null, 'user_mobile' => [], 'user_email' => [], 'withSpouse' => false, 'spouse_id' => null, 'spouse_mobile' => null, 'spouse_email' => null, 'coupon_code' => null];
            if ($payRq->notes) {
                //Update request status to paid for order
                $updateRs = $pCore->updateOrderDetails($order->id, $order);
                if ($updateRs->status && $updateRs->affected_rows > 0) {
                    $notes = json_decode($payRq->notes);
                    $res->data['user_id'] = $notes->user_id;
                    $res->status = true;
                    $res->data['user_id'] = $notes->user_id;
                    $res->data['withSpouse'] = count($notes->user_id) > 1;
                    $res->data['spouse_id'] = $notes->spouse_id;
                    $res->data['coupon_code'] = $notes->coupon_code;
                } else {
                    //Could not update payment status
                    $res->msg = 'Could not update payment status';
                }
            } else {
                $res->msg = 'Could not process payment request';
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $res;
    }

}
