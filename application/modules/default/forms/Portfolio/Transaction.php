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
 * Transaction form
 * 
 * Form used to operate a transaction such as buy, sell and edit
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Portfolio
 */
class Form_Portfolio_Transaction extends Ivc_Form
{

    public function init()
    {
        parent::init();
        
        /*
         * Force warning (hidden)
         */
        $force = new Zend_Form_Element_Hidden('force');
        $force->setRequired(false)
               ->setDecorators(array('ViewHelper', 'Errors'))
               ->setValue("0")
               ->addFilter('boolean')
               ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($force);
        
        /*
         * Stock id (hidden)
         */
        $stockId = new Zend_Form_Element_Hidden('stock_id');
        $stockId->setRequired(false)
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setValue("")
                ->addValidator('Int', true)
                ->addValidator('greaterThan', true, array('min' => 0))
                ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($stockId);
        
        /*
         * Transaction type (hidden)
         */
        $type = new Zend_Form_Element_Hidden('type');
        $type->setRequired(true)
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->addValidator('alpha', true)
                ->addValidator('callback', true, function($v){return $v=="buy"||$v=="sell"||$v=="edit";})
                ->setErrorMessages(array('errorForm' => 'Ann error occurred, please try again.'));
        $this->addElement($type);
        
        /*
         * Symbol
         */
        $symbol = new Ivc_Form_Element_AutocompleteYqlSymbol('symbol');    
        $symbol->setLabel('Nom ou symbole:')
               ->setAttrib('size', '15')
               ->setAttrib('maxlength', '65')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('stringLength', true, array(1, 65))
               ->addDecorators($this->_formJQueryElements);
        $this->addElement($symbol);
        
        /*
         * Currency
         */
        $currency = new Ivc_Form_Element_SelectCurrency('currency');
        $currency->setLabel('Devise:')
                ->setRequired(true)
                ->addErrorMessage('Please select a curency.')
                ->addDecorators($this->_elementDecorators);
        $this->addElement($currency);
        
        /*
         * Transaction date
         */
        $dob = new Ivc_Form_Element_DatePicker('date');
        $dob->setLabel('Date:')
            ->setAttrib('size', '10')
            ->setRequired(true)
            ->setJQueryParam('changeYear', 'true')
            ->setJQueryParam('changeMonth', 'true')
            ->setJQueryParam('minDate', "-40Y")
            ->setJQueryParam('maxDate', "+0Y")
            ->setJQueryParam('yearRange', "-40:+0")
            ->setJQueryParam('dateFormat', new Zend_Json_Expr('$.datepicker.ISO_8601'))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('date', true, array('format' => self::DATE_FORMAT))
            ->addValidator('dateCompare', false, array(Zend_Date::now()->subYear('40'), Zend_Date::now()))
            ->addDecorators($this->_formJQueryElements);
        $this->addElement($dob);

        /*
         * Shares
         */
        $shares = new Zend_Form_Element_Text('shares');
        $shares->setLabel('Nombre:')
               ->setRequired(true)
               ->setAttrib('maxlength', '12')
               ->setAttrib('size', '10')
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
               ->addValidator('stringLength', true, array(1, 12))
               ->addValidator('int', true)
               ->addValidator('greaterThan', false, array('min' => 0))
               ->addDecorators($this->_elementDecorators);
        $this->addElement($shares);
        
        /*
         * Price
         */
        $price = new Zend_Form_Element_Text('price');
        $price->setLabel('Prix:')
              ->setRequired(true)
              ->setAttrib('maxlength', '12')
              ->setAttrib('size', '10')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
              ->addFilter('LocalizedToNormalized')
              ->addValidator('stringLength', true, array(1, 12))
              ->addValidator('float', true, array('locale' => Zend_Registry::get('config')->resources->locale->default))
              ->addValidator('greaterThan', true, array('min' => 0))
              ->addDecorators($this->_elementDecorators);
        $this->addElement($price);
        
        /*
         * Fees
         */
        $fees = new Zend_Form_Element_Text('fees');
        $fees->setLabel('Frais:')
             ->setRequired(true)
             ->setAttrib('maxlength', '12')
             ->setAttrib('size', '10')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('LocalizedToNormalized')
             ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
             ->addValidator('stringLength', true, array(1, 12))
             ->addValidator('float', true, array('locale' => Zend_Registry::get('config')->resources->locale->default))
             ->addValidator('callback', false, function($v){return $v>=0;})
             ->addDecorators($this->_elementDecorators);
        $this->addElement($fees);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Ajouter')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'top-block-button positive add-member-submit')
               ->setAttrib('image', 'add')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);
        
        
        $this->addDisplayGroup(array('symbol'),
                               'dgSymbol');
        $this->addDisplayGroup(array('currency'),
                               'dgCurrency');
        $this->addDisplayGroup(array('date', 'shares', 'price', 'fees'),
                               'dgTransaction');
        $this->addDisplayGroup(array('submit'),
                               'dgSubmit');
        $displayGroups = $this->getDisplayGroups();
        foreach ($displayGroups as $key => $value) {
            $displayGroups[$key]->setDecorators($this->_displayGroupsDecorators);
        }
    }
}

