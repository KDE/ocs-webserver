<?php
 
   $user = array();
   if(isset($_GET['id'])) {
      $user['member_id'] = $_GET['id'];
   }
   if(isset($_GET['name'])) {
      $user['username'] = $_GET['name'];
   }
   if(isset($_GET['mail'])) {
      $user['mail'] = $_GET['mail'];
   }
   if(isset($_GET['avatar'])) {
      $user['avatar'] = $_GET['avatar'];
   }

   setcookie("ocs_data", json_encode($user), time()+36000000, '/');



?>