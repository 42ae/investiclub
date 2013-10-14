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
 * Notice View Helper
 * 
 * @package    	View
 * @subpackage	Helper
 * @author		Alexandre Esser
 */
class Ivc_View_Helper_Notice extends Zend_View_Helper_Abstract
{
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR = 'error';
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function notice()
    {
        return $this;
    }

    public function format($messages)
    {
        $content = array();
        $type = '';
        
        if (is_array($messages)) {
            foreach ($messages as $key => $value) {
                if ($value AND in_array($key, $this->getMessageTypes())) {
                    $type = $key;
                    $content = array_unique(array_merge($content, $value));
                } elseif ($value) {
                    $type = Ivc_Message::ERROR;
                    $content = array_unique(array_merge($content, $value));
                }
            }
        }
        
        return array('content' => $content, 'type' => $type);
    }

    public function get($messagesArray = null)
    {
        // default structure for returned message is:
        // - type: error | warning | success
        // - content: array of messages
        $message = array('content' => array(), 'type' => '');
        
        if ($messagesArray AND is_array($messagesArray)) {
            
            $flashMesenger = array();
            foreach ($messagesArray as $val) {
                if (is_array($val) AND Ivc_Utils::array_keys_exists($this->getMessageTypes(), $val)) {
                    // This is a FlashMessenger message, we have to format it properly
                    foreach ($val as $type => $message) {
                        if (null != $message) {
                            foreach ($message as $value) {
                                $flashMesenger[0][$type][] = $value;
                            }
                        }
                    }
                }
            }
            if (null != $flashMesenger) {
                $messagesArray = $flashMesenger[0];
            }
            $message = $this->format($messagesArray);

        }

        $class = 'flash ' . $message['type'];
        $style = ($message['content']) ? '' : 'display:none';
        
        $html = '<div id="top-notice" class="'. $class . '" style="' . $style . '">';
        $html .= '<p>' . implode('</p>' . PHP_EOL . '<p>', $message['content']) . '</p>';
        $html .= '</div>';
        return $html;
    }
    
    public function getMessageTypes()
    {
        return array(Ivc_Message::ERROR, Ivc_Message::WARNING, Ivc_Message::SUCCESS);
    }
}