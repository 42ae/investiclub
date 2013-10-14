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
 * Country element
 * 
 * Create a select element filled with a list of all countries 
 * translated in the user's language.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_SelectCountry extends Zend_Form_Element_Select
{
    /* @var string */
    protected $_column = 'country';

    /* @var string */
    protected $_table;

    /* @var string */
    protected $_where;

    protected $_translatorDisabled = true;

    public function init()
    {
        $locale = Zend_Registry::get('Zend_Locale');

        if (!$locale) {
            throw new Exception('No locale set in registry');
        }

        $countries = Zend_Locale::getTranslationList('territory', $locale, 2);

        unset($countries['ZZ']);

        $oldLocale = setlocale(LC_COLLATE, '0');
        setlocale(LC_COLLATE, 'en_US');
        asort($countries, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);


        $topItems = array();
        
        $currentRegion = $locale->getRegion();
        if ($currentRegion) {
            $topItems[$currentRegion] = $countries[$currentRegion];
            unset($countries[$currentRegion]);
        }

		/* if (isset($this->_table)) {
            $table = new $this->_table;
            $select = $table->select();
            $select->from($table, array($this->_column))
                   ->where("{$this->_column} != ''")
                   ->where("{$this->_column} != ?", $currentRegion)
                   ->group($this->_column)
                   ->order('COUNT(*) DESC')
                   ->limit(5);
           if (isset($this->_where)) $select->where($this->_where);
           $adapter = $table->getAdapter();
           $mostFrequentlyUsed = $adapter->fetchCol($select);
           foreach($mostFrequentlyUsed as $countryCode) {
               $topItems[$countryCode] = $countries[$countryCode];
               unset($countries[$countryCode]);
           }
        } */

        $topItems['---'] = '---';
        $countries = array_merge($topItems, $countries);

        $this->setMultiOptions($countries);
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