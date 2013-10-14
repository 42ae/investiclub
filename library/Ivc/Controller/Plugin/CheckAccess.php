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
 * @package		Ivc_Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Plugin that checks if a user is logged, and if so, if he
 * has enough privillege to access to the requested page or
 * resource.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Controller
 * @subpackage	Plugin
 */
class Ivc_Controller_Plugin_CheckAccess extends Zend_Controller_Plugin_Abstract
{
    const FAIL_AUTH_MODULE = 'default';
    const FAIL_AUTH_ACTION = 'login';
    const FAIL_AUTH_CONTROLLER = 'users';
    const FAIL_ACL_MODULE = 'default';
    const FAIL_ACL_ACTION = 'index';
    const FAIL_ACL_CONTROLLER = 'index';
    protected $_auth;
    protected $_acl;

    /**
     * 
     * Constructor
     * 
     * Set the current ACL and Auth object.
     */
    public function __construct()
    {
        $this->_acl = Zend_Registry::get('Ivc_Acl');
        $this->_auth = Zend_Auth::getInstance();
    }
    
    /**
     * 
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_auth->hasIdentity()) {
            $role = $this->_auth->getIdentity()->roleId;
            $parentRole = $this->_acl->getParentRole($role);
            if (!strncmp($parentRole, Ivc_Acl::CLUB_ADMIN, strlen(Ivc_Acl::CLUB_ADMIN)))
            	$parentRole = $this->_acl->getParentRole($parentRole) . "/$parentRole";
         } else {
            $role = 'guest';
            $parentRole = 'guest';
        }
        // TODO: Update with the new super-mega useful Ivc object!
        
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $front = Zend_Controller_Front::getInstance();
        $default = $front->getDefaultModule();
        
        if ($module == $default) {
            $resource = $controller;
        } else {
            $resource = $module . '_' . $controller;
        }
        
        if ($this->_acl->has($resource) !== true) {
            $resource = null;
        }
        
        $requestedResource = "Requested controller : " . $resource . " - Action:  " . $action
        					. "  - Role: [$parentRole/$role]";
        Zend_Registry::set('requestedResource', $requestedResource);

//        if (!$this->_acl->isAllowed($role, $resource, $action)) {
//            if ($this->_auth->hasIdentity() === false) {
//                $requestedResource .= "] - Unauthenticated user";
//                $module = self::FAIL_AUTH_MODULE;
//                $controller = self::FAIL_AUTH_CONTROLLER;
//                $action = self::FAIL_AUTH_ACTION;
//            } else {
//                $requestedResource .= "] - Current user can't access this resource";
//                $module = self::FAIL_ACL_MODULE;
//                $controller = self::FAIL_ACL_CONTROLLER;
//                $action = self::FAIL_ACL_ACTION;
//            }
//        }
//        else 
//            $requestedResource .= "] - Request executed successfully";
//        $request->setModuleName($module);
//        $request->setControllerName($controller);
//        $request->setActionName($action);
    }
} 