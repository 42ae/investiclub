<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Acl
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Acl
 * @subpackage	Assert
 */
class Ivc_Acl_Assert_IsMale implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {
        if (Ivc::getCurrentUser()->gender == 'M')
            return true;
        return false;
    }
}