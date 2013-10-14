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
 * @package		Ivc_Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Autocomplete broker element
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_AutocompleteBroker extends ZendX_JQuery_Form_Element_AutoComplete
{
    /* @var string */
    protected $_column = 'broker_id';
    /* @var string */
    protected $_table;

    protected $_translatorDisabled = true;

    public function init()
    {
        $brokers = array();
        $this->_table = 'Ivc_Model_Clubs_DbTable_Brokers';
        
        if (isset($this->_table)) {
            /* @var $table Zend_Db_Table_Abstract */
            $table = new $this->_table();
            $select = $table->select();
            $select->from($table, array('id' => $this->_column, 'value' => 'name', 'url', 'country'))
                   ->where("is_default = ?", true)
                   ->group($this->_column);
            $adapter = $table->getAdapter();
            $brokers = $adapter->fetchAll($select);
            foreach ($brokers as $key => $broker) {
                $brokers[$key]['icon'] = $broker['id'] . '.png';
            }
        }
        $this->setJQueryParam('source', $brokers);
    }

    public function setColumn($column)
    {
        $this->_column = $column;
    }

    public function setTable($table)
    {
        $this->_table = $table;
    }

    public function setWhere($where)
    {
        $this->_where = $where;
    }
}
?>