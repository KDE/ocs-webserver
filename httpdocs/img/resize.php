<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

ini_set("memory_limit", "80M");

# prevent creation of new directories
$is_locked = false;


# figure out requested path and actual physical file paths
$orig_dir = dirname(__FILE__);
$path = $_GET['dir'];
$tokens = explode("/", $path);
$file = "/" . implode('/', array_slice($tokens, 4));
$orig_file = $orig_dir . $file;


if (!file_exists($orig_file)) {
    header("Status: 404 Not Found");
    echo "Status: 404 Not Found";
    error_log("PATH={$path} ==> ORIGFILE={$orig_file}");

    return 0;
}


# check if new directory would need to be created
$save_path = "$orig_dir/cache/$tokens[2]$file";
$save_dir = dirname($save_path);

if (!file_exists($save_dir) && $is_locked) {
    header("Status: 403 Forbidden");
    echo "Status: 403 Forbidden";
    error_log("Directory creation is forbidden. {$save_dir}");

    return 0;
}

# check for a valid image file
if (!getimagesize($orig_file)) {
    header("Status: 404 Not Found");
    echo "Status: 404 Not Found";
    error_log("PATH={$path} ==> ORIGFILE={$orig_file}");

    return 0;
}

# parse out the requested image dimensions and resize mode
$x_pos = strpos($tokens[2], 'x');
$dash_pos = strpos($tokens[2], '-') ? strpos($tokens[2], '-') : strlen($tokens[2]);
$target_width = substr($tokens[2], 0, $x_pos);
$target_height = substr($tokens[2], $x_pos + 1, $dash_pos - $x_pos - 1);
$mode = substr($tokens[2], $dash_pos + 1);

$new_width = $target_width;
$new_height = $target_height;

try {
    $image = new Imagick($orig_file);
} catch (ImagickException $e) {
    header("Status: 500 Internal Server Error");
    echo "Status: 500 Internal Server Error";
    error_log($e->getMessage());

}
list($orig_width, $orig_height, $type, $attr) = getimagesize($orig_file);

# preserve aspect ratio, fitting image to specified box
if ($mode == "0") {
    $new_height = $orig_height * $new_width / $orig_width;
    if ($new_height > $target_height) {
        $new_width = $orig_width * $target_height / $orig_height;
        $new_height = $target_height;
    }
} # zoom and crop to exactly fit specified box
else {
    if ($mode == "2") {
        // crop to get desired aspect ratio
        $desired_aspect = $target_width / $target_height;
        $orig_aspect = $orig_width / $orig_height;

        if ($desired_aspect > $orig_aspect) {
            $trim = $orig_height - ($orig_width / $desired_aspect);
            $image->cropImage($orig_width, $orig_height - $trim, 0, $trim / 2);
            error_log("HEIGHT TRIM $trim");
        } else {
            $trim = $orig_width - ($orig_height * $desired_aspect);
            $image->cropImage($orig_width - $trim, $orig_height, $trim / 2, 0);
        }
    }
}

# mode 3 (stretch to fit) is automatic fall-through as image will be blindly resized
# in following code to specified box
//bugfix: $new_width and $new_height have to be > 0
if ($new_width == 0 && $new_height > 0) {
    $new_width = $new_height;
}
if ($new_height == 0 && $new_width > 0) {
    $new_height = $new_width;
}

$image->resizeImage($new_width, $new_height, imagick::FILTER_LANCZOS, 1);

# save and return the resized image file
if (!file_exists($save_dir)) {
    mkdir($save_dir, 0777, true);
}

$image->writeImage($save_path);


// Parse Info / Get Extension
$fsize = filesize($save_path);
$path_parts = pathinfo($save_path);
$ext = strtolower($path_parts["extension"]);

// Determine Content Type
switch ($ext) {
    case "pdf":
        $ctype = "application/pdf";
        break;
    case "exe":
        $ctype = "application/octet-stream";
        break;
    case "zip":
        $ctype = "application/zip";
        break;
    case "doc":
        $ctype = "application/msword";
        break;
    case "xls":
        $ctype = "application/vnd.ms-excel";
        break;
    case "ppt":
        $ctype = "application/vnd.ms-powerpoint";
        break;
    case "gif":
        $ctype = "image/gif";
        break;
    case "png":
        $ctype = "image/png";
        break;
    case "jpeg":
    case "jpg":
        $ctype = "image/jpg";
        break;
    default:
        $ctype = "text/html";
}

header("Content-Type: $ctype");
ob_clean();
flush();

echo file_get_contents($save_path);

return true;
