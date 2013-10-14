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
class Form_Users_Settings extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * Language (use as locale)
         */
        $locale = new Zend_Form_Element_Select('locale');
        $locale->addMultiOption('en_GB', 'English (UK)')
               ->addMultiOption('en_US', 'English (US)')
               ->addMultiOption('fr_FR', 'Français (France)')
               ->setLabel('Language:')
               ->setRequired(true)
               ->setAttrib('required locale', 'locale')
               ->addErrorMessage("Your language can't be empty.")
               ->addDecorators($this->_elementDecorators);
        $this->addElement($locale);
        
        /*
         * Currency
         */
        $currency = new Ivc_Form_Element_SelectCurrency('currency');
        $currency->setLabel('Currency:')
                 ->setAttrib('required name', 'currency')
                 ->setRequired(true)
                 ->addErrorMessage("Your currency can't be empty.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($currency);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Save my settings')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('image', 'success')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);   
    }
}