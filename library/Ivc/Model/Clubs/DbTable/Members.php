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
 * Members class for SQL table dependencies.
 * 
 * @author	Alexandre Esser
 * @package	DbTable
 */
class Ivc_Model_Clubs_DbTable_Members extends Zend_Db_Table_Abstract
{
    protected $_name = 'members';
    protected $_referenceMap = array(
        'Users' => array(
            'columns'       => array('user_id'),
            'refTableClass' => 'Ivc_Model_Users_DbTable_Users',
            'refColumns'    => array('user_id')
        ),
        'Clubs' => array(
            'columns'       => array('club_id'),
            'refTableClass' => 'Ivc_Model_Clubs_DbTable_Clubs',
            'refColumns'    => array('club_id')
        )
   );
}