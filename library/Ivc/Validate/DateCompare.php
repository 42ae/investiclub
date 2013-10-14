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
 * Compares two dates
 *
 * Usage:
 * $element->addValidator(new Ivc_Validate_DateCompare('startdate')); // exact match
 * $element->addValidator(new Ivc_Validate_DateCompare('startdate', 'enddate')); // between dates
 * $element->addValidator(new Ivc_Validate_DateCompare('startdate', '<')); // not later
 * $element->addValidator(new Ivc_Validate_DateCompare('startdate', '>')); // not earlier
 * $element->addValidator(new Ivc_Validate_DateCompare('startdate', true, 'm-d-Y')); // not later
 * and specified element has value in date format m-d-Y
 * 
 * @category	Ivc
 * @package		Ivc_Validate
 */
class Ivc_Validate_DateCompare extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_SAME = 'notSame';
    const MISSING_TOKEN = 'missingToken';
    const NOT_LATER = 'notLater';
    const NOT_EARLIER = 'notEarlier';
    const NOT_BETWEEN = 'notBetween';
    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_SAME => "The date '%token%' does not match the given '%value%'", 
        self::NOT_BETWEEN => "The date '%value%' is not in the valid range", 
        self::NOT_LATER => "The date '%token%' is not later than '%value%'", 
        self::NOT_EARLIER => "The date '%token%' is not earlier than '%value%'", 
        self::MISSING_TOKEN => 'No date was provided to match against');
    /**
     * @var array
     */
    protected $_messageVariables = array(
        'token' => '_tokenString');
    /**
     * Original token against which to validate
     * @var string
     */
    protected $_tokenString;
    protected $_token;
    protected $_compare;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     * @param  mixed $compare
     * @return void
     */
    public function __construct($token = null, $compare = true)
    {
        if (null !== $token) {
            $this->setToken($token);
            $this->setCompare($compare);
        }
    }

    /**
     * Set token against which to compare
     *
     * @param  mixed $token
     * @return Ivc_Validate_DateCompare
     */
    public function setToken($token)
    {
        $this->_tokenString = (string) $token;
        $this->_token = $token;
        return $this;
    }

    /**
     * Retrieve token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Set compare against which to compare
     *
     * @param  mixed $compare
     * @return Ivc_Validate_DateCompare
     */
    public function setCompare($compare)
    {
        $this->_compareString = (string) $compare;
        $this->_compare = $compare;
        return $this;
    }

    /**
     * Retrieve compare
     *
     * @return string
     */
    public function getCompare()
    {
        return $this->_compare;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue((string) $value);
        $token = $this->getToken();
        if ($token === null) {
            $this->_error(self::MISSING_TOKEN);
            return false;
        }
        $date1 = new Zend_Date($value, Ivc_Form::DATE_FORMAT);
        $date2 = new Zend_Date($token, Ivc_Form::DATE_FORMAT);
        if ($this->getCompare() === true) {
            if ($date1->compare($date2) < 0 || $date1->equals($date2)) {
                $this->_error(self::NOT_LATER);
                return false;
            }
        } else 
            if ($this->getCompare() === false) {
                if ($date1->compare($date2) > 0 || $date1->equals($date2)) {
                    $this->_error(self::NOT_EARLIER);
                    return false;
                }
            } else 
                if ($this->getCompare() === null) {
                    if (! $date1->equals($date2)) {
                        $this->_error(self::NOT_SAME);
                        return false;
                    }
                } else {
                    $date3 = new Zend_Date($this->getCompare());
                    if ($date1->compare($date2) < 0 || $date1->compare($date3) > 0) {
                        $this->_error(self::NOT_BETWEEN);
                        return false;
                    }
                }
        return true;
    }
}