<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	InvestiClub
 * @package		Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Edit profile form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Members
 */
class Form_Members_AddEmail extends Ivc_Form
{
    
    public function init()
    {
        parent::init();
        
        /*
         * Club User id (hidden)
         */
        $memberId = new Zend_Form_Element_Hidden('id');
        $memberId->setRequired(true)
                  ->setDecorators(array('ViewHelper', 'Errors'))
                  ->addValidator('Callback', true, function($e){ return (is_numeric($e));})
                  ->addValidator('greaterThan', true, array('min' => 0))
                  ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        
        /*
         * E-mail
         */
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail')
              ->setRequired(true)
              ->setAttrib('maxlength', '50')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('email', true)
              ->addValidator('emailDenied', true)
              ->addValidator('stringLength', true, array(1, 50))
              ->addDecorators($this->_elementDecorators);
              
              
        $this->addElement($email);
        $this->addElement($memberId);
    }
}
    