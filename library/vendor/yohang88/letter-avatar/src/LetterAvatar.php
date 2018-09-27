<?php

namespace YoHang88\LetterAvatar;

use Intervention\Image\Gd\Font;
use Intervention\Image\Gd\Shapes\CircleShape;
use Intervention\Image\ImageManager;

class LetterAvatar
{
    /**
     * Image Type PNG
     */
    const MIME_TYPE_PNG = 'image/png';

    /**
     * Image Type JPEG
     */
    const MIME_TYPE_JPEG = 'image/jpeg';

    /**
     * @var string
     */
    private $name;


    /**
     * @var string
     */
    private $nameInitials;


    /**
     * @var string
     */
    private $shape;


    /**
     * @var int
     */
    private $size;

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * LetterAvatar constructor.
     * @param string $name
     * @param string $shape
     * @param int    $size
     */
    public function __construct( $name,  $shape ,  $size )
    {
        $this->setName($name);
        $this->setImageManager(new ImageManager());
        $this->setShape($shape);
        $this->setSize($size);
    }

    /**
     * @param string $name
     */
    private function setName( $name)
    {
        $this->name = $name;
    }


    /**
     * @param ImageManager $imageManager
     */
    private function setImageManager( $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @param string $shape
     */
    private function setShape( $shape)
    {
        $this->shape = $shape;
    }


    /**
     * @param int $size
     */
    private function setSize( $size)
    {
        $this->size = $size;
    }


    /**
     * @return \Intervention\Image\Image
     */
    private function generate()
    //private function generate(): \Intervention\Image\Image
    {
        $isCircle = $this->shape === 'circle';

        $this->nameInitials = $this->getInitials($this->name);
        $color = $this->stringToColor($this->name);

        $canvas = $this->imageManager->canvas(480, 480, $isCircle ? null : $color);

        if ($isCircle) {
            $canvas->circle(480, 240, 240, function (CircleShape $draw) use ($color) {
                $draw->background($color);
            });

        }

        $canvas->text($this->nameInitials, 240, 240, function (Font $font) {
            $font->file(__DIR__ . '/fonts/arial-bold.ttf');
            $font->size(220);
            $font->color('#fafafa');
            $font->valign('middle');
            $font->align('center');
        });

        return $canvas->resize($this->size, $this->size);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getInitials( $name)
    //private function getInitials(string $name): string
    {
        $nameParts = $this->break_name($name);

        if(!$nameParts) {
            return '';
        }

        $secondLetter = $nameParts[1] ? $this->getFirstLetter($nameParts[1]) : '';

        return $this->getFirstLetter($nameParts[0]) . $secondLetter;

    }

    /**
     * @param string $word
     * @return string
     */
    //private function getFirstLetter_(string $word): string
    private function getFirstLetter_( $word)
    {
        return mb_strtoupper(trim(mb_substr($word, 0, 1, 'UTF-8')));
    }

    /**
     * @param string $word
     * @return string
     */
    // private function getFirstLetter(string $word): string
    private function getFirstLetter( $word)   
    {
        return trim(mb_substr($word, 0, 1, 'UTF-8'));
    }

    /**
     * Save the generated Letter-Avatar as a file
     *
     * @param        $path
     * @param string $mimetype
     * @param int    $quality
     * @return bool
     */
    // public function saveAs($path, $mimetype = 'image/png', $quality = 90): bool
    public function saveAs($path, $mimetype = 'image/png', $quality = 90)
    {
        $allowedMimeTypes = [
            'image/png',
            'image/jpeg'
        ];

        if (empty($path) || empty($mimetype) || !\in_array($mimetype, $allowedMimeTypes, true)) {
            return false;
        }

        return \is_int(@file_put_contents($path, $this->generate()->encode($mimetype, $quality)));
    }

    /**
     * @return string
     */
    // public function __toString(): string
    public function __toString()
    {
        return (string)$this->generate()->encode('data-url');
    }

    /**
     * Explodes Name into an array.
     * The function will check if a part is , or blank
     *
     * @param string $name Name to be broken up
     * @return array Name broken up to an array
     */
    // private function break_name(string $name): array
    private function break_name( $name)
    {
        $words = \explode(' ', $name);
        $words = array_filter($words, function($word) {
            return $word!=='' && $word !== ',';
        });
        return array_values($words);
    }

    /**
     * @param string $string
     * @return string
     */
    // private function stringToColor(string $string): string
    private function stringToColor( $string)
    {
        // random color
        $rgb = substr(dechex(crc32($string)), 0, 6);
        // make it darker
        // $darker = 1;
        // list($R16, $G16, $B16) = str_split($rgb, 2);
        // $R = sprintf('%02X', floor(hexdec($R16) / $darker));        
        // $G = sprintf('%02X', floor(hexdec($G16) / $darker));
        // $B = sprintf('%02X', floor(hexdec($B16) / $darker));
        // return '#' . $R . $G . $B;

        // ======== nr3 ==============
        $darker = 2;
        list($R16, $G16, $B16) = str_split($rgb, 2);
        $R = sprintf('%02X', floor(hexdec($R16)));
        $G = sprintf('%02X', floor(hexdec($G16) / $darker));
        $B = sprintf('%02X', floor(hexdec($B16) / $darker));
        return '#' . $R . $G . $B;

        // // ========nr1================
        // $darker = 2;
        // list($R16, $G16, $B16) = str_split($rgb, 2);
        // $R = sprintf('%02X', floor(hexdec($R16) / $darker));
        // $G = sprintf('%02X', floor(hexdec($G16) / $darker));
        // $B = sprintf('%02X', floor(hexdec($B16) / $darker));
        // return '#' . $R . $G . $B;
    }

}
