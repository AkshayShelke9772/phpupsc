<?php

require_once 'includes/header.php';
require_once 'helper/helper.php';
require_once 'apicontroller.php';
require_once 'framework/controller/studentController.php';
require_once 'framework/controller/adminController.php';

class main {

    function __construct() {
        $this->api();
    }

    function api() {
        global $auth_obj;
// get all headers and check the authorization 
        $headers = getallheaders();
        if (isset($headers['commtext']) && $headers['commtext'] == commtext) {
            $requested_url = $_SERVER['PATH_INFO'];
            $requested_method = $_SERVER['REQUEST_METHOD'];
// Converts it into a PHP object
            $json = file_get_contents('php://input');
            $data = json_decode($json);
            $arr = explode('/', $requested_url);
            $endPoint = $arr[2];
            $method_name = $arr[3];

            $deny_access_arr = ['social-login','register', 'login', 'verify-otp','forgot-password','reset-password'];//no need authorization

            if (in_array($method_name, $deny_access_arr)) {
                $apiRes = $this->working($endPoint, $method_name, $requested_method, $data, false);
                echo(json_encode($apiRes));
            } else if (isset($headers['Authorization'])) {
                // check the user is registered with us or not
                $hlpr = new helper();
                $res = $hlpr->getUserData($headers['Authorization']);
                // need to be remove
                if ($res->statusCode == 200) {
                    $_SESSION['user'] = $res->data['user'];
                    // check that logged in user is admin or student
                    // Call the function as per user role
                    $apiRes = $this->working($endPoint, $method_name, $requested_method, $data, true);
                    echo json_encode($apiRes);
                } else {
                    echo json_encode($res);
                }
            } else {
                
            }
        } else {
            $res = (object) ['statusCode' => 403, 'message' => 'Headers are missing.', 'data' => null];
            echo json_encode($res);
        }
    }

    function working($endPt, $methodname, $method, $data, $checkAuth) {
        global $auth_obj;
        if (!$checkAuth) {
            $apiRs = $this->common($methodname, $data, $endPt);
        } else {
            $check_auth = $this->_checkAuthorization($endPt, $methodname);
            $userRole = $auth_obj->user->user_role;
            if ($check_auth->statusCode == 200) {
                if ($userRole == 'admin') {
                    $apiRs = $this->admin($methodname, $data);
                } else {
                    $apiRs = $this->student($methodname, $data);
                }
            } else {
                return $check_auth;
            }
        }
        return $apiRs;
    }

    function student($methodname, $data) {
        $obj = new studentController ();
        $std_cont = new studentController();

        switch ($methodname) {
            case 'get-exam-questions-by-exam-id' : {
                    $fun = $obj->getWeeklyTwentyMCQs($data);
                    return $fun;
                }
            case 'get-exam-list-by-type' : {
                    $fun = $std_cont->getExamList($data);
                    return $fun;
                }
            case 'submit-exam' : {
                    $fun = $obj->submitExam($data);
                    return $fun;
                }
            case 'get-info' : {
                    $fun = $obj->getInfo();
                    return $fun;
                }
            case 'give-feedback' : {
                    $fun = $obj->giveFeedbackToExam($data);
                    return $fun;
                }
            case 'get-exam-result-by-stud-id' : {
                    global $auth_obj;
                    $stud_id = $auth_obj->user->user_id;
                    $fun = $obj->getExamResultByStudentId($stud_id);
                    return $fun;
                }
            case 'get-exam-result-details' : {
                    $fun = $obj->getExamResultInDetails($data);
                    return $fun;
                }
            case 'update-profile' : {
                    $fun = $obj->updateProfile($data);
                    return $fun;
                }
            case 'add-users-question' : {
                    $fun = $obj->addUsersQuestion($data);
                    return $fun;
                }
            case 'get-current-exam-result' : {
                    $fun = $obj->getCurrentExamResult($data);
                    return $fun;
                }
            case 'get-keyword-wise-student-result' : {
                    $fun = $obj->getKeywordWiseStudentExamREsult($data);
                    return $fun;
                }
            case 'get-ranking-list' : {
                    $fun = $obj->getRankingList($data);
                    return $fun;
                }
            case 'initiate-payment' : {
                    $fun = $obj->initiatePayment($data);
                    return $fun;
                }
            case 'settle-payment' : {
                    $fun = $obj->settlePayment($data);
                    return $fun;
                }
            case 'get-question-solution-list' : {
                    $fun = $obj->getQuestionsSolutionListByExamId($data);
                    return $fun;
                }
            case 'get-chart-by-difficulty-level' :{
                    $exam_id = $data->exam_id;
                    $fun = $obj->getChartByDifficultyLevels($exam_id);
                    return $fun;
                }
            case 'get-chart-by-keywords' :{
                    $exam_id = $data->exam_id;
                    $fun = $obj->getChartByKewords($exam_id);
                    return $fun;
                }    
        }
    }

