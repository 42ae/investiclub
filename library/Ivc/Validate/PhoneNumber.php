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
 * The PhoneNumber validator checks if a phone number contains a valid
 * international country code and a valid number both separated by an
 * hyphen. In order to process the validation, a pattern must be sent to
 * the constructor.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Validate
 */
class Ivc_Validate_PhoneNumber extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const INVALID   = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';
    const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_MATCH => "'%value%' does not match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    );
    
    /**
     * @var array
     */
    protected $_messageVariables = array(
        'pattern' => '_pattern'
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern;

    /**
     * Sets validator options
     *
     * @param  string|Zend_Config $pattern
     * @throws Zend_Validate_Exception On missing 'pattern' parameter
     * @return void
     */
    public function __construct($pattern)
    {
        if ($pattern instanceof Zend_Config) {
            $pattern = $pattern->toArray();
        }

        if (is_array($pattern)) {
            if (array_key_exists('pattern', $pattern)) {
                $pattern = $pattern['pattern'];
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'pattern'");
            }
        }

        $this->setPattern($pattern);
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @throws Zend_Validate_Exception if there is a fatal error in pattern matching
     * @return Zend_Validate_Regex Provides a fluent interface
     */
    public function setPattern($pattern)
    {
        $this->_pattern = (string) $pattern;
        $status         = @preg_match($this->_pattern, "Test");

        if (false === $status) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Internal error while using the pattern '$this->_pattern'");
        }

        return $this;
    }
    
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     * and the first part of value (excluding the hyphen character) is a valid
     * country code.
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) AND !is_int($value) AND !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        $this->_setValue($value);
        
        $locale = Zend_Registry::get('Zend_Locale');
        $countryCode = $territory = Zend_Locale::getTranslationList('Territory', $locale, 2);
        
        $matches = array();
        $status = @preg_match($this->_pattern, $value, $matches);
        
        if (false === $status) {
            $this->_error(self::ERROROUS);
            return false;
        }
        
        if (!$status and !array_key_exists($status[1], $countryCode)) {
            $this->_error(self::NOT_MATCH);
            return false;
        }
        
        return true;
    }
}
