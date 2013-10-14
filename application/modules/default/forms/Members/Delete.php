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
class Form_Members_Delete extends Ivc_Form
{
    
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
        
    }
}
    