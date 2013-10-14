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
 * This class extends {@Zend_Validate_EmailAddress} and overload the getMessage
 * method to return only one error message.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Validate
 */
class Ivc_Validate_Email extends Zend_Validate_EmailAddress
{
    public function getMessages()
    {
        return array('invalidEmail' => 'Please include a valid email address.');
    }
}