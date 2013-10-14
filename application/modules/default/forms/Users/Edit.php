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
 * @subpackage	Users
 */
class Form_Users_Edit extends Ivc_Form
{
    protected $_userId;
    
    public function init()
    {
        parent::init();
        
        /*
         * Firstname
         */
        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setLabel('Firstname:')
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
        $lastName->setLabel('Lastname:')
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
        $where = array('users', 'email', array('field' => 'user_id', 'value' => Ivc::getCurrentUserId()));
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail:')
              ->setRequired(true)
              ->setAttrib('required name', 'email')
              ->setAttrib('maxlength', '50')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('email', true)
              ->addValidator('emailDenied', true)
              ->addValidator('stringLength', true, array(1, 50))
              ->addValidator('db_NoRecordExists', true, $where)
              ->addDecorators($this->_elementDecorators);
        $this->addElement($email);
        
        /*
         * Gender
         */
        $gender = new Zend_Form_Element_Select('gender');
        $gender->setLabel('Gender:')
               ->setRequired(false)
               ->addErrorMessage("Select your gender please.")
               ->addMultiOption(null, 'Select...')
               ->addMultiOption('M', 'Male')
               ->addMultiOption('F', 'Female')
               ->addDecorators($this->_elementDecorators);
        $this->addElement($gender);
        
        /*
         * Date of birth
         */
        $dob = new Ivc_Form_Element_DatePicker('date_of_birth');
        $dob->setLabel('Date of birth:')
            ->setRequired(false)
            ->setJQueryParam('changeYear', 'true')
            ->setJQueryParam('changeMonth', 'true')
            ->setJQueryParam('minDate', "-105Y")
            ->setJQueryParam('maxDate', "-5Y")
            ->setJQueryParam('yearRange', "-105:-5")
            ->setJQueryParam('dateFormat', new Zend_Json_Expr('$.datepicker.ISO_8601'))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('date', true, array('format' => self::DATE_FORMAT))
            ->addValidator('dateCompare', false, array(Zend_Date::now()->subYear('105'), Zend_Date::now()->subYear('5')))
            ->addDecorators($this->_formJQueryElements);
        $this->addElement($dob);
            
        /*
         * Occupation
         */
        $occupation = new Zend_Form_Element_Text('occupation');
        $occupation->setLabel('Occupation:')
             ->setRequired(false)
             ->setAttrib('placeholder', 'e.g. Accountant')
             ->setAttrib('maxlength', '45')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('stringLength', false, array('max' => 45))
             ->addErrorMessage("Your occupation must contain less than 45 characters.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($occupation);
        
        /*
         * Address
         */
        $address = new Zend_Form_Element_Text('address');
        $address->setLabel('Address:')
                ->setRequired(false)
                ->setAttrib('maxlength', '100')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('stringLength', false, array('min' => 5, 'max' => 100))
                ->addErrorMessage("Your address must contain 5 to 100 characters.")
                ->addDecorators($this->_elementDecorators);
        $this->addElement($address);

        /*
         * Postal Code
         */
        $postalCode = new Zend_Form_Element_Text('postal_code');
        $postalCode->setLabel('Postal Code:')
                   ->setRequired(false)
                   ->setOptions(array('maxlength' => '10'))
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('stringLength', false, array('max' => 10))
                   ->addErrorMessage("Your postal code must contain less than 10 characters.")
                   ->addDecorators($this->_elementDecorators);
        $this->addElement($postalCode);
        
        /*
         * City
         */
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City:')
             ->setRequired(false)
             ->setOptions(array('maxlength' => '50'))
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('stringLength', false, array('max' => 50))
             ->addErrorMessage("Your city must contain less than 50 characters.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($city);
        
        /*
         * Country
         */
        $country = new Ivc_Form_Element_SelectCountry('country');
        $country->setLabel('Country:')
                ->setRequired(true)
                ->addErrorMessage("Country not in the list.")
                ->addDecorators($this->_elementDecorators);
        $this->addElement($country);
        
        /*
         * Phone (mobile)
         */
        
        $phoneMobile = new Ivc_Form_Element_Phone('phone_mobile');
        $phoneMobile->setRequired(false)
              ->setLabel('Phone (mobile):')
              ->setAttribs(array('codeAttribs'   => array('style' => 'width: 49%; margin-right: 9px'),
                                 'numberAttribs' => array('style' => 'width: 49%', 
                                 						  'placeholder' => 'e.g. 012-3456-789',
                                                          'maxlength'   => '25')))
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('Callback', array('callback' => array($this, 'digits')))
             ->addValidator('PhoneNumber', true, '/^([A-Z]{1,2})-\d{1,14}$/')
             ->addErrorMessage("Incorrect phone number.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($phoneMobile);
        
        /*
         * Phone (home)
         */     
        $phoneHome = new Ivc_Form_Element_Phone('phone_home');
        $phoneHome->setRequired(false)
              ->setLabel('Phone (home):')
              ->setAttribs(array('codeAttribs'   => array('style' => 'width: 49%; margin-right: 9px'),
                                 'numberAttribs' => array('style' => 'width: 49%', 
                                 						  'placeholder' => 'e.g. 012-3456-789',
                                                          'maxlength'   => '25')))
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('Callback', array('callback' => array($this, 'digits')))
             ->addValidator('PhoneNumber', true, '/^([A-Z]{1,2})-\d{1,14}$/')
             ->addErrorMessage("Incorrect phone number.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($phoneHome);
        
//        /*
//         * Current Password
//         */
//        $currentPassword = new Zend_Form_Element_Password('current_password');
//        $currentPassword->setLabel('Current password:')
//                 ->setRequired(false)
//                 ->addValidator('stringLength', false, array('min' => 6))
//                 ->addValidator('fieldNotEmpty', false, array('token' => 'password'))
//                 ->addDecorators($this->_elementDecorators);
//        $this->addElement($currentPassword);
//
//        /*
//         * New password
//         */
//        $password = new Zend_Form_Element_Password('password');
//        $password->setLabel('New password:')
//                 ->setRequired(false)
//                 ->addValidator('stringLength', false, array('min' => 6))
//                 ->addValidator('fieldNotEmpty', false, array('token' => 'current_password'))
//                 ->addDecorators($this->_elementDecorators);
//        $this->addElement($password);
//        
//        /*
//         * New password confirmation
//         */
//        $confirmPassword = new Zend_Form_Element_Password('confirm_password');
//        $confirmPassword->setLabel('Confirm new password:')
//                        ->setRequired(false)
//                        ->addValidator('identical', false, array('token' => 'password'))
//                        ->addDecorators($this->_elementDecorators);
//        $this->addElement($confirmPassword);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Save my profile')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('image', 'success')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);   
        
        /*
         * Token CSRF
         */           
        $token = new Zend_Form_Element_Hash('token');
        $token->setSalt(md5(uniqid(rand(), true)))
              ->setRequired(false)
              ->setTimeout('120')
              ->setErrorMessages(array('errorForm' => 'Pour des raisons de securités.... [lien]'))
              ->setDecorators(array('ViewHelper', 'Errors'));
        $this->addElement($token);

       /*
        * Display Groups : General Information - Contact Information - Change Password
        */
        $this->addDisplayGroup(array('first_name', 'last_name', 'email', 'gender', 'date_of_birth', 'occupation'),
                               'dgGeneralInformation');
        $this->addDisplayGroup(array('address', 'postal_code', 'city', 'country'),
                               'dgContactInformation');
        $this->addDisplayGroup(array('phone_mobile', 'phone_home'),
        					   'dgPhone');
        //$this->addDisplayGroup(array('current_password', 'password', 'confirm_password'),
       // 					   'dgChangePassword'); // password display group
        $this->addDisplayGroup(array('submit'),
        					   'dgSubmit');
        $displayGroups = $this->getDisplayGroups();
        foreach ($displayGroups as $key => $value) {
            $displayGroups[$key]->setDecorators($this->_displayGroupsDecorators);
        }
    }
    
    /**
     * Retrieve all form element values
     *
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getValues($suppressArrayNotation = false)
    {
        $values = parent::getValues($suppressArrayNotation);
        
        if (array_key_exists('phone_home_code', $values) AND array_key_exists('phone_home', $values)) {    
            $values['phone_home'] = $values['phone_home_code'] . $values['phone_home'];
        }
        if (array_key_exists('phone_mobile_code', $values) AND array_key_exists('phone_mobile', $values)) {
            $values['phone_mobile'] = $values['phone_mobile_code'] . $values['phone_mobile'];
        }

        unset($values['phone_home_code']);
        unset($values['phone_mobile_code']);
        
        return $values;
    }

    /**
     * 
     * Returns the string $value, removing everything except country code 
     * and digit characters separated by a hyphens.
     *
     * @param  string $value
     * @return string|null
     */
    public function digits($value)
    {
        if (null === $value OR false === strpos($value, '-'))
         return null;
         
        if (!(@preg_match('/\pL/u', 'a')) ? true : false) {
            // POSIX named classes are not supported, use alternative 0-9 match
            $pattern = '/[^0-9]/';
        } else if (extension_loaded('mbstring')) {
            // Filter for the value with mbstring
            $pattern = '/[^[:digit:]]/';
        } else {
            // Filter for the value without mbstring
            $pattern = '/[\p{^N}]/';
        }

        list($code, $number) = explode('-', $value, 2);
        $number = preg_replace($pattern, '', (string) $number);
        return $code . '-' . $number;
    }
}
    