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
 * Locale resource plugin
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Bootstrap
 * @subpackage	Resource
 */
class Ivc_Application_Resource_Locale extends Zend_Application_Resource_Locale
{
    /**
     * @var Zend_Locale
     */
    protected $_locale;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Locale
     */
    public function init()
    {
        return $this->getLocale();
    }

    /**
     * Retrieve locale object
     *
     * @return Zend_Locale
     */
    public function getLocale()
    {
        $options = $this->getOptions();
        $sessionL10n = Zend_Registry::get('session.l10n');
        
        if (Ivc_Auth::isLogged()) {
            $sessionL10n->locale = Ivc::getCurrentUser()->getSettings()->locale;
        }
        
        if (isset($sessionL10n->locale)) {
            $this->_locale = new Zend_Locale($sessionL10n->locale);
            // HARD CODED
                $this->_locale->setLocale('fr_FR');
            // HARD CODED
            Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $this->_locale);
        } else {
            $this->_locale = parent::getLocale();
            $language = $this->_locale->getLanguage();
            if ($language == 'en') {
                if ($this->_locale != 'en_GB' AND $this->_locale != 'en_US') {
                    $this->_locale->setLocale('en_US');
                }
            } elseif ($language == 'fr') {
                if ($this->_locale != 'fr_FR') { // AND $this->_locale != 'fr_CA') {
                    $this->_locale->setLocale('fr_FR');
                }
            } else {
               $this->_locale->setLocale($options['default']);
            }
            
            // HARD CODED
                $this->_locale->setLocale('fr_FR');
            // HARD CODED
            $sessionL10n->locale = $this->_locale->toString();
        }
        
        return $this->_locale;
    }
}