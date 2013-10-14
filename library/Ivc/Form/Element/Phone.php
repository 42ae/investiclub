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
 * @package		Ivc_Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Phone number element
 * 
 * Concatenate  two fields as follow:
 * 	- A first input of type "select" with a list of international calling 
 *    codes in it
 *  - A second field input of type "text" where the phone number will be typed 
 *    by the user
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_Phone extends Zend_Form_Element_Xhtml
{
    public $helper = 'formPhone';

    /**
     * Validate element value
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (is_array($value)) {
            $code = $value['code'];
            $value = $value['code'] . '-' . $value['number'];
            if ($value == $code . '-') {
                $value = null;
            }
        }
        return parent::isValid($value, $context);
    }

    /**
     * Retrieve filtered element value
     *
     * @return mixed
     */
    public function getValue()
    {
        if (is_array($this->_value)) {
            $value = $this->_value['code'] . '-' . $this->_value['number'];
            if ($value == '-') {
                $value = null;
            }
            $this->setValue($value);
        }
        return parent::getValue();
    }
}