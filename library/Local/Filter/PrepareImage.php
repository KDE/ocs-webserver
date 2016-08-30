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

class Local_Filter_PrepareImage implements Zend_Filter_Interface
{

    public function filter($value)
    {
        #Zend_Debug::dump($value);


        #die();
        $image = new Imagick();
        $image->readImage($value);

        $pathVals = explode("/", $value);

        $savePath = substr($value, 0, strrpos($value, "/") + 1);
        $origFileName = substr($pathVals[count($pathVals) - 1], 0, strrpos($pathVals[count($pathVals) - 1], "."));
        $goodFileName = $this->cleanFileName(substr($pathVals[count($pathVals) - 1], 0, strrpos($pathVals[count($pathVals) - 1], ".")));

        $normalFileName = $savePath . $goodFileName . ".jpg";

        $bgImg = new Imagick();
        $bgImgPrint = new Imagick();

        $origDims = $image->getImageGeometry();

        $targetWidth = 735;
        $targetHeight = 520;

        if ($origDims['width'] > $targetWidth) {
            $newWidth = $targetWidth;
            $newHeight = (($newWidth * $origDims['height']) / $origDims['width']);


            if ($newHeight > $targetHeight) {
                $newHeight = $targetHeight;
                $newWidth = (($newHeight * $origDims['width']) / $origDims['height']);
                $image->resizeImage($newWidth, $newHeight, null, null);
                #$image->cropImage($newWidth,$newHeight,0,0);
            } else {
                $image->resizeImage($newWidth, $newHeight, null, null);
            }


        } elseif ($origDims['height'] > $targetHeight) {
            $newHeight = $targetHeight;
            $newWidth = (($newHeight * $origDims['width']) / $origDims['height']);

            $image->resizeImage($newWidth, $newHeight, null, null);
        }

        $image->writeImage($normalFileName);

        #$emptyImg = new Imagick();
        #$emptyImg->newImage(129,77,"none");
        #$emptyImg->setImageFormat('png');

        $thumbImg = $image;


        $thumbWidth = 292;
        $thumbHeight = (($thumbWidth * $origDims['height']) / $origDims['width']);
        if ($thumbHeight < 219) {
            $thumbHeight = 219;
            $thumbWidth = (($thumbHeight * $origDims['width']) / $origDims['height']);
            $thumbImg->resizeImage($thumbWidth, $thumbHeight, null, null);
            $thumbImg->cropImage(292, 219, 0, 0);
        } else {
            $thumbImg->resizeImage($thumbWidth, $thumbHeight, null, null);
            $thumbImg->cropImage($thumbWidth, 219, 0, 0);
        }

        //$thumbImg->setImageFormat('jpg');

        #$picOverlay = new Imagick();
        #$picOverlay->readImage($_SERVER['DOCUMENT_ROOT']."/images/pic_over.png");

        #$emptyImg->compositeImage($thumbImg,imagick::COMPOSITE_DEFAULT,9,9);
        #$emptyImg->compositeImage($picOverlay,imagick::COMPOSITE_DEFAULT,0,0);
        /*


                $image->roundCorners(10,10);

                $bgImg->newImage($sideLength,$sideLength, new ImagickPixel('#e8e8e8'));
                $bgImgPrint->newImage($sideLength,$sideLength, new ImagickPixel('#ffffff'));

                $bgImg->setImageFormat('jpg');
                $bgImgPrint->setImageFormat('jpg');

                $bgImg->compositeImage($image,imagick::COMPOSITE_DEFAULT,0,0);
                $bgImgPrint->compositeImage($image,imagick::COMPOSITE_DEFAULT,0,0);
        */


#		$fileName = substr($pathVals[count($pathVals)-1],0,strrpos($pathVals[count($pathVals)-1],".")).".jpg";
        $fileNameThumb = "thumb_" . $goodFileName . ".jpg";

        $thumbImg->writeImage(substr($value, 0, strrpos($value, "/")) . "/" . $fileNameThumb);


        $thumbImg->destroy();
        #$picOverlay->destroy();
        #$emptyImg->destroy();

        if ($origFileName != $goodFileName) {
            unlink($value);
        }


        return $normalFileName;
    }

    private function cleanFileName($fileName)
    {
        $newFileName = strtr($fileName, " ", "_");

        return $newFileName;
    }

    private function getWidth($width, $height)
    {
        return ($width * 209) / $height;
    }

    private function getHeight($width, $height)
    {
        return ($height * 209) / $width;
    }

} 