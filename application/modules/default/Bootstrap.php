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
 * @package		Bootstrap
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Default Module
 * 
 * This class is used to start up the default module and is configured
 * to use directives in the configuration file.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Bootstrap
 */
class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * Autoload default module without prefix
     */
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(
                        array('namespace' => '', 
                              'basePath' => __DIR__));
    }
    
    private function getNavigationConfig()
    {
    	if (($navigationConfig = Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_IVC, 'navigationConfig')) === false)
    	{
    		$navigationConfig = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
    		Ivc_Cache::getInstance()->save($navigationConfig, Ivc_Cache::SCOPE_IVC, 'navigationConfig');
    	}
    	return ($navigationConfig);
    }
    
    /**
     * Initialize and configure the main navigation for Default module.
     */
    protected function _initNavigation()
    {      
        //@todo ensure resources are bootstraped
    	//if (($containerTop = Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_USER, 'navigationContainerTop')) === false)
    	{
    		$config = $this->getNavigationConfig();
            $pages = Ivc_Utils::convertNavigationAclToObject($config->toArray());
            $containerTop = new Zend_Navigation($pages);
            $containerTop->findBy('controller', 'users')->setVisible(false);
            //Ivc_Cache::getInstance()->save($containerTop, Ivc_Cache::SCOPE_USER, 'navigationContainerTop');
    	}
    	
    	//if (($containerSub = Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_USER, 'navigationContainerSub')) === false)
    	{
    		$config = $this->getNavigationConfig();
            $pages = Ivc_Utils::convertNavigationAclToObject($config->toArray());
            $containerSub = new Zend_Navigation($pages);
           	//Ivc_Cache::getInstance()->save($containerSub, Ivc_Cache::SCOPE_USER, 'navigationContainerSub');
    	}
    	
    	//if (($containerBC = Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_USER, 'navigationContainerBC')) === false)
    	{
    		$config = $this->getNavigationConfig();
            $pages = Ivc_Utils::convertNavigationAclToObject($config->toArray());
            $containerBC = new Zend_Navigation($pages);
            //$containerBC->findBy('controller', 'users')->setVisible(false);
            //Ivc_Cache::getInstance()->save($containerBC, Ivc_Cache::SCOPE_USER, 'navigationContainerBC');
    	}
    	
        if (Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_USER, 'acl') === false)
            Ivc_Cache::getInstance()->save(Zend_Registry::get('Ivc_Acl'), Ivc_Cache::SCOPE_USER, 'acl');

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl(Zend_Registry::get('Ivc_Acl'));
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(Ivc::getCurrentUser()->getRoleId());
        
        $config = Zend_Registry::get('config')->registry->index->navigation;
        
        Zend_Registry::set($config->topmenu, $containerTop);
        Zend_Registry::set($config->submenu, $containerSub);
        Zend_Registry::set($config->breadcrumbs, $containerBC);
    }
}