<?php


interface Local_Auth_User_Interface
{

    public function setUserData(array $user_data);

    public function getUserData();

    public function hasIdentity();

    public function getIdentity();

    public function startSession(array $config);

    public function initSession(array $config);
}