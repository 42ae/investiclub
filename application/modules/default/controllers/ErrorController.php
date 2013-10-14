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
 * @package		Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */
/**
 * Error controller
 * 
 * Handles errors and display an error message.
 * 
 * @author		Zend Framework
 * @category	InvestiClub
 * @package		Controller
 */
class ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('error', 'json')
                    ->addActionContext('error', 'html')
                    ->initContext();
    }

    /**
     * Error handler
     * 
     * Displays an error message and log it if necessary. 
     * Modifies the HTTP header according to the error type.
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

                
        if (!$errors OR !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
             case ($errors->exception instanceof Ivc_Acl_Exception): 
               $this->getResponse()->setHttpResponseCode(401); // Unauthorized
               $priority = Zend_Log::ERR;
               $this->view->message = 'Unauthorized! Insufficient rights';
               $this->_helper->redirector('index', 'index', 'default');
               break; 
             case ($errors->exception instanceof Ivc_Exception): 
               $this->getResponse()->setHttpResponseCode(500); // Handled internal errors
               $priority = $errors->exception->getCode() ? $errors->exception->getCode() : Zend_Log::ERR;
               //Zend_Debug::dump($errors->exception);die;
               //Zend_Debug::dump($errors->exception->getMessage());die;
               $this->view->message = $errors->exception->getMessage();
               break; 
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';Zend_Debug::dump($errors->exception);
                break;
        }
        
        // Log exception, if logger available
        if (($log = $this->getLog()) != false) {
            $extras = array('user_id'     => Ivc::getCurrentUser()->user_id,
                            'ip'		  => $_SERVER['REMOTE_ADDR'], //@todo: better ip check 
            				'stack_trace' => 'File: ' . $errors->exception->getFile() . PHP_EOL .
                                           	 'Line: ' . $errors->exception->getLine() . PHP_EOL .
                                             'Param:' . var_export($errors->request->getParams(), true));
            $log->log($this->view->message, $priority, $extras);
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    /**
     * Return a Zend_Log instance
     * 
     * @return Zend_Log|false
     */
    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

