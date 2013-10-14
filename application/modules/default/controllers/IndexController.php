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
 * Index controller
 * 
 * Website homepage, will be use as a showcase to show the product
 * and his main features.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        if ($this->getRequest()->getActionName() == 'contact') {
            return;
        }
        
        if (Ivc_Auth::isLogged()) {
            $this->_helper->redirector('index', 'dashboard', 'default');
        }
    }
    
    public function langAction()
    {
        $request = $this->getRequest();
        $lang = $this->_getParam('lang');
        
        $session = Zend_Registry::get('session.l10n');
        if (null !== $lang AND in_array($lang, Zend_Registry::get('Zend_Translate')->getList())) {
            switch ($lang) {
                case 'fr': $session->locale = 'fr_FR'; break;
                case 'us': $session->locale = 'en_US'; break;
                case 'en': $session->locale = 'en_GB'; break;
            }
        }
        
        $this->_helper->redirectToOrigin();
    }

    public function indexAction()
    {
        $action = $this->_helper->url('keep-me-updated', 'index', 'ajax', array('format' => 'json'));
        $kmuForm = new Form_Public_KeepMeUpdated();
        $kmuForm->setAction($action);
        
        $this->view->layout()->nestedLayout = 'blankLayout';
        $this->view->kmuForm = $kmuForm; 
        
        
//        echo Ivc_Model_Users_Session::getInstance()->timezone;
//        if (Ivc_Model_Users_Session::getInstance()->timezone)
//            echo "Timezone = " . Ivc_Model_Users_Session::getInstance()->timezone . "<br />";
//        $date = Zend_Date::now();
//        $date = $date->toString(Zend_Date::ISO_8601);
//        echo "Date inserée : " . $date . "<br />";
//        $db = Zend_Db_Table::getDefaultAdapter();
//        $data = array(
//            'created_on' => $date);
//        echo $n = $db->update('users', $data, 'user_id = 100') . "<br />";
//        $newDate = new Zend_Date($db->fetchOne('SELECT created_on FROM users WHERE user_id = 100'), Zend_Date::ISO_8601);
//        $newDate->setTimezone(Ivc_Model_Users_Session::getInstance()->timezone);
//        echo "My date : " . $newDate->toString();
    }
    
    public function solutionAction() {
        $this->view->layout()->nestedLayout = 'blankLayout';
    }

    public function newsAction() {
        $model = new Model_Public_News;
        $model->checkAcl('news');
        $this->view->layout()->nestedLayout = 'blankLayout';
    }

    public function contactAction() {
        $action = $this->_helper->url('contact', 'index', 'ajax', array('format' => 'json'));
        $contactForm = new Form_Public_Contact();
        $contactForm->setAction($action);
        
        $this->view->layout()->nestedLayout = 'blankLayout';
        $this->view->contactForm = $contactForm;
    }
}

