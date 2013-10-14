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
 * User's class for SQL table dependencies.
 * 
 * @author	Alexandre Esser
 * @package	DbTable
 */
class Ivc_Model_Users_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';
    protected $_dependentTables = array('Ivc_Model_Users_DbTable_UserSettings',
                                        'Ivc_Model_Users_DbTable_Notifications',
                                        'Ivc_Model_Clubs_DbTable_Members');
}

class Ivc_Model_Users_DbTable_UserSettings extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_settings';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'),
            'refTableClass' => 'Ivc_Model_Users_DbTable_Users',
            'refColumns'    => array('user_id')
        )
   );
}

class Ivc_Model_Users_DbTable_Messages extends Zend_Db_Table_Abstract
{
    protected $_name = 'messages';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'),
            'refTableClass' => 'Ivc_Model_Users_DbTable_Users',
            'refColumns'    => array('user_id')
        )
   );
}