<?php


interface Local_Auth_Token_Interface
{

    public function encode(array $authUserData);

    public function decode($jwt, $verify = true);

    public function isValid($jwt);

}