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
 * Add Email for "Keep me Updated" form. This form is only used for filter and validation purpose.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Members
 */
class Form_Public_KeepMeUpdated extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        /*
         * E-mail
         */
        $email = new Zend_Form_Element_Text('email');
        $email->setRequired(true)
              ->setAttrib('maxlenght', '254')
              ->addErrorMessage('Email invalid or existing.')
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addFilter('StringToLower')
              ->addValidator('EmailAddress', true)
              ->addValidator('EmailDenied', true)
              ->addValidator('stringLength', true, array(5, 254))
              ->addValidator('Db_NoRecordExists', true, array('newsletter', 'email'))
              ->addDecorators($this->_elementDecorators);
        $this->addElement($email);
    }
    
}
    