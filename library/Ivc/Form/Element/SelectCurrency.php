<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category    Ivc
 * @package     Ivc_Form
 * @copyright   Copyright (c) 2011-2013 All Rights Reserved
 * @license     http://investiclub.net/license
 */


/**
 * Currency element
 * 
 * Create a select element filled with a list of all currencies 
 * translated in the user's language.
 * 
 * @author      Alexandre Esser
 * @category    Ivc
 * @package     Ivc_Form
 * @subpackage  Element
 */
class Ivc_Form_Element_SelectCurrency extends Zend_Form_Element_Select
{
    /* @var string */
    protected $_column = 'currency';

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

        $currencyToRegion = Zend_Locale::getTranslationList('CurrencyToRegion', $locale);
        
        $currencies = array();
        foreach ($currencyToRegion as $key => $value) {
            $currencies[$value] = $value;
        }

        $oldLocale = setlocale(LC_COLLATE, '0');
        setlocale(LC_COLLATE, 'en_US');
        asort($currencies, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);
        
        $currency = new Zend_Currency($locale);
        $currentCurrency = $currency->getShortName();

        $topItems = array();
        if ($currentCurrency) {
            $topItems[$currentCurrency] = $currentCurrency;
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
        $this->setOptions(array('disable' => array('---')));
        $currencies = array_merge($topItems, $currencies);

        $this->setMultiOptions($currencies);
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