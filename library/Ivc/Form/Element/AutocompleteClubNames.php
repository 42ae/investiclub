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
 * Autocomplete club name element
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_AutocompleteClubNames extends ZendX_JQuery_Form_Element_AutoComplete
{
    /* @var string */
    protected $_column = 'name';
    /* @var string */
    protected $_table = 'Ivc_Model_Clubs_DbTable_Clubs';

    protected $_translatorDisabled = true;

    public function init()
    {
        /* @var $table Zend_Db_Table_Abstract */
        $table = new $this->_table();
        $select = $table->select();
        $select->from($table, array('value' => $this->_column, 'id' => 'club_id'))
               ->where("active = ?", true);
               
        $adapter = $table->getAdapter();
        $clubs = $adapter->fetchAll($select);
        $this->setJQueryParam('source', $clubs);
    }
}