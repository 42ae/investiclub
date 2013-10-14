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
class Form_Members_AddAllContribution extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * Comment
         */
        $comment = new Zend_Form_Element_Text('comment');
        $comment->setLabel('Commentaire')
                  ->setRequired(false)
                  ->setAttrib('maxlength', '45')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array('min' => 1, 'max' => 45))
                  ->addDecorators($this->_elementDecorators);
        $this->addElement($comment);
        
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

    }
    
    public function addMembersFields($members, $contributions, $balanceSheetInfo)
    {
        $fields = array();
        foreach ($contributions as $memberId => $contribution) {
            if ($contribution['status'] == 'unpaid') {
                $memberName = $members->getUserByMemberId($memberId)->first_name . ' ' 
                            . $members->getUserByMemberId($memberId)->last_name;
                ${'field_' . $memberId} = new Zend_Form_Element_Text('contribution_member_' . $memberId);
                ${'field_' . $memberId}->setLabel($memberName)
                  ->setRequired(false)
                  ->setAttrib('required name', 'contribution_member_' . $memberId)
                  ->setAttrib('maxlength', '45')
                  ->setAttrib('size', '10')
                  ->setValue($contribution['value'])
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('stringLength', false, array('min' => 1, 'max' => 45))
                  ->addDecorators($this->_elementDecorators);
               $this->addElement(${'field_' . $memberId});
               array_push($fields, ${'field_' . $memberId});
            }
        }
        $this->addDisplayGroup($fields,
                               'dgFields', array('disableLoadDefaultDecorators' => true));
        $this->setDisplayGroupDecorators(
        array('FormElements'));
        
        /*
         * Contribution date - BILAN COURANT
         */
        $startDate = new Zend_Date($balanceSheetInfo['startDate'], Zend_Date::ISO_8601, 'en_US');
        $endDate = new Zend_Date($balanceSheetInfo['endDate'], Zend_Date::ISO_8601, 'en_US');
        $startYear = $startDate->toString('YYYY');
        $startMonth = $startDate->toString('M');
        $startDay = $startDate->toString('d');
        $endYear = $endDate->toString('YYYY');
        $endMonth = $endDate->toString('M');
        $endDay = $endDate->toString('d');
        
        $contributionDate = new Ivc_Form_Element_DatePicker('contribution_date');
        $contributionDate->setLabel('Contribution Date:')
                     ->setRequired(true)
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
    