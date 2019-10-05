<?php

require_once 'framework/model/adminModel.php';
require_once 'helper/communicator.php';
require_once 'helper/helper.php';

class adminController {

    function __construct() {
        
    }

    function login($data) {
        global $auth_obj;
        $auth_obj = (object) [];
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $hlpr = new helper();
//        $otpCode = $hlpr->generateRandomOtp();
//        $sendOtpRs = new communicator($otpCode);
//        $rs = $sendOtpRs->sendOtp($otpCode);
        $response = $db->getUserData($data);
        if ($response->status) {
            // check if the is_verify_otp is 1 or not
            $user_data = $response->data['user_data'];
            if (($user_data->password === sha1($data->password))) {
                $res->statusCode = 200;
                $res->message = 'Logged in Successfully';
                unset($user_data->password);
                //  call the helper function for generating token;
                $helper = new helper();
                $token = $helper->getAuthorization($user_data);
                $res->data['user_data'] = $user_data;
                $res->data['user_data']->token = $token;
                $auth_obj->user = $res->data['user_data'];
            } else if ($user_data->is_verify_otp == '0') {
                // send OTP then verify 
//                $otpCode = 7654;
//                $sendOtpRs = new communicator($otpCode);
//                if ($sendOtpRs->status) {
//                    // save OTP in database
//                    $stud_id = $user_data->student_id;
//                    $savRs = $db->saveOtp($stud_id, $otpCode);
//                    if (!$savRs->status) {
//                        $res->statusCode = $savRs->status ? 200 : 403;
//                        $res->message = $savRs->message;
//                    }
//                }
            } else {
                $res->statusCode = 403;
                $res->message = 'Wrong Password';
            }
        } else {
            // uer is not registered
            $res->statusCode = 403;
            $res->message = 'Your not register with us. Please register first';
        }
        return $res;
    }

