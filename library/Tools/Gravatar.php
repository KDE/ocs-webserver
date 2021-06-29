<?php
/** @noinspection PhpUnused */

namespace Library\Tools;

/**
 *  Class Gravatar
 *
 * From Gravatar Help:
 *        "A gravatar is a dynamic image resource that is requested from our server. The request
 *        URL is presented here, broken into its segments."
 * Source:
 *    http://site.gravatar.com/site/implement
 *
 * Usage:
 * <code>
 *        $email = "youremail@yourhost.com";
 *        $default = "http://www.yourhost.com/default_image.jpg";    // Optional
 *        $gravatar = new Gravatar($email, $default);
 *        $gravatar->size = 80;
 *        $gravatar->rating = "G";
 *        $gravatar->border = "FF0000";
 *
 *        echo $gravatar; // Or echo $gravatar->toHTML();
 * </code>
 *
 *    Class Page: http://www.phpclasses.org/browse/package/4227.html
 *
 * @author  Lucas Araújo <araujo.lucas@gmail.com>
 * @version 1.0
 * @package Gravatar
 */
class Gravatar
{
    /**
     *    Gravatar's url
     */
    const GRAVATAR_URL = "http://www.gravatar.com/avatar.php";
    /**
     *    Query string. key/value
     */
    protected $_properties = array(
        "gravatar_id" => null,
        "default"     => null,
        "size"        => 80, // The default value
        "rating"      => null,
        "border"      => null,
    );
    /**
     *    E-mail. This will be converted to md5($email)
     */
    protected $email = "";
    /**
     *    Extra attributes to the IMG tag like ALT, CLASS, STYLE...
     */
    protected $extra = "";
    /**
     *    Ratings available
     */
    private $GRAVATAR_RATING = array("G", "PG", "R", "X");

    /**
     * @param null $email
     * @param null $default
     */
    public function __construct($email = null, $default = null)
    {
        $this->setEmail($email);
        $this->setDefault($default);
    }

    /**
     * @param $email
     *
     * @return bool
     */
    public function setEmail($email)
    {
        if ($this->isValidEmail($email)) {
            $this->email = $email;
            $this->_properties['gravatar_id'] = md5(strtolower($this->email));

            return true;
        }

        return false;
    }

    /**
     * @param $email
     *
     * @return int
     */
    public function isValidEmail($email)
    {
        // Source: http://www.zend.com/zend/spotlight/ev12apr.php
        return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
    }

    /**
     * @param $default
     */
    public function setDefault($default)
    {
        $this->_properties['default'] = $default;
    }

    /**
     * @param $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     *    Object property overloading
     *
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        return @$this->_properties[$var];
    }

    /**
     * Object property overloading
     *
     * @param $var
     * @param $value
     *
     * @return bool|void
     */
    public function __set($var, $value)
    {
        switch ($var) {
            case "email":
                return $this->setEmail($value);
            case "rating":
                return $this->setRating($value);
            case "default":
                return $this->setDefault($value);
            case "size":
                return $this->setSize($value);
            // Cannot set gravatar_id
            case "gravatar_id":
                return;
        }

        return @$this->_properties[$var] = $value;
    }

    /**
     * @param $rating
     *
     * @return bool
     */
    public function setRating($rating)
    {
        if (in_array($rating, $this->GRAVATAR_RATING)) {
            $this->_properties['rating'] = $rating;

            return true;
        }

        return false;
    }

    /**
     * @param $size
     */
    public function setSize($size)
    {
        $size = (int)$size;
        if ($size <= 0) {
            $size = null;
        } // Use the default size
        $this->_properties['size'] = $size;
    }

    /**
     *    Object property overloading
     *
     * @param $var
     *
     * @return bool
     */
    public function __isset($var)
    {
        return isset($this->_properties[$var]);
    }

    /**
     *    Object property overloading
     *
     * @param $var
     *
     * @return bool
     */
    public function __unset($var)
    {
        return @$this->_properties[$var] == null;
    }

    /**
     *    toString
     */
    public function __toString()
    {
        return $this->toHTML();
    }

    /**
     *    toHTML
     */
    public function toHTML()
    {
        return '<img src="' . $this->getSrc() . '"' . (!isset($this->size) ? "" : ' width="' . $this->size . '" height="' . $this->size . '"') . $this->extra . ' />';
    }

    /**
     *    Get source
     */
    public function getSrc()
    {
        $url = self::GRAVATAR_URL . "?";
        $first = true;
        foreach ($this->_properties as $key => $value) {
            if (isset($value)) {
                if (!$first) {
                    $url .= "&";
                }
                $url .= $key . "=" . urlencode($value);
                $first = false;
            }
        }

        return $url;
    }
}