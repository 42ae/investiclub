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
 * Enter description here ...
 * 
 * @author		Jonathan Hickson
 * @category	Ivc
 * @package		Ivc_Error
 */
class Ivc_Error_Core
{ 
    
    public $list;
    


	public function __construct()
    {
        $this->list = array(Ivc_Message::ERROR => null,
                            Ivc_Message::WARNING => null,
                            Ivc_Message::SUCCESS => null);
    }
    
    public function push($type, $message)
    {
        if ($type === Ivc_Message::SUCCESS or $type === Ivc_Message::WARNING or $type === Ivc_Message::ERROR)
            $this->list[$type][] = $message;
        else
        {
            /* TODO : use the future implementation of IVC Log */
            echo "Please use Ivc_Error Const !";
            $exception = new Zend_Exception();
            foreach($exception->getTrace() as $t){
                echo $t['line'] . " " . $t['file'] . "<br />";
            }
            die;
        }
        return ($this);
    }
    
    public function level()
    {
        if (count($this->list[Ivc_Message::ERROR]))
            return (2);
        elseif (count($this->list[Ivc_Message::WARNING]))
            return (1);
        else //SUCCESS
            return (0);
    }
    
    public function flush()
    {
        unset($this->list);
        $this->list = array(Ivc_Message::ERROR => null,
                            Ivc_Message::WARNING => null,
                            Ivc_Message::SUCCESS => null);
        return ($this);
    }
    
     /**
	 * @return $list
	 */
	public function getList() {
		return $this->list;
	}
}

class Ivc_Error
{
    const SUCCESS = 'SUCCESS';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    
    const TR_PF        = 'treasuryPortfolio';

    private static $_ivcErrorSession;
    private $_errorList;
    
    private function __construct()
    {
        $this->_errorList = array();
    }
    
    static public function getInstance($nameSpace)
    {
        if (null === self::$_ivcErrorSession) {
            self::$_ivcErrorSession = new self();
        }
        if (!isset(self::$_ivcErrorSession->_errorList[$nameSpace]))
            self::$_ivcErrorSession->_errorList[$nameSpace] = new Ivc_Error_Core();
        return (self::$_ivcErrorSession->_errorList[$nameSpace]);
    }
}