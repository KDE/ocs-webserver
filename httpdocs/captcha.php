<?php

session_start();

$num1=rand(1,9); //Generate First number between 1 and 9  
$num2=rand(1,9); //Generate Second number between 1 and 9  
$captcha_total=$num1+$num2;  

$math = "$num1"." + "."$num2"." =";  

$_SESSION['rand_code'] = $captcha_total;

$font = 'mmobuyit-captcha-fonts/Arial.ttf';

$image = imagecreatetruecolor(120, 30); //Change the numbers to adjust the size of the image
$black = imagecolorallocate($image, 0, 0, 0);
$color = imagecolorallocate($image, 0, 100, 90);
$white = imagecolorallocate($image, 0, 26, 26);

imagefilledrectangle($image,0,0,399,99,$white);
imagettftext ($image, 20, 0, 20, 25, $color, $font, $math );//Change the numbers to adjust the font-size

header("Content-type: image/png");
imagepng($image);


