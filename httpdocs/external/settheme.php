<?php
// Define path to application library
defined('APPLICATION_LIB')
|| define('APPLICATION_LIB', realpath(dirname(__FILE__) . '/../../library'));

require  APPLICATION_LIB . "/Local/LoginCookie.php";

if(extension_loaded('zlib')){ob_start('ob_gzhandler');}
header("Content-type: text/css");
if(isset($_GET['k'])) {
    $key = $_GET['k'];
    $session_id = Local_LoginCookie::readJwt($key);
    if ($session_id) {
        setcookie("OcsWebserverId", $session_id, time()+36000000, '/');
    }
}
include dirname(__FILE__) . "/../theme/flatui/css/empty.css";
if(extension_loaded('zlib')){ob_end_flush();}