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
 * Club creation form
 * 
 * Renders a form that contains at least these fields:
 * Name, Creation date, Country, Currency, Club Role and Broker.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Clubs
 */
class Form_Clubs_Create extends Ivc_Form
{

    public function init()
    {
        parent::init();
        
        /*
         * Club name
         */
        $clubName = new Zend_Form_Element_Text('name');
        $clubName->setLabel('Club name:')
                  ->setRequired(true)
                  ->setAttribs(array('required name' => 'name', 'maxlength' => '100'))
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array(1, 100))
                  ->addErrorMessage("You must enter the name of your club.")
                  ->addDecorators($this->_elementDecorators);
        $this->addElement($clubName);
        
        /*
         * Registration date
         */
        $registration = new Ivc_Form_Element_DatePicker('registration_date');
        $registration->setLabel('Registration Date:')
                     ->setRequired(false)
                     ->setJQueryParam('changeYear', 'true')
                     ->setJQueryParam('changeMonth', 'true')
                     ->setJQueryParam('minDate', "-40Y")
                     ->setJQueryParam('maxDate', "-0Y")
                     ->setJQueryParam('yearRange', "-40:-0")
                     ->setJQueryParam('dateFormat', new Zend_Json_Expr('$.datepicker.ISO_8601'))
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('date', true, array('format' => self::DATE_FORMAT))
                     ->addValidator('dateCompare', false, array(Zend_Date::now()->subYear('40'), Zend_Date::now()))
                     ->addDecorators($this->_formJQueryElements);
        $this->addElement($registration);       
        
        /*
         * Country
         */
        $country = new Ivc_Form_Element_SelectCountry('country');
        $country->setLabel('Country:')
                ->setRequired(true)
                ->addErrorMessage('Please select the country of your club.')
                ->addDecorators($this->_elementDecorators);
        $this->addElement($country);
        
                $decorator = $country->getDecorator('ViewHelper');
        $decorator->setOption('escape', false);
        
        
        /*
         * Currency
         */
        $currency = new Ivc_Form_Element_SelectCurrency('currency');
        $currency->setLabel('Currency:')
                ->setRequired(true)
                ->addErrorMessage('Please select the currency of your club.')
                ->addDecorators($this->_elementDecorators);
        $this->addElement($currency);

        /*
         * Club Role
         */
        $role = new Zend_Form_Element_Select('role');
        $role->setLabel('Your role:')
                ->setRequired(true)
                ->addMultiOption('president', 'President')
                ->addMultiOption('secretary', 'Secretary')
                ->addMultiOption('treasurer', 'Treasurer')
                ->addMultiOption('member', 'Member')
                ->addErrorMessage('Please select your role within the club.')
                ->addDecorators($this->_elementDecorators);
        $this->addElement($role);
        
        /*
         * Broker
         */
        $broker = new Ivc_Form_Element_AutocompleteBroker('broker');        
        $broker->setLabel('Your broker:')
               ->setRequired(true)
               ->setJQueryParam('focus', new Zend_Json_Expr("function( event, ui ) { $( \"#broker\" ).val(ui.item.value); return false;}"))
               ->setJQueryParam('open', new Zend_Json_Expr("function(event, ui) { $(this).autocomplete(\"widget\").css({ \"width\": 550 });}"))
               ->setJQueryParam('select', new Zend_Json_Expr("function( event, ui ) { $( \"#broker\" ).val( ui.item.value ); $( \"#broker_id\" ).val( ui.item.id ); $( \"#broker-desc\" ).html( '<img src=\"/assets/img/sprites/blank.png\" class=\"flag ' + ui.item.country.toLowerCase() + '\" alt=\"' + ui.item.country + '\" />' + ' ' + '<span style=\"font-weight:bold\">' + ui.item.value + '</span>' + '<br />' + '<a href=\"' + ui.item.url + '\" title=\"' + ui.item.value + '\">' + ui.item.url + '</a>' ); $( \"#broker-icon\" ).attr( \"src\", \"/assets/img/brokers/\" + ui.item.icon ); $( \"#broker-icon\" ).css( \"display\", \"block\" ); return false; }"))
               ->addErrorMessage('Please enter the name of your club broker.')
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addDecorators($this->_formJQueryElements);
        $this->addElement($broker);

        /*
         * Broker Id
         */
        // @todo: element for brokers
        $source = $broker->getJQueryParam('source');
        $allowValues = array();
        foreach ($source as $broker) {
            $allowValues[] = $broker['id'];
        }
        $brokerId = new Zend_Form_Element_Hidden('broker_id');
        $brokerId->setRequired(false)
                 ->setDecorators(array('ViewHelper', 'Errors'))
                 ->addFilter('null')
                 ->addValidator('inArray', false, array($allowValues))
                 ->addErrorMessage('An error occurrrrred, please try again.');
        $this->addElement($brokerId);

        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Create my club')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('style', 'font-size:16px')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);
        
        $this->addDisplayGroup(array('name', 'registration_date', 'country', 'currency', 'role', 'broker', 'broker_id'),
                               'dgCreate');
        $this->addDisplayGroup(array('submit'),
                               'dgSubmit');
        $displayGroups = $this->getDisplayGroups();
        foreach ($displayGroups as $key => $value) {
            $displayGroups[$key]->setDecorators($this->_displayGroupsDecorators);
        }
    }
}