    function register($methodname, $data, $is_Admin) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        if (isset($data->name) && isset($data->password)) {
            $db = new adminModel();
            $hlpr = new helper();
            $response = $db->register($data, $is_Admin);
            if ($response->status) {
                $res->statusCode = 200;
                $res->message = $response->message;
                // send OTP for the mobile number
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

    function saveQuestions($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->saveQuestions($data);
        $response->status = true;
//        if ($response->status) {
        // save this question against a exam id
        $question_id = 1;
//        $saveRs = $db->saveQuestionAgainstExamId($exam_id, $question_id);
//        if (!$saveRs->status) {
//            $res->statusCode = 403;
//            $res->message = 'Could not link question to exam.';
//        }
        $res->statusCode = 200;
        $res->message = $response->message;
//        } else {
//            $res->statusCode = 403;
//            $res->message = $response->message;
//        }
        return $res;
    }

    function updateQuestions($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updateQuestions($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function deleteQuestionById($data) {
        $que_id = $data->question_id;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        // delete questions from exam_question_mapper first
        $deleteQuesMap = $db->unlinkQuestionsFromMapper($que_id);
        if ($deleteQuesMap->status) {
            // delete questions from question_master table
            $response = $db->deleteQuestionById($que_id);
        }
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function deleteExam($data) {
        $exam_id = $data->exam_id;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        // delete questions from exam_question_mapper first
        $response = $db->deleteExam($exam_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function verifyOtp($methodname, $data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
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

    function getQquestionSetOfDailyMcqs() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
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

    function getAllQuestionList($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getAllQuestionList($data);
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

    function getExamCatList() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getExamCatList();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['cat_list'] = $response->data['cat_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getExamSubCatList() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getExamSubCatList();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['sub_cat_list'] = $response->data['sub_cat_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getExamList($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getExamList($data);
        if ($response->status) {
            $exam_list = $response->data['exam_list'];
            if (count($exam_list) > 0) {
                // get questions against exam id
                foreach ($exam_list as $ek => $ev) {
                    $exam_id = $ev->exam_id;
                    $getQuestionsRs = $db->getQuestionsAgainstExamId($exam_id);
                    if ($getQuestionsRs->status) {
                        $ev->questions = $getQuestionsRs->data['question_set'];
                    } else {
                        $ev->questions = [];
                    }
                }
            }
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['exam_list'] = $response->data['exam_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getCategoryDetails($cat_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getCategoryDetails($cat_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['cat_details'] = $response->data['cat_details'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getSubCategoryDetails($cat_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getSubCategoryDetails($cat_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['sub_cat_details'] = $response->data['sub_cat_details'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getSubCategoryListByCatId($cat_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getSubCategoryListByCatId($cat_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['cat_list'] = $response->data['cat_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getQuestionDetailsById($cat_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getQuestionDetailsById($cat_id);
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

    function getExamTypeList() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getExamTypeList();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['exam_types'] = $response->data['exam_types'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getQuestionsByAutoSearch($post) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getQuestionsByAutoSearch($post);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['data'] = $response->data['data'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getUserDataById($user_id) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
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

    function addExamCategory($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->addExamCategory($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['category_data'] = $response->data;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function addExamSubCategory($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->addExamSubCategory($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['sub_category_data'] = $response->data;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function addExam($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->addExam($data);
        if ($response->status) {
            $exam_id = $response->data['exam_id'];
            if (count($data->question_list) > 0) {
                foreach ($data->question_list as $dk => $dv) {
                    $linkRs = $db->linkQuestionsToExams($exam_id, $dv);
                    if (!$linkRs->status) {
                        $res->statusCode = 403;
                        $res->message = 'Could not link questions to exam';
                    }
                }
            } else {
                $res->statusCode = 403;
                $res->message = 'There is no question list for linking to the exam';
            }
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function addFeedbackQuestion($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->addFeedbackQuestion($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function addCategoryLevels($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->addCategoryLevels($data);
        if ($response->status) {
            $id = $response->data['id'];
            $getRs = $db->getCategoryLevelList($id);
            if ($getRs->status) {
                $res->statusCode = 200;
                $res->message = $getRs->message;
                $res->data['category_level_list'] = $getRs->data['category_level_list'];
            }
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function updatExamCeategory($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updatExamCeategory($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['category_list'] = $response->data;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function updateCategoryLevel($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updateCategoryLevel($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function updatExamSubCategory($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updatExamSubCategory($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['sub_category_list'] = $response->data;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function updateExam($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updateExam($data);
        if ($response->status) {
            // update the exam question link
            // delete first all the entries against this exam id
            $deleteRs = $db->unlinkTheQuestionsByExamId($data->exam_id);
            if ($deleteRs->status) {
                // link the exam with questions again
                if (count($data->question_list) > 0) {
                    foreach ($data->question_list as $dk => $dv) {
                        $linkRs = $db->linkQuestionsToExams($data->exam_id, $dv);
                        if (!$linkRs->status) {
                            $res->statusCode = 403;
                            $res->message = 'Could not link questions to exam.';
                        }
                    }
                }
            }
            $res->statusCode = 200;
            $res->message = $response->message;
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function updateExamCategory($data) {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->updateExamCategory($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
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

    function getFeedbackQueList() {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $user_id = $auth_obj->user->user_id;
        $response = $db->getFeedbackQueList();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['question_list'] = $response->data['question_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getCategoryLevelList() {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getCategoryLevelList(NULL);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['category_level_list'] = $response->data['category_level_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getCategoryLevelById($data) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getCategoryLevelById($data);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['cat_levels'] = $response->data['cat_levels'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getCategoryListByLevel($level) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getCategoryListByLevel($level);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['cat_levels'] = $response->data['cat_levels'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getFeedbackRatingsByExamId($exam_id) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getFeedbackRatingsByExamId($exam_id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['feedback'] = $response->data['feedback'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }



    



    function getFeedbackQueById($id) {
        global $auth_obj;
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $user_id = $auth_obj->user->user_id;
        $response = $db->getFeedbackQueById($id);
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['question_list'] = $response->data['question_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

    function getUsersChatQuestionList() {
        $res = (object) ['statusCode' => 500, 'message' => 'Something Went Wrong', 'data' => null];
        $db = new adminModel();
        $response = $db->getUsersChatQuestionList();
        if ($response->status) {
            $res->statusCode = 200;
            $res->message = $response->message;
            $res->data['question_list'] = $response->data['question_list'];
        } else {
            $res->statusCode = 403;
            $res->message = $response->message;
        }
        return $res;
    }

}
