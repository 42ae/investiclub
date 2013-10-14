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
 * Sign Up form
 * 
 * Renders a signup form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Portfolio
 */
class Form_Users_Signup extends Ivc_Form
{

    public function init()
    {
        parent::init();
    
        /*
         * Locale
         */
        $currentLocale = Zend_Registry::get('Zend_Locale');
        $locale = new Zend_Form_Element_Hidden('locale');
        $locale->setRequired(true)
               ->setDecorators(array('ViewHelper', 'Errors'))
               ->setValue($currentLocale->toString())
               ->addValidator('Identical', false, $currentLocale->toString())
               ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($locale);

        /*
         * Firstname
         */
        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setLabel('Firstname:')
                  ->setRequired(true)
                  ->setAttrib('required name', 'first_name')
                  ->setAttrib('maxlenght', '45')
                  ->setAttrib('class', 'signup-inputs')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array(1, 45))
                  ->addErrorMessage("You must enter your firstname.")
                  ->addDecorators($this->_elementDecorators);
        $this->addElement($firstName);
        
        /*
         * Lastname
         */
        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setLabel('Lastname:')
                 ->setAttrib('required name', 'first_name')
                 ->setAttrib('maxlenght', '45')
                 ->setAttrib('class', 'signup-inputs')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('stringLength', false, array(1, 45))
                 ->addErrorMessage("You must enter your lastname.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($lastName);
        
        /*
         * E-mail
         */
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail:')
              ->setRequired(true)
              ->setAttrib('required name', 'first_name')
              ->setAttrib('maxlenght', '50')
              ->setAttrib('class', 'signup-inputs')
              ->addErrorMessage('Please enter a valid email address.')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('EmailAddress', true)
              ->addValidator('EmailDenied', true)
              ->addValidator('stringLength', true, array(5, 50))
              ->addValidator('Db_NoRecordExists', true, array('users', 'email'))
              ->addDecorators($this->_elementDecorators);
        $this->addElement($email);
        
        /*
         * Password
         */
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                 ->setRequired(true)
                 ->setAttrib('required name', 'password')
                 ->setAttrib('class', 'signup-inputs')
                 ->addValidator('stringLength', false, array(6))
                 ->addErrorMessage('Your password must be at least 6 characters long.')
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($password);
        
        /*
         * Password confirmation
         */
        $confirmPassword = new Zend_Form_Element_Password('confirm_password');
        $confirmPassword->setLabel('Confirm your password:')
                        ->setRequired(true)
                        ->setAttrib('required name', 'confirm_password')
                        ->setAttrib('class', 'signup-inputs')
                        ->addValidator('identical', false, array('token' => 'password'))
                        ->addErrorMessage('Please enter the same password as above.')
                        ->addDecorators($this->_elementDecorators);
        $this->addElement($confirmPassword);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Sign Up')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('style', 'font-size:16px')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);
    }
}

