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
 * Edit club settings form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Clubs
 */
class Form_Clubs_Information extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * Name
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
        
//        
//        /*
//         * Broker
//         */
//        $broker = new Ivc_Form_Element_AutocompleteBroker('broker');        
//        $broker->setLabel('Your broker:')
//               ->setRequired(true)
//               ->setJQueryParam('focus', new Zend_Json_Expr("function( event, ui ) { $( \"#broker\" ).val(ui.item.value); return false;}"))
//               ->setJQueryParam('open', new Zend_Json_Expr("function(event, ui) { $(this).autocomplete(\"widget\").css({ \"width\": 550 });}"))
//               ->setJQueryParam('select', new Zend_Json_Expr("function( event, ui ) { $( \"#broker\" ).val( ui.item.value ); $( \"#broker_id\" ).val( ui.item.id ); $( \"#broker-desc\" ).html( '<img src=\"/assets/img/sprites/blank.png\" class=\"flag ' + ui.item.country.toLowerCase() + '\" alt=\"' + ui.item.country + '\" />' + ' ' + '<span style=\"font-weight:bold\">' + ui.item.value + '</span>' + '<br />' + '<a href=\"' + ui.item.url + '\" title=\"' + ui.item.value + '\">' + ui.item.url + '</a>' ); $( \"#broker-icon\" ).attr( \"src\", \"/assets/img/brokers/\" + ui.item.icon ); $( \"#broker-icon\" ).css( \"display\", \"block\" ); return false; }"))
//               ->addErrorMessage('Please enter the name of your club broker.')
//               ->addFilter('StripTags')
//               ->addFilter('StringTrim')
//               ->addDecorators($this->_formJQueryElements);
//        $this->addElement($broker);
        
        /*
         * Registration Date
         */
        $registration = new Ivc_Form_Element_DatePicker('registration_date');
        $registration->setLabel('Date of birth:')
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
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Save my club information')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('image', 'success')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);
    }
}