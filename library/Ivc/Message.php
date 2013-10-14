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
 * @package		Ivc_Error
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Message manager
 * 
 * @author		Jonathan Hickson
 * @category	Ivc
 * @package		Ivc_Message
 */
class Ivc_Message
{
    const ERROR   = 'error';
    const WARNING = 'warning';
    const SUCCESS = 'success';

    // a voir comment tu veux les nommer, dans l'ideal utilise CONTROLLER(_MODEL)
    const TR_PF   = 'treasuryPortfolio'; 
    const MEMBERS = 'members';
    const USERS   = 'users';
    const CLUBS   = 'clubs';

    private static $_instance = null;
    
    /**
     * Singleton pattern
     * @param string $namespace
     * @return Ivc_Message_Core
     */
    static public function getInstance()
    {
        if (!isset(self::$_instance))
            self::$_instance = new Ivc_Message_Core;
        return self::$_instance;
    }
}

class Ivc_Message_Core
{
    protected $_messages;

	public function __construct()
    {
        $this->_messages = array(Ivc_Message::ERROR   => null,
                                 Ivc_Message::WARNING => null,
                                 Ivc_Message::SUCCESS => null);
    }
    
    public function push($type, $message)
    {
        if (false === array_key_exists($type, $this->_messages) AND null === $message) {
            /* TODO : use the future implementation of IVC Log */
            $e = new Zend_Exception('type must be a valid constant and/or a message must be specified');
            foreach($e->getTrace() as $t){
                echo $t['line'] . " " . $t['file'] . "<br />";
            }
            throw new Ivc_Exception(Ivc_Exception::ERROR_OCCURED, Zend_Log::CRIT);
        }
        
        $log = Zend_Registry::get('Zend_Log');
        $log->log($message, Zend_Log::INFO, array('stack_trace' => 'LOL', 'user_id' => '0', 'ip' => 'LMAO'));
        // @todo: push info through another writer with url/params/stacktrace/...
        
        $translate = Zend_Registry::get('Zend_Translate');
        $args = func_get_args();
        $type = array_shift($args);
        $args[0] = $translate->_($args[0]);
        $message = call_user_func_array('sprintf', $args);
        
        $this->_messages[$type][] = $message;
        return $this;
    }
    
    public function flush()
    {
        foreach ($this->_messages as $k => $v) {
            $this->_messages[$k] = null;
        }
        return $this;
    }

    public function getLevel()
    {
        if (count($this->_messages[Ivc_Message::ERROR]))
            return "2";
        elseif (count($this->_messages[Ivc_Message::WARNING]))
            return "1";
        return "0";
    }
    
    /**
	 * @return array $_messages
	 */
	public function toArray() {
		return $this->_messages;
	}
}