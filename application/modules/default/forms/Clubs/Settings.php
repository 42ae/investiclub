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
class Form_Clubs_Settings extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * Min members
         */
        $minMembers = new Zend_Form_Element_Text('min_members');
        $minMembers->setLabel('Minimum number of members:')
                   ->setRequired(true)
                   ->setAttrib('required name', 'min_members')
                   ->setAttrib('maxlength', '3')
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
                   ->addValidator('int', false)
                   ->addValidator('greaterThan', false, array('min' => 0))
                   ->addErrorMessage("Minimum number of members can't be empty.")
                   ->addDecorators($this->_elementDecorators);
        $this->addElement($minMembers);


        
        
        
        
        /*
         * Max members
         */
        $maxMembers = new Zend_Form_Element_Text('max_members');
        $maxMembers->setLabel('Maximum number of members:')
                   ->setRequired(true)
                   ->setAttrib('required name', 'max_members')
                   ->setAttrib('maxlength', '3')
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
                   ->addValidator('int', false)
                   ->addValidator('greaterThan', false, array('min' => 0))
                   ->addErrorMessage("Maximum number of members can't be empty.")
                   ->addDecorators($this->_elementDecorators);
        $this->addElement($maxMembers);
        
        /*
         * Default transaction fee (flat)
         */
        $transFeeFlat = new Zend_Form_Element_Text('transaction_fee_flat');
        $transFeeFlat->setLabel('Default transaction fee (flat):')
             ->setRequired(true)
             ->setAttrib('maxlength', '12')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('LocalizedToNormalized')
             ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
             ->addValidator('stringLength', false, array(1, 12))
             ->addValidator('float', false, array('locale' => Zend_Registry::get('config')->resources->locale->default))
             ->addValidator('callback', false, function($v){return $v>=0;})
             ->addDecorators($this->_elementDecorators);
        $this->addElement($transFeeFlat);

        /*
         * Default transaction fee (%)
         */
        $transFeePercent = new Zend_Form_Element_Text('transaction_fee_percent');
        $transFeePercent->setLabel('Default transaction fee (%):')
             ->setRequired(true)
             ->setAttrib('maxlength', '12')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('LocalizedToNormalized')
             ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
             ->addValidator('stringLength', false, array(1, 12))
             ->addValidator('float', false, array('locale' => Zend_Registry::get('config')->resources->locale->default))
             ->addValidator('callback', false, function($v){return $v>=0;})
             ->addDecorators($this->_elementDecorators);
        $this->addElement($transFeePercent);
        
        /*
         * Minimum contribution
         */
        $minContribution = new Zend_Form_Element_Text('min_contribution');
        $minContribution->setLabel('Minimum contribution value:')
             ->setRequired(true)
             ->setAttrib('maxlength', '12')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('LocalizedToNormalized')
             ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
             ->addValidator('stringLength', false, array(1, 12))
             ->addValidator('float', false, array('locale' => Zend_Registry::get('config')->resources->locale->default))
             ->addValidator('callback', false, function($v){return $v>=0;})
             ->addErrorMessage("Minimum contribution can't be empty.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($minContribution);

        /*
         * Maximum contribution
         */
        $maxContribution = new Zend_Form_Element_Text('max_contribution');
        $maxContribution->setLabel('Maximum contribution value:')
             ->setRequired(true)
             ->setAttrib('maxlength', '12')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter('LocalizedToNormalized')
             ->addFilter('pregReplace', array('match' => '/\s+/', 'replace' => ''))
             ->addValidator('stringLength', false, array(1, 12))
             ->addValidator('float', false, array('locale' => Zend_Registry::get('config')->resources->locale->default))
             ->addValidator('callback', false, function($v){return $v>=0;})
             ->addErrorMessage("Maximum contribution can't be empty.")
             ->addDecorators($this->_elementDecorators);
        $this->addElement($maxContribution);
                
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