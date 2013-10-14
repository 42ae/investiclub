<?php

/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category    Ivc
 * @package     Ivc_Form
 * @copyright   Copyright (c) 2011-2013 All Rights Reserved
 * @license     http://investiclub.net/license
 */
/**
 * Role element
 * 
 * @author      Alexandre Esser
 * @category    Ivc
 * @package     Ivc_Form
 * @subpackage  Element
 */
class Ivc_Form_Element_SelectRole extends Zend_Form_Element_Select
{
    protected $_translatorDisabled = true;

    public function init()
    {

        $roles = array('president' => 'President',
        			   'secretary' => 'Secretary',
        			   'treasurer' => 'Treasurer',
                       'member'    => 'Member');
        $this->setMultiOptions($roles);
    }
}
?>