    function admin($methodname, $data) {
        $obj = new adminController ();
        
        switch ($methodname) {
            case 'save-questions' : {
                    $fun = $obj->saveQuestions($data);
                    return $fun;
                }
            case 'get-question-list' : {
                    $fun = $obj->getAllQuestionList($data);
                    return $fun;
                }
            case 'update-questions' : {
                    $fun = $obj->updateQuestions($data);
                    return $fun;
                }
            case 'delete-question-by-id' : {
                    $fun = $obj->deleteQuestionById($data);
                    return $fun;
                }
            case 'add-exam-cat' : {
                    $fun = $obj->addExamCategory($data);
                    return $fun;
                }
            case 'edit-exam-cat' : {
                    $fun = $obj->updatExamCeategory($data);
                    return $fun;
                }
            case 'add-exam-sub-cat' : {
                    $fun = $obj->addExamSubCategory($data);
                    return $fun;
                }
            case 'edit-exam-sub-cat' : {
                    $fun = $obj->updatExamSubCategory($data);
                    return $fun;
                }
            case 'get-exam-cat-list' : {
                    $fun = $obj->getExamCatList();
                    return $fun;
                }
            case 'get-exam-sub-cat-list' : {
                    $fun = $obj->getExamSubCatList();
                    return $fun;
                }
            case 'get-exam-list' : {
                    $fun = $obj->getExamList(null);
                    return $fun;
                }
            case 'get-cat-by-cat-id' : {
                    $cat_id = $_GET['cat_id'];
                    $fun = $obj->getCategoryDetails($cat_id);
                    return $fun;
                }
            case 'get-sub-cat-by-sub-cat-id' : {
                    $sub_cat_id = $_GET['sub_cat_id'];
                    $fun = $obj->getSubCategoryDetails($sub_cat_id);
                    return $fun;
                }
            case 'get-sub-cat-by-cat-id' : {
                    $cat_id = $_GET['cat_id'];
                    $fun = $obj->getSubCategoryListByCatId($cat_id);
                    return $fun;
                }
            case 'get-question-details-by-id' : {
                    $que_id = $_GET['que_id'];
                    $fun = $obj->getQuestionDetailsById($que_id);
                    return $fun;
                }
            case 'get-info' : {
                    $fun = $obj->getInfo();
                    return $fun;
                }
            case 'add-exam' : {
                    $fun = $obj->addExam($data);
                    return $fun;
                }
            case 'update-exam' : {
                    $fun = $obj->updateExam($data);
                    return $fun;
                }
            case 'exam-type-list' : {
                    $fun = $obj->getExamTypeList();
                    return $fun;
                }
            case 'auto-search-questions' : {
                    $fun = $obj->getQuestionsByAutoSearch($data);
                    return $fun;
                }
            case 'add-feedback-questions' : {
                    $fun = $obj->addFeedbackQuestion($data);
                    return $fun;
                }
            case 'get-feedback-que-list' : {
                    $fun = $obj->getFeedbackQueList();
                    return $fun;
                }
            case 'get-feedback-que-by-id' : {
                    $que_id = $_GET['id'];
                    $fun = $obj->getFeedbackQueById($que_id);
                    return $fun;
                }
            case 'delete-exam' : {
                    $fun = $obj->deleteExam($data);
                    return $fun;
                }
            case 'chat-question-list' : {
                    $fun = $obj->getUsersChatQuestionList();
                    return $fun;
                }
            case 'add-category-levels' : {
                    $fun = $obj->addCategoryLevels($data);
                    return $fun;
                }
            case 'get-category-level-list' : {
                    $fun = $obj->getCategoryLevelList();
                    return $fun;
                }
            case 'get-category-level-by-id' : {
                    $fun = $obj->getCategoryLevelById($data);
                    return $fun;
                }
            case 'get-category-list-by-level' : {
                    $fun = $obj->getCategoryListByLevel(1);
                    return $fun;
                }
            case 'update-category-level' : {
                    $fun = $obj->updateCategoryLevel($data);
                    return $fun;
                }
            case 'get-feedback-ratings-by-exam-id' : {
                    $exam_id = $data->exam_id;
                    $fun = $obj->getFeedbackRatingsByExamId($exam_id);
                    return $fun;
                }
        }
    }

