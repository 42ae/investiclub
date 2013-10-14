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
 * @package		Ivc_Bootstrap
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Internationalization resource plugin
 * 
 * I18n and L10n with Zend_Locale and Zend_Translate.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Application
 * @subpackage	Resource
 */
class Ivc_Application_Resource_Internationalization extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Translate
     */
    protected $_translate;

    /**
     * @var Zend_Locale
     */
    protected $_locale;

    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     */
    public function init()
    {
        $this->setInternationalization();
    }

    /**
     * Set objects such as Zend_Translate for internationalization
     *
     */
    public function setInternationalization()
    {
        $options = $this->getOptions();
        $bootstrap = $this->getBootstrap();
        
        if ($bootstrap instanceof Zend_Application_Bootstrap_ResourceBootstrapper 
            AND $bootstrap->hasPluginResource('locale') 
            AND $bootstrap->hasPluginResource('translate')) {
                
            $this->_locale = $bootstrap->locale;
            $this->_translate = $bootstrap->translate;
            
            switch ($this->_locale) {
                case 'en_US':
                    $this->_translate->setLocale('en');
                    break;
                case 'en_GB':
                    $this->_translate->setLocale('uk');
                    break;
                case 'fr_FR':
                    $this->_translate->setLocale('fr');
                    break;
            }
        }
        
        $bootstrap->bootstrap('view');
        if ($bootstrap->hasPluginResource('view')) {
            $this->_view = $bootstrap->view;
            $timezone = Zend_Registry::get('session.l10n')->timezone;
            if (!isset($timezone)) {
                $timezone = date_default_timezone_get();
                $this->_view->jQuery()->addJavascriptFile('/assets/js/jstz.min.js');
            }
        }
    }
}