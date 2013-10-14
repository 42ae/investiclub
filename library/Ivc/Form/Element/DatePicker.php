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
 * jQuery DatePicker, automatically append a js to the page acording to the
 * user's locale.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_DatePicker extends ZendX_JQuery_Form_Element_UiWidget
{
    public $helper = "datePicker";
    
    /**
     * Constructor
     *
     * @param  mixed $spec
     * @param  mixed $options
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        if (Zend_Registry::isRegistered('Zend_Locale')) {
            $lang = Zend_Registry::get('Zend_Locale')->getLanguage();
            if ('en' !== $lang) {
                // @todo: lookup associative array between jQuery language and Zend language.
                $jQuery = $this->getView()->jQuery();
                $jQuery->addJavascriptFile('http://jquery-ui.googlecode.com/svn/tags/latest/ui/i18n/jquery-ui-i18n.js');
                $jQuery->addOnLoad('$.datepicker.setDefaults($.datepicker.regional[\'' . $lang . '\']);');
            }
        }
        parent::__construct($spec, $options);
    }
    
    
}