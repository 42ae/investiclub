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
 * Join form
 * 
 * Renders a form that contains at least these fields:
 * Name, Creation date, Country, Currency, Club Role and Broker.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Clubs
 */
class Form_Clubs_Join extends Ivc_Form
{

    public function init()
    {
        parent::init();
        
        /*
         * Club Id
         */
        $clubId = new Zend_Form_Element_Hidden('id');
        $clubId->setRequired(false)
                 ->setDecorators(array('ViewHelper', 'Errors'))
                 ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($clubId);
        
        /*
         * Club Autocompletion
         */
        $name = new Ivc_Form_Element_AutocompleteClubNames('search');
        $name->setLabel('Search a club:')
              ->setRequired(true)
              //->setJQueryParam('focus', new Zend_Json_Expr("function( event, ui ) { $( \"#broker\" ).val(ui.item.value); return false;}"))
              //->setJQueryParam('open', new Zend_Json_Expr("function(event, ui) { $(this).autocomplete(\"widget\").css({ \"width\": 550 });}"))
              ->setJQueryParam('select', new Zend_Json_Expr('function( event, ui ) { $( "#name" ).val( ui.item.name ); $( "#id" ).val( ui.item.id ); }'))
              ->addErrorMessage('Please enter the name of your club broker.')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->setAttrib('style', 'width:300px')
              ->addDecorators($this->_formJQueryElements);
        $this->addElement($name);
        
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Search')
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'confirmation')
               ->setAttrib('style', 'font-size:12px')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);
    }
}

