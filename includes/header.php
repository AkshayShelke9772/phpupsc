<?php

// need to be add in all files for accessing the external API's
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
//header('Content-Type: application/x-www-form-urlencoded');

global $auth_obj;

session_abort();

define('commtext', 'l0a@c7r4e');
define('RZRPAY_TEST_KEY', 'rzp_test_fyQU301XgEWZ2L');
define('RZRPAY_SECURE_KEY', 'cIQrwYnbb6Um4Yl27wSN8zaq');




// define the site url 
//define(SITE_URL, 'localhost/upsc');


