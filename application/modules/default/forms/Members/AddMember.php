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
class Form_Members_AddMember extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * Firstname
         */
        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setLabel('Firstname')
                  ->setRequired(true)
                  ->setAttrib('required name', 'first_name')
                  ->setAttrib('maxlength', '45')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array('min' => 1, 'max' => 45))
                  ->addErrorMessage("Your firstname can't be empty.")
                  ->addDecorators($this->_elementDecorators);
        $this->addElement($firstName);
        
        /*
         * Lastname
         */
        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setLabel('Lastname')
                 ->setAttrib('required name', 'last_name')
                 ->setAttrib('maxlength', '45')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('stringLength', true, array('max' => 45))
                 ->addErrorMessage("Your lastname can't be empty.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($lastName);
        
        /*
         * E-mail
         */
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail')
              ->setRequired(false)
              ->setAttrib('maxlength', '50')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('email', true)
              ->addValidator('emailDenied', true)
              ->addValidator('stringLength', true, array(1, 50))
              ->addDecorators($this->_elementDecorators);
        $this->addElement($email);
        
        /*
         * Role
         */   
        $role = new Ivc_Form_Element_SelectRole('role');
        $role->setLabel('Role')
                ->setRequired(true)
                ->addErrorMessage('Please select a role.')
                ->addDecorators($this->_elementDecorators);
        $this->addElement($role);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Add')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'top-block-button positive add-member-submit')
               ->setAttrib('image', 'add')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);

        /*
         * Cancel
         */
        $cancel = new Zend_Form_Element_Button('cancel');
        $cancel->setLabel('Cancel')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'top-block-button negative')
               ->setAttrib('image', 'cancel')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($cancel);

    }
}
    