<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Validate
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * 
 * Check whether if a hostname is denied or not.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Validate
 */
class Ivc_Validate_EmailDenied extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_YOPMAIL = 'notYopmail';
    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_YOPMAIL => "'%value%' is not a valid hostname.");

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
        $host = strstr($value, '@');
        if (false !== $host) {
            if (substr_count($host, 'yopmail.com')) {
                $this->_error(self::NOT_YOPMAIL);
                return false;
            }
        }
        return true;
    }
}