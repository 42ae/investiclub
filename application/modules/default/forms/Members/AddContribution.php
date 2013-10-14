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
class Form_Members_AddContribution extends Ivc_Form
{
    protected $_balanceSheetInfo = null;

    public function setBalanceSheetInfo($balanceSheetInfo) 
    {
        $this->_balanceSheetInfo = $balanceSheetInfo;
        return $this;
    }
  
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
        $this->addElement($memberId);

        /*
         * Balance sheet date (hidden)
         */
        $balanceSheetDate = new Zend_Form_Element_Hidden('date');
        $balanceSheetDate->setRequired(true)
                  ->setDecorators(array('ViewHelper', 'Errors'))
                  ->addValidator('date', true, array('format' => self::DATE_FORMAT))
                  ->addValidator('dateCompare', false, array($this->_balanceSheetInfo['startDate'], $this->_balanceSheetInfo['endDate']))
                  ->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($balanceSheetDate);

        /*
         * Amount
         */
        $amount = new Zend_Form_Element_Text('amount');
        $amount->setLabel('Montant :')
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
        $this->addElement($amount);
        
        
        
        /*
         * Comment
         */
        $comment = new Zend_Form_Element_Text('comment');
        $comment->setLabel('Commentaire :')
                  ->setRequired(false)
                  ->setAttrib('maxlength', '45')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array('min' => 1, 'max' => 45))
                  ->addDecorators($this->_elementDecorators);
        $this->addElement($comment);
        
        /*
         * Contribution date - BILAN COURANT - ADD RANGE VALIDATION
         */
        $startDate = new Zend_Date($this->_balanceSheetInfo['startDate'], Zend_Date::ISO_8601, 'en_US');
        $endDate = new Zend_Date($this->_balanceSheetInfo['endDate'], Zend_Date::ISO_8601, 'en_US');
        $startYear = $startDate->toString('YYYY');
        $startMonth = $startDate->toString('M');
        $startDay = $startDate->toString('d');
        $endYear = $endDate->toString('YYYY');
        $endMonth = $endDate->toString('M');
        $endDay = $endDate->toString('d');
        
        $contributionDate = new Ivc_Form_Element_DatePicker('contribution_date');
        $contributionDate->setLabel('Date de cotisation :')
                     ->setRequired(true)
                     ->setAttrib('id', 'contributionDate')
                     ->setJQueryParam('changeYear', 'true')
                     ->setJQueryParam('changeMonth', 'true')
                     ->setJQueryParam('minDate', new Zend_Json_Expr('new Date(' . $startYear . ', ' . ($startMonth - 1) . ', ' . $startDay . ')'))
                     ->setJQueryParam('maxDate', new Zend_Json_Expr('new Date(' . $endYear . ', ' . ($endMonth - 1) . ', ' . $endDay . ')'))
                     ->setJQueryParam('yearRange', $startYear . ":" . $endYear)
                     ->setJQueryParam('dateFormat', new Zend_Json_Expr('$.datepicker.ISO_8601'))
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('date', true, array('format' => self::DATE_FORMAT))
                     ->addValidator('dateCompare', false, array($startDate, $endDate))
                     ->addDecorators($this->_formJQueryElements);
        $this->addElement($contributionDate);

    }
}
    