    function common($methodname, $data, $endPt) {
        $obj = new adminController ();
        $is_Admin = 1;
        if ($endPt == 'student') {
            $obj = new studentController ();
            $is_Admin = 0;
        }
        switch ($methodname) {
            case 'register' : {
                    $fun = $obj->register($methodname, $data, $is_Admin);
                    return $fun;
                }
            case 'verify-otp' : {
                    $fun = $obj->verifyOtp($methodname, $data);
                    return $fun;
                }
            case 'forgot-password' : { // verify otp while during register / forgot password
                    $fun = $obj->forgotPassword($methodname, $data);
                    return $fun;
                }  
            case 'reset-password' : {
                    $fun = $obj->resetPassword($data);
                    return $fun;
                }    
            case 'login' : {
                    $fun = $obj->login($data);
                    return $fun;
                } 
            case 'social-login' : {
                    $fun = $obj->socialLogin($data);
                    return $fun;
                }
            // case 'resend-otp' : {
            //     // $fun = $obj->resendOtp($data);
            //     return $fun;
            //     }            
        }
    }

    private function _checkAuthorization($endPt, $methodname) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Data Could not be fetch', 'data' => null];
        $userRole = $auth_obj->user->user_role;
        // check is endpoint accessible for user role
        if ($userRole == 'admin') {
            $checkRs = $this->_isEndPointAccessibleForAdmin($methodname);
        } else {
            $checkRs = $this->_isEndPointAccessibleForStudent($methodname);
        }
        if (!$checkRs) {
            $res->statusCode = 403;
            $res->message = 'You are not authorized to make this request';
        } else {
            $res->statusCode = 200;
            $res->message = 'Authorized Access';
        }
        return $res;
    }

    private function _isEndPointAccessibleForStudent($methodname) {
        $arr = ['get-chart-by-keywords','get-chart-by-difficulty-level','social-login','reset-password','register', 'verify-otp', 'login', 'submit-exam', 'get-exam-result-by-stud-id', 'get-info', 'give-feedback', 'get-exam-result', 'get-exam-result-details', 'get-exam-list-by-exam-type', 'update-profile', 'add-users-question', 'get-current-exam-result', 'get-ranking-list', 'get-feedback-que-list', 'initiate-payment', 'settle-payment', 'get-keyword-wise-student-result', 'get-exam-list-by-type', 'get-question-solution-list', 'get-exam-questions-by-exam-id'];
        return in_array($methodname, $arr);
    }

    private function _isEndPointAccessibleForadmin($methodname) {
        $arr = ['social-login','reset-password','register', 'verify-otp', 'login', 'save-questions', 'update-questions', 'add-exam-cat', 'edit-s-cat', 'add-exam-sub-cat', 'edit-exam-sub-cat', 'edit-exam-cat', 'get-exam-cat-list', 'get-cat-by-cat-id', 'get-sub-cat-by-cat-id', 'get-question-details-by-id', 'get-daily-10-mcqs', 'get-info', 'add-exam', 'exam-type-list', 'update-exam', 'auto-search-questions', 'add-feedback-questions', 'get-question-list', 'delete-question-by-id', 'get-exam-list', 'get-exam-sub-cat-list', 'update-exam-cat', 'delete-exam', 'chat-question-list', 'add-category-levels', 'get-category-level-list', 'update-category-level', 'get-category-level-by-id', 'get-category-list-by-level', 'get-feedback-ratings-by-exam-id'];
        return in_array($methodname, $arr);
    }

}

$obj = new main();
