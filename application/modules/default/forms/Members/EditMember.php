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
 * Edit member form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Members
 */
class Form_Members_EditMember extends Ivc_Form
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
         * Enrollement date
         */
        $enrollement = new Ivc_Form_Element_DatePicker('enrollement_date');
        $enrollement->setLabel('Date d\'enregistrement :')
                     ->setRequired(true)
                     ->setJQueryParam('changeYear', 'true')
                     ->setJQueryParam('changeMonth', 'true')
                     ->setJQueryParam('minDate', "-40Y")
                     ->setJQueryParam('maxDate', "-0Y")
                     ->setJQueryParam('yearRange', "-40:-0")
                     ->setJQueryParam('dateFormat', new Zend_Json_Expr('$.datepicker.ISO_8601'))
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('regex', true,  array('pattern' => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/'))
                     ->addValidator('date', true, array('format' => self::DATE_FORMAT))
                     ->addValidator('dateCompare', false, array(Zend_Date::now()->subYear('40'), Zend_Date::now()))
                     ->addDecorators($this->_formJQueryElements);
                     
        $role = new Ivc_Form_Element_SelectRole('role');
        $role->setLabel('Votre rôle:')
                ->setRequired(true)
                ->addErrorMessage('Please select a role.')
                ->addDecorators($this->_elementDecorators);

        $this->addElement($enrollement);
        $this->addElement($role);
        $this->addElement($memberId);
    }
    
    /**
     * Element to add to the form according to the member type (active, 
     * pending, unregistered)
     */
    public function addMemberRelatedElements($memberId)
    {
        $membersManager = new Model_Members_Members();
        $member = $membersManager->getMemberById($memberId);
        
        if (null == $member) {
            throw new Ivc_Exception(Ivc_Exception::ERROR_OCCURRED, Zend_Log::ERR);
        }
        
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

        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setLabel('Lastname')
                 ->setAttrib('required name', 'last_name')
                 ->setAttrib('maxlength', '45')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('stringLength', false, array('max' => 45))
                 ->addErrorMessage("Your lastname can't be empty.")
                 ->addDecorators($this->_elementDecorators);

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail')
              ->setRequired(true)
              ->setAttrib('maxlength', '50')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('email')
              ->addValidator('emailDenied')
              ->addValidator('stringLength', false, array(1, 50))
              ->addDecorators($this->_elementDecorators);

        if ($member->isPending()) {
            $this->addElement($email->setValue($member->getUser()->email));
        } elseif ($member->isUnregistered()) {
            $this->addElement($firstName->setValue($member->getUser()->first_name));
            $this->addElement($lastName->setValue($member->getUser()->last_name));
        }
        
        $this->getElement('id')->setValue($member->member_id);
        $this->getElement('enrollement_date')->setValue($member->enrollement_date);
        $this->getElement('role')->setValue($member->role);
    }
}
    