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
class Form_Portfolio_Reevaluate extends Ivc_Form
{
    public function init()
    {
        parent::init();

        /**
         * Hidden date
         */
        $date = new Zend_Form_Element_Hidden('date');
        $date->setRequired(true)
             ->setDecorators(array('ViewHelper', 'Errors'))
             ->addValidator('date', true, array('format' => self::DATE_FORMAT))
             ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($date);
                
        /*
         * Submit
         */
        $submit = new Zend_Form_Element_Button('submit');
        $submit->setLabel('Réevaluation')
                ->setOrder(100)
               ->setAttrib('type', 'submit')
               ->setAttrib('class', 'top-block-button positive')
               ->setAttrib('image', 'add')
               ->setIgnore(true)
               ->setDecorators($this->_buttonDecorators);
        $this->addElement($submit);

    }
    
    public function addPortfolioStocks($stocks)
    {
        foreach ($stocks as $stockId => $stock) {
            ${'field_' . $stockId} = new Zend_Form_Element_Text('stock_' . $stockId);
            ${'field_' . $stockId}->setLabel($stock['name'])
              ->setRequired(false)
              ->setAttrib('required name', 'stock_' . $stockId)
              ->setAttrib('maxlength', '45')
              ->setAttrib('size', '10')
              ->setValue($stock['lastPrice'])
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('stringLength', false, array('min' => 1, 'max' => 45))
              ->addValidator('float', true, array('locale' => Zend_Registry::get('config')->resources->locale->default))
              ->addValidator('greaterThan', true, array('min' => 0))
              ->addDecorators($this->_elementDecorators);
            $this->addElement(${'field_' . $stockId});
        }
    }

}