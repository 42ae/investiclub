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
 * Clubs class for SQL table dependencies.
 * 
 * @author	Alexandre Esser
 * @package	DbTable
 */
class Ivc_Model_Clubs_DbTable_Clubs extends Zend_Db_Table_Abstract
{
    protected $_name = 'clubs';
    protected $_referenceMap = array(
    'Club' => array(
        'columns'       => array('broker_id'),
        'refTableClass' => 'Ivc_Model_Clubs_DbTable_Brokers',
        'refColumns'    => array('broker_id')
        )
    );
    protected $_dependentTables = array('Ivc_Model_Clubs_DbTable_Members', 
    									'Ivc_Model_Clubs_DbTable_ClubSettings');
}

class Ivc_Model_Clubs_DbTable_ClubSettings extends Zend_Db_Table_Abstract
{
    protected $_name = 'club_settings';
    protected $_referenceMap = array(
        'Club' => array(
            'columns'       => array('club_id'),
            'refTableClass' => 'Ivc_Model_Clubs_DbTable_Clubs',
            'refColumns'    => array('club_id')
        )
   );
}