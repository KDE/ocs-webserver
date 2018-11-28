<?php
    if (isset($_SERVER['HTTP_COOKIE'])) {
      $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
      $cookie_params = session_get_cookie_params();
      foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, false, time()-1000, $cookie_params['path'], $_SERVER['HTTP_HOST'], $cookie_params['secure'], true);
      }
   }
