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
class Form_Members_DeleteContribution extends Ivc_Form
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
                  ->addValidator('dateCompare', false, array($this->_balanceSheetInfo['treasuryName'], $this->_balanceSheetInfo['treasuryName']));
                  //->setErrorMessages(array('errorForm' => 'An error occurred, please try again.'));
        $this->addElement($balanceSheetDate);

        
        
    }
}
    