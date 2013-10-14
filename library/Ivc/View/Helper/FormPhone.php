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
 * @package		Ivc_View
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * 
 * Phone View Helper
 * 
 * International calling number and text input.
 * 
 * @author		Alexandre Esser
 * @package    	Ivc_View
 * @subpackage	Helper
 * @since		2012-01-15
 */
class Ivc_View_Helper_FormPhone extends Zend_View_Helper_FormElement
{
    public function formPhone($name, $value = null, $attribs = null)
    {
        $code = '';
        $number = '';
        if (is_array($value)) {
            $code = $value['code'];
            $number = $value['number'];
        }  elseif ($value) {
             list($code, $number) = explode('-', $value, 2);
           }

        $codeAttribs = isset($attribs['codeAttribs']) ? $attribs['codeAttribs'] : array();
        $numberAttribs = isset($attribs['numberAttribs']) ? $attribs['numberAttribs'] : array();
            
        $locale = Zend_Registry::get('Zend_Locale');
        if (!$locale)
            throw new Exception('No locale set in registry');

        $phoneToTerritory = Zend_Locale::getTranslationList('PhoneToTerritory', $locale);
        unset($phoneToTerritory["001"]);
        $territory = Zend_Locale::getTranslationList('Territory', $locale, 2);
        
        $array = array();
        foreach ($phoneToTerritory as $key => $value) {
            if (array_key_exists($key, $territory))
                $codeMultiOptions[$key] = $territory[$key] . ' (+' . $value . ')';
        }
        
        $oldLocale = setlocale(LC_COLLATE, '0');
        setlocale(LC_COLLATE, 'en_US');
        asort($codeMultiOptions, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);
        
        $topItems = array();
        $currentRegion = $locale->getRegion();
        if ($currentRegion) {
            $topItems[$currentRegion] = $codeMultiOptions[$currentRegion];
        }
        
        $codeMultiOptions = $topItems + $codeMultiOptions;

        return
            $this->view->formSelect(
                $name . '[code]',
                $code,
                $codeAttribs,
                $codeMultiOptions) .
            $this->view->formText(
                $name . '[number]',
                $number,
                $numberAttribs);
    }
}