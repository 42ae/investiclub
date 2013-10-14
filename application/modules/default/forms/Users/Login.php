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
 * Log In form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Portfolio
 */
class Form_Users_Login extends Ivc_Form
{

    public function init()
    {
        parent::init();  
        
        /*
         * E-mail (from the Signup form without the DbExist validator)
         */
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail:')
              ->setRequired(true)
              ->setAttrib('required name', 'email')
              ->setAttrib('placeholder', $this->getDefaultTranslator()->_('FORM_PLACEHOLDER_EMAIL'))
              ->setAttrib('maxlength', '50')
              ->setAttrib('class', 'login-inputs')
              ->addErrorMessage('Please enter a valid email address.')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('EmailAddress', true)
              ->addValidator('EmailDenied', true)
              ->addValidator('stringLength', false, array(5, 50))
              ->setDecorators(array('ViewHelper'));
        $this->addElement($email);
        
        /*
         * Password (from the Singup form)
         */
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                 ->setRequired(true)
                 ->setAttrib('required name', 'password')
                 ->setAttrib('placeholder', $this->getDefaultTranslator()->_('FORM_PLACEHOLDER_PASSWORD'))
                 ->setAttrib('maxlength', '50')
                 ->setAttrib('class', 'login-inputs')
                 ->addValidator('stringLength', false, array(6, 128))
                 ->addErrorMessage('Your password must be at least 6 characters long.')
                 ->setDecorators(array('ViewHelper'));
        $this->addElement($password);
        
        /*
         * Remember Me
         */
        $rememberMe = new Zend_Form_Element_Checkbox('remember_me');
        $rememberMe->setLabel('REMEMBER_ME')
                   ->setRequired(false)
                   ->setDecorators(array('ViewHelper', 'Label', array('HtmlTag', array('tag' => 'div', 'id' => 'rememberMe'))));
        $this->addElement($rememberMe);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('LOGIN')
               ->setDecorators(array('ViewHelper'))
               ->setIgnore(true);
               $this->addElement($submit);        
    }
}
