ALTER TABLE `question_master` ADD `difficullty_level` ENUM('very_easy','easy','moderate','difficult','very_difficult') NOT NULL DEFAULT 'easy' AFTER `marks_per_que`, ADD `keywords` VARCHAR(128) NULL DEFAULT NULL AFTER `difficullty_level`;

alter table  `exam_master` ADD CONSTRAINT `FK_SUB_CAT_ID` FOREIGN KEY (`sub_cat_id`) REFERENCES `sub_category_master`(`sub_category_id`)

ALTER TABLE `exam_master` ADD `is_publish` INT(10) NOT NULL DEFAULT '0' AFTER `sub_cat_id`;

ALTER TABLE `exam_type_master` CHANGE `updated_date` `updated_date` TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE `exam_type_master` ADD `duration` varchar(10) NOT NULL  AFTER `description`;

ALTER TABLE `student_master` ADD `total_coins` FLOAT(10) NOT NULL DEFAULT '0.0' AFTER `client_id`;

<!-- update all changes on test server -->


ALTER TABLE `question_master` ADD `answer_seq_id` INT(10) NOT NULL AFTER `answer`;

alter table `users_exam_payment_request` add 
CONSTRAINT `FK_USER_USER_ID` FOREIGN KEY (`user_id`)
    REFERENCES `student_master`(`student_id`)


alter table `users_exam_payments` add 
CONSTRAINT `FK_USER_EXAM_PAY_REQ_ID` FOREIGN KEY (`upcpayrq_id`)
    REFERENCES `users_exam_payment_request`(`upcpayrq_id`)


alter table `users_exam_payments` add 
CONSTRAINT `FK_USERs_EXAM_ID` FOREIGN KEY (`exam_id`)
    REFERENCES `exam_master`(`exam_id`)


ALTER TABLE `users_exam_payment_request` CHANGE `exam` `exam` INT(10) UNSIGNED NOT NULL;


ALTER TABLE `users_exam_payment_request` ADD `attempts` INT(10) NOT NULL AFTER `status`;
