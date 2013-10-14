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
 * Account controller
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class AccountController extends Zend_Controller_Action
{

    public function init()
    {
        if ($this->getRequest()->getActionName() == 'logout')
            return;
            
        if (Ivc_Auth::isLogged()) {
            $this->_helper->redirector('view', 'users');
        }
    }

    /**
     * The default users action
     * 
     * Checks whether if a user is authenticated and redirect him to his
     * personnal account or to the login page according to his status.
     * 
     * 
     * Email verification:
     * 
     * If a post request is send through this page with a "e" and "t" get
     * parameters, the method create a {@link Model_Users_Signup} object
     * and check if the e-mail (e) and token (t) to validate are correct.
     * Finally, the request is redirected to the login page.
     */
    public function indexAction()
    {
        $email = $this->_getParam('e');
        $token = $this->_getParam('t');
        
        if (null !== $email AND null !== $token) {
            $model = new Model_Users_Signup();
            $status = $model->activateAccount($email, $token);
            $messages = $model->getMessages()->toArray();
            $this->_helper->flashMessenger($messages);
        }
        $this->_helper->redirector('login', 'account');
    }

    /**
     * The login action
     * 
     * Renders a {@link Form_Users_Login} form and sends the credentials 
     * received to an {@link Ivc_Auth} object.
     */
    public function loginAction()
    {
        $form = new Form_Users_Login();
        $form->setAction($this->view->url());
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $auth = new Ivc_Auth($form->getValues());
                if (true === $auth->authenticate()) {
                    $this->_helper->redirector('index', 'index');
                }
                $messages = $auth->getMessages()->toArray();
            } else {
                $messages = $form->getMessages();
            }
            $this->_helper->flashMessenger($messages);
            $this->_helper->redirector('login', 'account');
        }
        
        $messages = $this->_helper->flashMessenger->getMessages();
        $notice = $this->view->notice()->get($messages);
        $this->view->messages = $notice;
        
        $this->view->layout()->nestedLayout = 'loginLayout';
        $this->view->form = $form;
    }

    /**
     * The logout action
     * 
     * Clear the current user's identity and clear his session.
     * Finally, the request is redirected to the login page.
     */
    public function logoutAction()
    {
        Zend_Session::forgetMe();
        Ivc_Model_Users_Session::namespaceUnset();
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('login', 'account');
    }
    
    /**
     * Sign Up action
     * 
     * Renders a {@link Model_Users_Signup} form and sends the data entered
     * to it. Then, a verification email is generated and sent to the new registered 
     * user.
     */
    public function signupAction()
    {
        $form = new Form_Users_Signup();
        $form->setAction($this->view->url());
        $request = $this->getRequest();
        
         if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $model = new Model_Users_Signup();
                $user = $model->signUp($data);
                $this->view->user = $user;
                $this->renderScript('/account/signup-confirmation.phtml');
            }
            $this->messages = $form->getMessages();
         }
         $this->view->hideSidebar = true;
         $this->view->hideBreadCrumbs = true;
         $this->view->form = $form;
    }
}

