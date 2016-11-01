<?php
namespace Cgsmith\Form\Element;

/**
 * Class Recaptcha
 * Renders a div for google recaptcha to allow for use of the Google Recaptcha API
 *
 * @package Cgsmith
 * @license MIT
 * @author  Chris Smith
 */
class Recaptcha extends \Zend_Form_Element
{
    /** @var string specify formRecaptcha helper */
    public $helper = 'formRecaptcha';

    /** @var string siteKey for Google Recaptcha */
    protected $_siteKey = '';

    /** @var string secretKey for Google Recaptcha */
    protected $_secretKey = '';

    /**
     * Constructor for element and adds validator
     *
     * @param array|string|Zend_Config $spec
     * @param null $options
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function __construct($spec, $options = null)
    {
        if (empty($options['siteKey']) || empty($options['secretKey'])) {
            throw new \Zend_Exception('Site key and secret key must be specified.');
        }
        $this->_siteKey = trim($options['siteKey']); // trim the white space if there is any just to be sure
        $this->_secretKey = trim($options['secretKey']); // trim the white space if there is any just to be sure
        $this->addValidator('Recaptcha', false, array('secretKey' => $this->_secretKey));
        $this->setAllowEmpty(false);
        parent::__construct($spec, $options);
    }
}
