<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/upscMcqsAPI/includes/db/db_connect.php';

class adminModel {

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

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        try {
            $stud_id = $this->_getMaxTableId($this->con, 'student_master', 'student_id');
            $name = trim($data->name);
            $email = trim($data->email);
            $contact = trim($data->mobile);
            $password = strlen($data->password) > 0 ? sha1($data->password) : 'null';
            $nickname = isset($data->nickname) ? trim($data->nickname) : 'null';
            $address = isset($data->address) ? trim($data->address) : 'null';
            $pin_code = isset($data->pin_code) ? ($data->pin_code) : 'null';
            $created_date = date('Y-m-d H:i:s');
            $updated_date = date('Y-m-d H:i:s');
            $status = 'A';
            $is_by_social_media = isset($data->client_id) ? 1 : 0;
            $sql = "INSERT INTO `student_master`(`student_id`, `full_name`, `email_id`, `phone_number`, `nick_name`, `address`, `password`,`is_admin`, `pin_code`,`is_by_social_media`,`login_by`, `client_id`, `created_date`, `updated_date`, `status`) VALUES ($stud_id, '$name', '$email', $contact, $nickname, $address, '$password', $is_Admin, $pin_code,$is_by_social_media, '$data->login_by','$data->client_id', '$created_date', '$updated_date', '$status')";
            if ($this->con->query($sql)) {
                $res->status = true;
                $res->message = 'Student Registered Successfully';
                $res->data['stud_id'] = $stud_id;
            } else {
                $res->status = false;
                $res->message = 'Could not save data';
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'phone_number') !== false) {
                $res->status = false;
                $res->message = 'Student with this mobile number registered already.';
            }
        }
        $this->close = null;
        return $res;
    }

    function saveQuestions($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $qu_id = $this->_getMaxTableId($this->con, 'question_master', 'id');
        $sub_cat_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $question = trim($data->question);
        $description = trim($data->description);
        $option1 = trim($data->option1);
        $option2 = trim($data->option2);
        $option3 = trim($data->option3);
        $option4 = trim($data->option4);
        $option5 = trim($data->option5);
        $answer = trim($data->answer);
        $answer_seq_id = trim($data->answer_seq_id);
        $marks_per_que = ($data->marks_per_que);
        $difficullty_level = ($data->difficullty_level);
        $keywords = ($data->keywords);
        $answer_description = trim($data->answer_description);
        $created_date = date('Y-m-d H:i:s');
        $updated_date = date('Y-m-d H:i:s');
        $status = 'A';
        $stmt = $this->con->prepare("INSERT INTO `question_master`(`id`, `sub_category_id`, `question`, `description`, `option1`, `option2`,`option3`, `option4`, `option5`, `answer`,`answer_seq_id`, `answer_description`, `status`,`marks_per_que`,`difficullty_level`, `keywords`,  `created_date`) VALUES ('$qu_id', $sub_cat_id, '$question', '$description', '$option1', '$option2', '$option3', '$option4', '$option5', '$answer','$answer_seq_id', '$answer_description', '$status', '$marks_per_que','$difficullty_level','$keywords', '$created_date')");
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

    function addFeedbackQuestion($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $qu_id = $this->_getMaxTableId($this->con, 'feedback_question_master', 'id');
        $sub_cat_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $question = trim($data->question);
        $created_by = 1;
        $updated_by = 1;
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
        $stmt = $this->con->prepare("INSERT INTO `feedback_question_master`(`id`, `sub_category_id`, `question`, `description`, `option1`, `option2`,`option3`, `option4`, `option5`, `answer`,`answer_description`, `status`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES ('$qu_id', $sub_cat_id, '$question', '$description', '$option1', '$option2', '$option3', '$option4', '$option5', '$answer', '$answer_description', '$status', '$created_date', '$created_by', '$updated_date', '$updated_by')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Feedback questions added Successfully';
            $res->data['id'] = $qu_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function addExamCategory($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $cat_id = $this->_getMaxTableId($this->con, 'category_master', 'category_id');
        $cat_name = trim($data->cat_name);
        $desc = trim($data->desc);
        $created_date = date('Y-m-d H:i:s');
        $updated_date = date('Y-m-d H:i:s');
        $status = 'A';
        $created_by = 1;
        $stmt = $this->con->prepare("INSERT INTO `category_master`(`category_id`, `category_name`, `description`, `created_date`, `created_by`, `status`) VALUES ('$cat_id', '$cat_name', '$desc', '$created_date', '$created_by', '$status')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Category added Successfully';
            $res->data['category_id'] = $cat_id;
            $res->data['category_name'] = $cat_name;
            $res->data['description'] = $desc;
            $res->data['created_date'] = $created_date;
            $res->data['status'] = $status;
            $res->data['created_by'] = $created_by;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function saveQuestionAgainstExamId($exam_id, $question_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $exam_qu_id = $this->_getMaxTableId($this->con, 'exam_question_mapper', 'id');
        $stmt = $this->con->prepare("INSERT INTO `exam_question_mapper`(`id`, `exam_id`, `que_id`) VALUES ('$exam_qu_id', '$exam_id', '$question_id')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Link the question with exam successfully.';
            $res->data['id'] = $exam_qu_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function addExamSubCategory($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $sub_cat_id = $this->_getMaxTableId($this->con, 'sub_category_master', 'sub_category_id');
        $sub_cat_name = trim($data->sub_category_name);
        $cat_id = trim($data->cat_id);
        $created_date = date('Y-m-d H:i:s');
        $updated_date = date('Y-m-d H:i:s');
        $status = 'A';
        $created_by = 1;
        $stmt = $this->con->prepare("INSERT INTO `sub_category_master`(`sub_category_id`, `category_id`,`sub_category_name`, `created_date`, `created_by`, `status`) VALUES ('$sub_cat_id', '$cat_id', '$sub_cat_name', '$created_date', '$created_by', '$status')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Sub Category added Successfully';
            $res->data['sub_category_id'] = $sub_cat_id;
            $res->data['category_id'] = $cat_id;
            $res->data['sub_category_name'] = $sub_cat_name;
            $res->data['created_date'] = $created_date;
            $res->data['created_by'] = $created_by;
            $res->data['status'] = $status;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function submitExam($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_student_mapper', 'id');
        $created_date = date('Y-m-d H:i:s');
        $created_by = 1;
        $status = 'A';
        $stmt = $this->con->prepare("INSERT INTO `exam_student_mapper`(`id`, `exam_id`, `student_id`, `attempted_date`, `result_exam`, `total_time_all_que`, `created_date`, `created_by`) VALUES ('$id', '$data->exam_id', '$data->student_id', '$data->attempted_date', '$data->result_exam', '$data->total_time_taken', '$created_date', '$created_by')");
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

    function saveExamQuestionAnswers($exam_stud_map_id, $data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_details_result_mapper', 'id');
        $created_date = date('Y-m-d H:i:s');
        $attempted_date = date('Y-m-d H:i:s');
        $created_by = 1;
        $status = 'A';
        $stmt = $this->con->prepare("INSERT INTO `exam_details_result_mapper`(`id`, `exam_stud_map_id`, `exam_id`, `question_id`, `correct_answer`, `select_answer`, `is_correct_wrong`,`attempted_date`,`total_time_per_que`, `created_date`, `created_by`, `Status`) VALUES ('$id', '$exam_stud_map_id', '$data->exam_id', '$data->question_id', '$data->correct_answ', '$data->selected_answer', '$data->is_correct', '$attempted_date', '$data->total_time_per_que', '$created_date', '$created_by', '$status');");
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

    function addExam($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'exam_master', 'exam_id');
        $created_date = date('Y-m-d H:i:s');
        $created_by = 1;
        $status = 'A';
        $start_data = date('Y-m-d H:i:s', strtotime($data->start_date));
        $end_data = date('Y-m-d H:i:s', strtotime($data->end_date));
        $cat_id = !is_null($data->cat_id) ? $data->cat_id : 'null';
        $sub_cat_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';

        $stmt = $this->con->prepare("INSERT INTO `exam_master`(`exam_id`, `exam_name`, `description`, `no_of_que`, `is_penaulty`, `start_date`, `end_date`,`duration`, `exam_type_id`,`category_id`, `sub_cat_id`,`is_publish`, `created_date`, `created_by`, `Status`) VALUES ('$id', '$data->exam_name', '$data->desc', '$data->no_of_que', '$data->is_penaulty', '$start_data', '$end_data','$data->duration', '$data->exam_type_id', $cat_id, $sub_cat_id, $data->is_publish, '$created_date', '$created_by', '$status');");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Added Successfully';
            $res->data['exam_id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function linkQuestionsToExams($exam_id, $que_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $exam_question_mapper_id = $this->_getMaxTableId($this->con, 'exam_question_mapper', 'id');
        $stmt = $this->con->prepare("INSERT INTO `exam_question_mapper`(`id`, `exam_id`, `que_id`) VALUES ('$exam_question_mapper_id', $exam_id, $que_id);");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions linked with exam successfully';
            $res->data['id'] = $exam_question_mapper_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function unlinkTheQuestionsByExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $stmt = $this->con->prepare("DELETE FROM `exam_question_mapper` WHERE `exam_id` = $exam_id ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions unlinked successfully.';
            $res->data['id'] = $exam_id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function deleteQuestionById($que_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $stmt = $this->con->prepare("DELETE FROM `question_master` WHERE `id` = $que_id ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions unlinked successfully.';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function deleteExam($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $stmt = $this->con->prepare("UPDATE `exam_master` SET Status = 'D' WHERE exam_id = " . $exam_id);
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Deleted Successfully.';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function unlinkQuestionsFromMapper($que_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $stmt = $this->con->prepare("DELETE FROM `exam_question_mapper` WHERE `id` = $que_id ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions unlinked successfully.';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateQuestions($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $sub_cat_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $question = (isset($data->question)) ? trim($data->question) : trim($data->question);
        $description = (strlen($data->description) > 0) ? trim($data->description) : null;
        $option1 = (isset($data->option1)) ? trim($data->option1) : trim($data->option1);
        $option2 = (isset($data->option2)) ? trim($data->option2) : trim($data->option2);
        $option3 = (isset($data->option3)) ? trim($data->option3) : trim($data->option3);
        $option4 = (isset($data->option4)) ? trim($data->option4) : trim($data->option4);
        $option5 = (isset($data->options5)) ? trim($data->option5) : trim($data->option5);
        $answer = (isset($data->answer)) ? trim($data->answer) : trim($data->answer);
        $answer_seq_id = (isset($data->answer_seq_id)) ? trim($data->answer_seq_id) : trim($data->answer_seq_id);
        $difficullty_level = (isset($data->difficullty_level)) ? trim($data->difficullty_level) : trim($data->difficullty_level);
        $keywords = (isset($data->keywords)) ? trim($data->keywords) : trim($data->keywords);
        $answer_description = (isset($data->answer_description)) ? trim($data->answer_description) : trim($data->answer_description);
        $updated_date = date('Y-m-d H:i:s');
        $status = $data->status;
        $stmt = $this->con->prepare("update question_master set sub_category_id = $sub_cat_id, question = '$question', description = '$description', option1 = '$option1', option2 = '$option2',option3 = '$option3', option4 = '$option4', option5 = '$option5', answer = '$answer',answer_description = '$answer_description', answer_seq_id = '$answer_seq_id',  status = '$status ', updated_date = '$updated_date', difficullty_level = '$difficullty_level', keywords = '$keywords' where id = '$data->qu_id';");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Questions updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updatExamCeategory($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $cat_name = trim($data->cat_name);
        $desc = trim($data->desc);
        $status = $data->status;
        $cat_id = $data->cat_id;
        $stmt = $this->con->prepare("update category_master set category_name ='$cat_name', description = '$desc', status = '$status', updated_date = '$updated_date' where category_id = '$cat_id';");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam Category updated Successfully';
            $res->data['category_id'] = $cat_id;
            $res->data['category_name'] = $cat_name;
            $res->data['description'] = $desc;
            $res->data['status'] = $status;
            $res->data['updated_date'] = $updated_date;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updatExamSubCategory($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $sub_cat_name = trim($data->sub_cat_name);
        $status = $data->status;
        $cat_id = $data->cat_id;
        $updated_by = 1;
        $sub_cat_id = $data->sub_cat_id;
        $stmt = $this->con->prepare("update sub_category_master set category_id = $cat_id,sub_category_name = '$sub_cat_name', status = '$status', updated_date = '$updated_date', updated_by = $updated_by where sub_category_id = $sub_cat_id;");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam SubCategory updated Successfully';
            $res->data['sub_category_id'] = $sub_cat_id;
            $res->data['category_id'] = $cat_id;
            $res->data['sub_cat_name'] = $sub_cat_name;
            $res->data['status'] = $status;
            $res->data['updated_date'] = $updated_date;
            $res->data['updated_by'] = $updated_by;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateCategoryLevel($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $updated_by = 1;
        $cat_name = trim($data->cat_name);
        $level = $data->level;
        $status = $data->status;
        $id = $data->id;
        $level1_cat_id = strlen($data->level1_cat_id) > 0 ? $data->level1_cat_id : 'null';
        $level2_cat_id = strlen($data->level2_cat_id) > 0 ? $data->level2_cat_id : 'null';
        $level3_cat_id = strlen($data->level3_cat_id) > 0 ? $data->level3_cat_id : 'null';

        $stmt = $this->con->prepare("update category_level set category_name = '$cat_name',level = '$level', level1_cat_id = $level1_cat_id, level2_cat_id=$level2_cat_id, level3_cat_id=$level3_cat_id, status='$status',  updated_date = '$updated_date', updated_by = '$updated_by' where id = $id");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Category level updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateExam($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $status = $data->status;
        $updated_by = 1;
        $start_date = date('Y-m-d H:i:s', strtotime($data->start_date));
        $end_date = date('Y-m-d H:i:s', strtotime($data->end_date));
        $start_time = date('Y-m-d H:i:s', strtotime($data->start_time));
        $end_time = date('Y-m-d H:i:s', strtotime($data->end_time));
        $sub_category_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $category_id = !is_null($data->cat_id) ? $data->cat_id : 'null';
        $stmt = $this->con->prepare("update `exam_master` set `exam_name` = '$data->exam_name',`description` = '$data->desc', `no_of_que` = '$data->no_of_que', `is_penaulty` = '$data->is_penaulty', `start_date` = '$start_date', `end_date` = '$end_date',`start_time` = '$start_time',`end_time` = '$end_time', `duration`= '$data->duration', `exam_type_id` = '$data->exam_type_id',`category_id` = $category_id, `sub_cat_id`  =$sub_category_id, `updated_date` = '$updated_date',`updated_by` = '$updated_by',`Status` = '$status', `is_publish` = '$data->is_publish'  where `exam_id` = '$data->exam_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Exam updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function updateFeedbackQuestion($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be update', 'data' => null];
        $updated_date = date('Y-m-d H:i:s');
        $status = $data->status;
        $updated_by = 1;
        $sub_category_id = !is_null($data->sub_cat_id) ? $data->sub_cat_id : 'null';
        $stmt = $this->con->prepare("update `feedback_question_master` set `sub_category_id` = $sub_category_id,`question` = '$data->question',  `description` = '$data->description', `option1` = '$data->option1', `option2` = '$data->option2', `option3` = '$data->option3', `option4` = '$data->option4',`option5` = '$data->option5',`answer` = '$data->answer', `answer_description`= '$data->answer_description', `updated_date` = '$updated_date',`updated_by` = '$updated_by',`status` = '$data->status'  where `id` = '$data->feedback_id' ");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Feedback question updated Successfully';
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    // function register the user
    function getUserData($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetch', 'data' => null];
        $mobile = trim($data->mobile);
        $sql = "select * from student_master where (phone_number='$mobile')";
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
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['user_data'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['user_data'] = null;
            }
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

    function getQuestionSetOfDailyMcqs() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "select * from question_master where status = 'A'";
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

    function getAllQuestionList($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = "select * from question_master where status = 'A'";

        if (strlen($data->keyword) > 0) {
            $sql .= ' AND keywords LIKE  "%' . "$data->keyword" . '%" ';
        }
        if (strlen($data->difficullty_level) > 0) {
            $sql .= ' AND difficullty_level LIKE  "%' . "$data->difficullty_level" . '%" ';
        }
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
                    $obj->answer_description = $row['answer_description'];
                    $obj->answer_seq_id = $row['answer_seq_id'];
                    $obj->difficullty_level = $row['difficullty_level'];
                    $obj->keywords = $row['keywords'];
                    $obj->question = (object) ['options' => []];
                    $obj->created_date = $row['created_date'];
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

    // function register the user
    function forgotPassword($userId, $username) {
        // return $username;

        $userId = mysqli_real_escape_string($this->con, $userId);
        $username = mysqli_real_escape_string($this->con, $username);

        $sql = "select email from users where (email='$username' or mobile='$username') and UserId='$userId'";

        $result = $this->con->prepare($sql);

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
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $otp = trim($otp);
        $valid_time = (date('Y-m-d H:i:s'));
//        $stmt = "update student_master set otp= " . $otp . ", Valid_Time= " . $valid_time . " where student_id= " . $userId . ";";
        $stmt = sprintf('update student_master set otp=%s, Valid_Time = "%s" where student_id=%s', $otp, $valid_time, $userId);
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

// save otp in database
    function addCategoryLevels($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $id = $this->_getMaxTableId($this->con, 'category_level', 'id');
        $cat_name = trim($data->cat_name);
        $level = $data->level;
        $level1_cat_id = strlen($data->level1_cat_id) > 0 ? $data->level1_cat_id : 'null';
        $level2_cat_id = strlen($data->level2_cat_id) > 0 ? $data->level2_cat_id : 'null';
        $level3_cat_id = strlen($data->level3_cat_id) > 0 ? $data->level3_cat_id : 'null';
        $created_date = date('Y-m-d H:i:s');
        $updated_date = date('Y-m-d H:i:s');
        $status = 'A';
        $updated_by = 1;
        $created_by = 1;
        $stmt = $this->con->prepare("INSERT INTO `category_level`(`id`, `category_name`, `level`, `level1_cat_id`, `level2_cat_id`, `level3_cat_id`,`created_date`, `created_by`, `updated_date`, `updated_by`) VALUES ('$id', '$cat_name', '$level',$level1_cat_id, $level2_cat_id, $level3_cat_id, '$created_date', '$created_by', '$updated_date', '$updated_by')");
        if ($stmt->execute()) {
            $res->status = true;
            $res->message = 'Category added Successfully';
            $res->data['id'] = $id;
        } else {
            $res->status = false;
        }
        $this->close = null;
        return $res;
    }

    function verifyOtp($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be saved', 'data' => null];
        $mobile = $data->mobile;
        $otp = trim($data->otp);
        $sql = "select * from student_master where phone_number='$mobile' and otp='$otp'";
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $last = $row["Valid_Time"];
                    $now = (date('Y-m-d H:i:s'));
                    $diff = strtotime($now) - strtotime($last);
                    $minutes = $diff / 60;
                }
                if ($minutes < 10) {
                    $updateSql = "update student_master set is_verfiy_otp = 1 where phone_number='$mobile'";
                    $sql1 = $this->con->prepare($updateSql);
                    if ($sql1->execute()) {
                        $res->status = true;
                        $res->message = 'Verify OTP successfully';
                    } else {
                        $res->status = false;
                        $res->message = 'Could not update OTP status';
                    }
                } else {
                    $deletesql = "delete from student_master where phone_number='$mobile' and otp='$otp'";
                    $sql2 = $this->con->prepare($deletesql);
                    if ($sql2->execute()) {
                        $res->status = false;
                        $res->message = 'Timeout. Please fill the form again'; //otp expire
                    }
                }
            } else {
                $res->status = false;
                $res->message = 'Invalid OTP or Mobile No.';
            }
        } else {
            $res->status = false;
            $res->message = 'Invalid OTP'; //Invalid OTP
        }
        return $res;
    }

// end verify()

    function changePassword($userId, $newPassword) {

        $sql = "select * from forgotPass where otp=' $userId'";
        $result = $this->con->prepare($sql);

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

    function getExamResultByStudentId($stud_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("select es.`id`, es.`attempted_date`, es.`result_exam`, es.`total_time_all_que`, s.`full_name`, s.`phone_number`, e.`exam_name` FROM `exam_student_mapper` es INNER JOIN `exam_master` e
 ON es.`exam_id` =  e.`exam_id` INNER JOIN `student_master` s ON es.`student_id` =  s.`student_id` where es.`student_id` = %s AND e.`is_publish` = ?", $stud_id, 1);
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

    function getCategoryDetails($cat_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("SELECT * FROM `category_master` WHERE `category_id` = %s ORDER BY `created_date` DESC ", $cat_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->category_id = $row['category_id'];
                    $obj->category_name = $row['category_name'];
                    $obj->description = $row['description'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    $obj->Status = $row['Status'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['cat_details'] = $obj;
            }
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['cat_details'] = [];
        }
        return $res;
    }

    function getCategoryLevelById($data) {
        $level1_cat_id = strlen($data->level1_cat_id) > 0 ? $data->level1_cat_id : 'IS NULL';
        $level2_cat_id = strlen($data->level2_cat_id) > 0 ? $data->level2_cat_id : 'IS NULL';
        $level3_cat_id = strlen($data->level3_cat_id) > 0 ? $data->level3_cat_id : 'IS NULL';

        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $where_cond = '';
        if (strlen($data->level1_cat_id) > 0) {
            $where_cond .= ' AND `level1_cat_id` = ' . $level1_cat_id;
        } else {
            $where_cond .= ' AND `level1_cat_id` IS NULL';
        }
        if (strlen($data->level2_cat_id) > 0) {
            $where_cond .= ' AND `level2_cat_id` = ' . $level2_cat_id;
        } else {
            $where_cond .= ' AND `level2_cat_id` IS NULL';
        }
        if (strlen($data->level3_cat_id) > 0) {
            $where_cond .= ' AND `level3_cat_id` = ' . $level3_cat_id;
        } else {
            $where_cond .= ' AND `level3_cat_id` IS NULL';
        }
        $sql = sprintf('SELECT * FROM `category_level` WHERE `level` = %s %s ORDER BY `created_date` DESC', $data->level, $where_cond);

        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->category_name = $row['category_name'];
                    $obj->level = $row['level'];
                    $obj->level1_cat_id = $row['level1_cat_id'];
                    $obj->level2_cat_id = $row['level2_cat_id'];
                    $obj->level3_cat_id = $row['level3_cat_id'];
                    $obj->status = $row['status'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['cat_levels'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['cat_levels'] = [];
            }
        }
        return $res;
    }

    function getCategoryListByLevel($level) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT * FROM `category_level` WHERE `level` = %s', $level);

        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->category_name = $row['category_name'];
                    $obj->level = $row['level'];
                    $obj->level1_cat_id = $row['level1_cat_id'];
                    $obj->level2_cat_id = $row['level2_cat_id'];
                    $obj->level3_cat_id = $row['level3_cat_id'];
                    $obj->status = $row['status'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['cat_levels'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['cat_levels'] = [];
            }
        }
        return $res;
    }

    function getSubCategoryDetails($cat_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("SELECT s.*, c.`category_name` FROM `sub_category_master` s INNER JOIN `category_master` c ON c.`category_id` = s.`category_id`  WHERE s.`sub_category_id` = %s", $cat_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->sub_category_name = $row['sub_category_name'];
                    $obj->category_name = $row['category_name'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    $obj->Status = $row['Status'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['sub_cat_details'] = $obj;
            }
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['sub_cat_details'] = [];
        }
        return $res;
    }

    function getSubCategoryListByCatId($cat_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("SELECT s.*, c.`category_name` FROM `sub_category_master` s INNER JOIN `category_master` c ON c.`category_id` = s.`category_id`  WHERE s.`category_id` = %s", $cat_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->sub_category_name = $row['sub_category_name'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->Status = $row['Status'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
            }
            $res->status = true;
            $res->message = 'Got Data Successfully';
            $res->data['cat_list'] = $arr;
        } else {
            $res->status = false;
            $res->message = 'Could not get data';
            $res->data['cat_list'] = [];
        }
        return $res;
    }

    function getQuestionDetailsById($cat_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("SELECT qs.*, s.`sub_category_name`, c.`category_name` FROM `question_master` qs LEFT JOIN `sub_category_master` s ON s.`sub_category_id` = qs.`sub_category_id` LEFT JOIN `category_master` c ON c.`category_id` = s.`category_id` WHERE qs.`id` = %s", $cat_id);
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->id = $row['id'];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->sub_category_name = $row['sub_category_name'];
                    $obj->category_name = $row['category_name'];
                    $obj->description = $row['description'];
                    $obj->question = (object) ['options' => []];
                    $obj->question->que_name = $row['question'];
                    $obj->question->options[] = $row['option1'];
                    $obj->question->options[] = $row['option2'];
                    $obj->question->options[] = $row['option3'];
                    $obj->question->options[] = $row['option4'];
                    $obj->question->options[] = $row['option5'];
                    $obj->answer = $row['answer'];
                    $obj->answer_seq_id = $row['answer_seq_id'];
                    $obj->marks_per_que = $row['marks_per_que'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_set'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_set'] = [];
            }
        }
        return $res;
    }

    function getExamTypeList() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf("SELECT * FROM `exam_type_master`WHERE `Status` = %s ", ' "A" ');
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            $arr = [];
            if ($number_of_rows > 0) {
                $obj = (object) [];
                foreach ($this->con->query($sql) as $row) {
                    $obj->id = $row['id'];
                    $obj->exam_type_id = $row['exam_type_id'];
                    $obj->name = $row['name'];
                    $obj->description = $row['description'];
                    $obj->created_date = $row['created_date'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_types'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['exam_types'] = $arr;
            }
        }
        return $res;
    }

    function getQuestionsByAutoSearch($data) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = ("SELECT * FROM `question_master` WHERE `question` LIKE '%$data->str%' ");
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            $arr = [];
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->question = $row['question'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['data'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['data'] = [];
            }
        }
        return $res;
    }

    function getExamCatList() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `category_master` Where `Status` = "A" ORDER BY `created_date` DESC ';
        $result = $this->con->prepare($sql);
        $arr = [];

        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->category_id = $row['category_id'];
                    $obj->category_name = $row['category_name'];
                    $obj->description = $row['description'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->Status = $row['Status'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['cat_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['cat_list'] = [];
            }
        }
        return $res;
    }

    function getExamSubCatList() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'SELECT s.*, c.`category_name`, c.`category_id` FROM `sub_category_master` s INNER JOIN `category_master` c ON c.`category_id` = s.`category_id`  WHERE s.`Status` = "A" ORDER BY c.`created_date` DESC ';
        $result = $this->con->prepare($sql);
        $arr = [];
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->sub_category_name = $row['sub_category_name'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->Status = $row['Status'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['sub_cat_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['sub_cat_list'] = [];
            }
        }
        return $res;
    }

//     function getExamList($data) {
//         $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
//         $sql = 'Select * From `exam_master` Where `Status` = "A" AND `is_publish` = 1 ';
//         if (!is_null($data)) {
//             $exam_type_id = $data->exam_type_id;
//             $sql .= ' AND `exam_type_id` = ' . $exam_type_id;
//         }
//         $sql .= ' ORDER BY `created_date` DESC';
//         $arr = [];
//         $result = $this->con->prepare($sql);
//         if ($result->execute()) {
//             $arr = [];
//             $number_of_rows = $result->fetchColumn();
//             if ($number_of_rows > 0) {
//                 foreach ($this->con->query($sql) as $row) {
//                     $obj = (object) [];
//                     $obj->exam_id = $row['exam_id'];
//                     $obj->exam_name = $row['exam_name'];
//                     $obj->description = $row['description'];
//                     $obj->no_of_que = $row['no_of_que'];
//                     $obj->is_penaulty = $row['is_penaulty'];
//                     $obj->start_date = $row['start_date'];
//                     $obj->end_date = $row['end_date'];
// //                    $obj->start_time = date('h:i a', strtotime($row['start_time']));
// //                    $obj->end_time = date('h:i a', strtotime($row['end_time']));
//                     $obj->duration = $row['duration'];
//                     $obj->exam_type_id = $row['exam_type_id'];
//                     $obj->category_id = $row['category_id'];
//                     $obj->sub_cat_id = $row['sub_cat_id'];
//                     $obj->is_publish = $row['is_publish'];
//                     $obj->created_date = $row['created_date'];
//                     $obj->created_by = $row['created_by'];
//                     $obj->updated_date = $row['updated_date'];
//                     $obj->updated_by = $row['updated_by'];
//                     $obj->Status = $row['Status'];
//                     array_push($arr, $obj);
//                 }
//                 $res->status = true;
//                 $res->message = 'Got Data Successfully';
//                 $res->data['exam_list'] = $arr;
//             } else {
//                 $res->status = false;
//                 $res->message = 'Could not get data';
//                 $res->data['cat_list'] = [];
//             }
//         }
//         return $res;
//     }


    // new priyanka ma'am 4-10-2019

    function getExamList($data) {
        global $auth_obj;
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $user_id = $auth_obj->user->user_id;
        $sql = sprintf('Select ep.`pay_id`, er.`exam_type_id`,e.*, CASE WHEN ep.`pay_id` IS NULL THEN 0 ELSE 1 END as `is_subscribe`, CASE WHEN es.`id` IS NULL THEN 0 ELSE 1 END as `is_attempted` From `exam_master` e  INNER JOIN `exam_type_master` et ON et.`id` = e.`exam_type_id` LEFT JOIN `exam_student_mapper` es ON es.`exam_id` = e.`exam_id`  LEFT JOIN `users_exam_payment_request` er ON er.`exam_type_id` = et.`id` AND er.`status` = "paid" AND er.`user_id` = %s  LEFT JOIN `users_exam_payments` ep ON ep.`upcpayrq_id` = er.`upcpayrq_id` AND ep.`status` = "captured"  Where e.`Status` = "A" AND e.`is_publish` = 1 ', $user_id);
        if (!is_null($data)) {
            $exam_type_id = $data->exam_type_id;
            $sql .= ' AND e.`exam_type_id` = ' . $exam_type_id;
        }
        $sql .= ' ORDER BY e.`created_date` DESC';
        
        $result = $this->con->prepare($sql);
       if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->rowCount();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->exam_id = $row['exam_id'];
                    $obj->is_subscribe = $row['is_subscribe'];
                    $obj->is_attempted = $row['is_attempted'];
                    $obj->exam_name = $row['exam_name'];
                    $obj->description = $row['description'];
                    $obj->no_of_que = $row['no_of_que'];
                    $obj->is_penaulty = $row['is_penaulty'];
                    $obj->start_date = $row['start_date'];
                    $obj->end_date = $row['end_date'];
    //                    $obj->start_time = date('h:i a', strtotime($row['start_time']));
    //                    $obj->end_time = date('h:i a', strtotime($row['end_time']));
                    $obj->duration = $row['duration'];
                    $obj->exam_type_id = $row['exam_type_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->sub_cat_id = $row['sub_cat_id'];
                    $obj->is_publish = $row['is_publish'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    $obj->Status = $row['Status'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['cat_list'] = [];
            }
        }
        return $res;
    }




    function getExamListByExamType($exam_type_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `exam_master` Where `Status` = "A" AND `is_publish` = 1 AND `exam_type_id` = ' . $exam_type_id . ' ORDER BY `created_date` DESC LIMIT 1 OFFSET 0';
        $arr = [];
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->exam_id = $row['exam_id'];
                    $obj->exam_name = $row['exam_name'];
                    $obj->description = $row['description'];
                    $obj->no_of_que = $row['no_of_que'];
                    $obj->is_penaulty = $row['is_penaulty'];
                    $obj->start_date = $row['start_date'];
                    $obj->end_date = $row['end_date'];
//                    $obj->start_time = date('h:i a', strtotime($row['start_time']));
//                    $obj->end_time = date('h:i a', strtotime($row['end_time']));
                    $obj->duration = $row['duration'];
                    $obj->exam_type_id = $row['exam_type_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->sub_cat_id = $row['sub_cat_id'];
                    $obj->is_publish = $row['is_publish'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    $obj->Status = $row['Status'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['exam_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['exam_list'] = [];
            }
        }
        return $res;
    }

    function getQuestionsAgainstExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select q.* FROM `exam_question_mapper` qm INNER JOIN `question_master` q ON qm.`que_id` = q.`id` WHERE qm.`exam_id` = ' . $exam_id;
        $result = $this->con->prepare($sql);
        $arr = [];
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
                    $obj->answer_seq_id = $row['answer_seq_id'];
                    $obj->question = $row['question'];
                    $obj->options = [];
                    $obj->options[] = $row['option1'];
                    $obj->options[] = $row['option2'];
                    $obj->options[] = $row['option3'];
                    $obj->options[] = $row['option4'];
                    $obj->options[] = $row['option5'];
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

    function getFeedbackQueList() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `feedback_question_master` Where `Status` = "A"';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->question = $row['question'];
                    $obj->description = $row['description'];
                    $obj->option1 = $row['option1'];
                    $obj->option2 = $row['option2'];
                    $obj->option3 = $row['option3'];
                    $obj->option4 = $row['option4'];
                    $obj->option5 = $row['option5'];
                    $obj->answer = $row['answer'];
                    $obj->answer_description = $row['answer_description'];
                    $obj->status = $row['status'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_list'] = [];
            }
        }
        return $res;
    }

    function getCategoryLevelList($id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'SELECT s.*, l1.`category_name`as `first_level_cat`,  l2.`category_name`as `sec_level_cat` , l3.`category_name`as `third_level_cat`  FROM `category_level` s LEFT JOIN  `category_level` l1 ON l1.`id` = s.`level1_cat_id` LEFT JOIN  `category_level` l2 ON l2.`id` = s.`level2_cat_id`  LEFT JOIN  `category_level` l3 ON l3.`id` = s.`level3_cat_id`';
        if (!is_null($id)) {
            $sql .= ' WHERE s.`id` = ' . $id;
        }
        $sql .= '  ORDER BY s.`created_date` DESC';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $arr = [];
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->category_name = $row['category_name'];
                    $obj->first_level_cat = $row['first_level_cat'];
                    $obj->sec_level_cat = $row['sec_level_cat'];
                    $obj->third_level_cat = $row['third_level_cat'];
                    $obj->level = $row['level'];
                    $obj->level1_cat_id = $row['level1_cat_id'];
                    $obj->level2_cat_id = $row['level2_cat_id'];
                    $obj->level3_cat_id = $row['level3_cat_id'];
                    $obj->status = $row['status'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['category_level_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['category_level_list'] = [];
            }
        }
        return $res;
    }

    function getFeedbackQueById($que_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `feedback_question_master` Where `id` = ' . $que_id;
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->id = $row['id'];
                    $obj->sub_category_id = $row['sub_category_id'];
                    $obj->question = $row['question'];
                    $obj->description = $row['description'];
                    $obj->option1 = $row['option1'];
                    $obj->option2 = $row['option2'];
                    $obj->option3 = $row['option3'];
                    $obj->option4 = $row['option4'];
                    $obj->option5 = $row['option5'];
                    $obj->answer = $row['answer'];
                    $obj->answer_description = $row['answer_description'];
                    $obj->status = $row['status'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_list'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_list'] = [];
            }
        }
        return $res;
    }


    function getFeedbackRatingsByExamId($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = sprintf('SELECT f.`question`, f.`option1`, f.`option2`, f.`option3`, f.`option4`, f.`option5`, fm.`comment`, fm.`rating` FROM `feedback_question_master` f INNER JOIN `feedback_master` fm ON fm.`feedback_id` = f.`id` WHERE fm.`exam_id` = %s', $exam_id);
        
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->rowCount();
            if ($number_of_rows > 0) {
                $arr = [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->question = $row['question'];
                    $obj->option1 = $row['option1'];
                    $obj->option2 = $row['option2'];
                    $obj->option3 = $row['option3'];
                    $obj->option4 = $row['option4'];
                    $obj->option5 = $row['option5'];
                    $obj->comment = $row['comment'];
                    $obj->rating = $row['rating'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['feedback'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['feedback'] = [];
            }
        }
        return $res;
    }

    function getUsersChatQuestionList() {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `users_question` ORDER BY created_date DESC';
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                $arr = [];
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->question_id = $row['question_id'];
                    $obj->user_name = $row['user_name'];
                    $obj->user_email = $row['user_email'];
                    $obj->question = $row['question'];
                    $obj->created_date = $row['created_date'];
                    array_push($arr, $obj);
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['question_list'] = $arr;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['question_list'] = [];
            }
        }
        return $res;
    }

    function getExamDetails($exam_id) {
        $res = (object) ['status' => FALSE, 'message' => 'Data Could not be fetched', 'data' => null];
        $sql = 'Select * From `exam_master` Where `exam_id` = ' . $exam_id;
        $result = $this->con->prepare($sql);
        if ($result->execute()) {
            $number_of_rows = $result->fetchColumn();
            if ($number_of_rows > 0) {
                foreach ($this->con->query($sql) as $row) {
                    $obj = (object) [];
                    $obj->exam_id = $row['exam_id'];
                    $obj->exam_name = $row['exam_name'];
                    $obj->description = $row['description'];
                    $obj->no_of_que = $row['no_of_que'];
                    $obj->is_penaulty = $row['is_penaulty'];
                    $obj->start_date = $row['start_date'];
                    $obj->end_date = $row['end_date'];
                    $obj->start_time = $row['start_time'];
                    $obj->end_time = $row['end_time'];
                    $obj->duration = $row['duration'];
                    $obj->exam_type_id = $row['exam_type_id'];
                    $obj->category_id = $row['category_id'];
                    $obj->is_publish = $row['is_publish'];
                    $obj->created_date = $row['created_date'];
                    $obj->created_by = $row['created_by'];
                    $obj->updated_date = $row['updated_date'];
                    $obj->updated_by = $row['updated_by'];
                    $obj->Status = $row['Status'];
                }
                $res->status = true;
                $res->message = 'Got Data Successfully';
                $res->data['detail'] = $obj;
            } else {
                $res->status = false;
                $res->message = 'Could not get data';
                $res->data['detail'] = [];
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

?>