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
 * This class is used to start up and is configured to use
 * directives in the configuration file.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Bootstrap
 * @version		2.0
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Initialize a user for each new request
     */
    protected function _initUser()
    {
        // ensure db has been bootstrapped
        $this->bootstrap('db');
        if (Ivc_Auth::isLogged()) {
            $gateway = new Ivc_Model_Users_Gateway();
            $user = $gateway->fetchUser(Ivc::getCurrentUserId());
            Ivc::setCurrentUser($user);
            if ($user->hasClub()) {
                $gateway = new Ivc_Model_Clubs_Gateway();
                $member = $gateway->fetchMember($user, 'active');
                Ivc::setCurrentMember($member);
            }
            // Set User Last Activity for active caching
            $dba = Zend_Db_Table::getDefaultAdapter();
            if ($dba->fetchOne('SELECT COUNT(*) as isThere FROM `users_activity` WHERE user_id = ' . Ivc::getCurrentUserId()))
            	$dba->update('users_activity', array('last_activity' => new Zend_Db_Expr('NOW()')), 'user_id = ' . Ivc::getCurrentUserId());
            else
            	$dba->insert('users_activity', array('user_id' => Ivc::getCurrentUserId(), 'last_activity' => new Zend_Db_Expr('NOW()')));
        }
        return Ivc::getCurrentUser();
    }
    
    /**
     * Profiles the application during the development phase
     */
    protected function _initProfiling()
    {
        // ensure db has been bootstrapped
        $this->bootstrap('db');
        /* TODO : we should init cache before to set properly dbCache, cacheMode and stats*/
        $cache = Ivc_Cache::getInstance();
        $cache->setCacheMode(true);
        $cache->setStats('on'); // off, on (just for a page), full (store in db)
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache->getCache()); //comment it when making db changes
        /* TODO : End of the mess */
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $profiler = new Ivc_Profiler('Ivc DB Queries from Bootstrap');
        $profiler->setEnabled(true);
        $adapter->setProfiler($profiler);
        $cs = array('treasury' => 0, 'portfolio' => 0, 'core_treasury' => 0, 'core_portfolio' => 0);
        Zend_Registry::set('Construct_stats', $cs);
        return microtime(true);
    }
    
    /**
     * Registers the config in the registry
     */
    protected function _initConfig()
    {
        if (($config = Ivc_Cache::getInstance()->load(Ivc_Cache::SCOPE_IVC, 'zendConfig')) === false) {
            $config = new Zend_Config($this->getOptions());
            Ivc_Cache::getInstance()->save($config, Ivc_Cache::SCOPE_IVC, 'zendConfig');
        }
        Zend_Registry::set('config', $config);
        return $config;
    }
    
    protected function _initSession()
    {
        $this->bootstrap('config');
        $config = Zend_Registry::get('config');

        $l10n = new Zend_Session_Namespace($config->session->namespace->l10n, false);
        Zend_Registry::set('session.l10n', $l10n);

        $user = new Zend_Session_Namespace($config->session->namespace->request, false);
        Zend_Registry::set('session.request', $user);
        
        $user = new Zend_Session_Namespace($config->session->namespace->user, false);
        Zend_Registry::set('session.user', $user);
    }

    /**
     * Initialize ACL using a factory pattern.
     */
    protected function _initAcl()
    {
        $this->bootstrap('db');
        $factory = new Ivc_Acl_Factory();
        $acl = $factory->getAcl();
        Zend_Registry::set('Ivc_Acl', $acl);
    }

    protected function _initLog()
    {
        $this->bootstrap('db');
        $db = $this->getResource('db');
        
        $columnMap = array('priority' => 'priority', 
        				   'priority_name' => 'priorityName',
                           'message' => 'message',
                           'user_id' => 'user_id',
                           'stack_trace' => 'stack_trace', 
                           'ip' => 'ip', 
                           'timestamp' => 'timestamp');
        
        $db = Zend_Db_Table::getDefaultAdapter();
        $writer = new Zend_Log_Writer_Db($db, "logs", $columnMap);
        $log = new Zend_Log($writer);
        Zend_Registry::set('Zend_Log', $log);
        return $log;
    }
    
//    protected function _initErrorHandler()
//    {
//        $plugin = new Zend_Controller_Plugin_ErrorHandler();
//        $plugin->setErrorHandlerModule('default')
//               ->setErrorHandlerController('error')
//               ->setErrorHandlerAction('error');
//        $front = Zend_Controller_Front::getInstance();
//        $front->registerPlugin($plugin);
//    }
}

/**
 * This class is used to as a singleton to retrieve the current
 * user after each new request.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Bootstrap
 * @version		1.0
 */
class Ivc extends Bootstrap
{
    /**
     * @var Ivc_Model_User_User
     */
    protected static $_currentUser;
    
    /**
     * @var Ivc_Model_Users_Member
     */
    protected static $_currentMember;

    /**
     * Ivc singleton constructor
     * 
     * @param Zend_Application
     */
    public function __construct($application)
    {
        parent::__construct($application);
    }

    /**
     * @param Ivc_Model_Users_User
     */
    public static function setCurrentUser(Ivc_Model_Users_User $user)
    {
        self::$_currentUser = $user;
    }

    /**
     * @param Ivc_Model_Clubs_Club
     */
    public static function setCurrentMember(Ivc_Model_Users_Member $member)
    {
        self::$_currentMember = $member;
    }

    /**
     * @return Ivc_Model_Users_User
     */
    public static function getCurrentUser()
    {
        if (null === self::$_currentUser) {
            $gateway = new Ivc_Model_Users_Gateway();
            $guest = $gateway->createUser(array('user_id' => Ivc_Acl::GUEST));
            self::setCurrentUser($guest);
        }
        return self::$_currentUser;
    }

    public static function getCurrentMember()
    {
        return self::$_currentMember;
    }

    /**
     * @return integer $user_id
     */
    public static function getCurrentUserId()
    {
        if (Ivc_Auth::isLogged()) {
            return Zend_Auth::getInstance()->getIdentity()->userId;
        }
        return Ivc::getCurrentUser()->user_id;
    }
}