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
 * @package		DbTable
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Stocks class for SQL table dependencies.
 * 
 * @author	Alexandre Esser
 * @package	DbTable
 */
class Model_Portfolio_DbTable_QuotesLive extends Zend_Db_Table_Abstract
{
    protected $_name = 'quotes_live';
    protected $_referenceMap = array(
        'Stock' => array(
            'columns'       => array('symbol'),
            'refTableClass' => 'Model_Portfolio_DbTable_Stocks',
            'refColumns'    => array('symbol')
        )
   );
}