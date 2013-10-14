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
 * @package		DbTable
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Brokers class for SQL table dependencies.
 * 
 * @author	Alexandre Esser
 * @package	DbTable
 */
class Ivc_Model_Clubs_DbTable_Brokers extends Zend_Db_Table_Abstract
{
    protected $_name = 'brokers';
    protected $_dependentTables = array('Ivc_Model_Clubs_DbTable_Clubs');
}