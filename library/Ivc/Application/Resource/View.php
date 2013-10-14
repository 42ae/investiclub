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
 * View resource plugin
 * 
 * Initialize the view object, placeholders (js, css...) and jQuery settings.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Bootstrap
 * @subpackage	Resource
 */
class Ivc_Application_Resource_View extends Zend_Application_Resource_View
{
    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_View
     */
    public function init()
    {
        $view = $this->getView();

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $view;
    }
    
    /**
     * Retrieve view object
     *
     * @return Zend_View
     */
    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            
            $title = '';
            $separator = ' ';
            if (array_key_exists('title', $options)) {
                $title = $options['title'];
                unset($options['title']);
            }
            if (array_key_exists('separator', $options)) {
                $separator = $options['separator'];
                unset($options['separator']);
            }
            
            $this->_view = new Zend_View($options);
            
            if (isset($options['doctype'])) {
                $this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
                if (isset($options['charset']) && $this->_view->doctype()->isHtml5()) {
                    $this->_view->headMeta()->setCharset($options['charset']);
                }
            }
            if (isset($options['contentType'])) {
                $this->_view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
            }
            if (isset($options['assign']) && is_array($options['assign'])) {
                $this->_view->assign($options['assign']);
            }
            
            // Set the initial title and separator:
            $this->_view->headTitle($title);
            $this->_view->headTitle()->setSeparator($separator);
            
            // jQuery Local Fallback
            // $view->_view->jQuery()->addJavascriptFile('/assets/js/fallback.js');
            
            // Set the initial stylesheet:
            $this->_view->headLink()->prependStylesheet('/assets/css/style.css');
            $this->_view->headLink()->prependStylesheet('/assets/css/public.css');
        }
        return $this->_view;
    }
}