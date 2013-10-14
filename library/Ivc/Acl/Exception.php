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
 * Use: Zend_Log::CONST
 * 
 * EMERG   = 0;  // Emergency: system is unusable
 * ALERT   = 1;  // Alert: action must be taken immediately
 * CRIT    = 2;  // Critical: critical conditions
 * ERR     = 3;  // Error: error conditions
 * WARN    = 4;  // Warning: warning conditions
 * NOTICE  = 5;  // Notice: normal but significant condition
 * INFO    = 6;  // Informational: informational messages
 * DEBUG   = 7;  // Debug: debug messages
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Acl
 * @subpackage	Exception
 */
class Ivc_Acl_Exception extends Zend_Exception
{
    public function __construct($msg = '', $code = 3, Exception $previous = null)
    {
            parent::__construct('', $code, $previous);
    }
}