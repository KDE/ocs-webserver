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
        $cookie_params = session_get_cookie_params();
        setcookie("OcsWebserverId", $session_id, time()+31536000,  $cookie_params['path'], $_SERVER['HTTP_HOST'], $cookie_params['secure'], true);
    }
}
include dirname(__FILE__) . "/../theme/flatui/css/empty.css";
if(extension_loaded('zlib')){ob_end_flush();}