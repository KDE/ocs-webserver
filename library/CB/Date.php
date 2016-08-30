<?php

/**
 * Description of Date
 *
 * @author local
 */
class CB_Date extends Zend_Date
{
    public function difference(Zend_Date $arg)
    {
        $diff = $this->sub($arg);
        
        return $diff->getUnixTimestamp();
        
    }
}