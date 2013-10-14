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
 * @package		View
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Header Navigation View Helper
 * 
 * Creates a top navigation according to the current user's privilleges
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		View
 * @subpackage	Helper
 */
class Zend_View_Helper_Header extends Zend_View_Helper_Abstract
{

   public function header()
   {
        return $this;
   }
   
   public function render()
   {
       if (Ivc_Auth::isLogged()) {
           return $this->dropDown();
       }
       return $this->login();
   }
   
   public function dropDown()
   {
       
       $dashboard = $this->view->url(array('controller' => 'dashboard', 'action' => 'index'),    'default', true);
       $profile   = $this->view->url(array('controller' => 'users',     'action' => 'view'),     'default', true);
       $settings  = $this->view->url(array('controller' => 'users',     'action' => 'settings'), 'default', true);
       $logOut    = $this->view->url(array('controller' => 'account',   'action' => 'logout'),   'default', true);
       
       $html = '<div id="toplinks-wrapper" class="group">'
       		 .  '<div id="dropdown" class="right">'
 			 .   '<a class="my-account">' . $this->view->translate('MY_ACCOUNT') . '</a>'
 			 .   '<div class="submenu">'
 		   	 .    '<ul class="root">'
             .     '<li><a href="'. $dashboard .'">' . $this->view->translate('DASHBOARD') . '</a></li>'
             .     '<li><a href="'. $profile .'">' . $this->view->translate('PROFILE') . '</a></li>'
             .     '<li><a href="'. $settings .'">' . $this->view->translate('SETTINGS') . '</a></li>'
             .     $this->getMessagesLink()
             .     '<li><a href="'. $logOut .'">' . $this->view->translate('LOGOUT') . '</a></li>'
             .    '</ul>'
             .   '</div>'
             .  '</div>'
             . '</div>';
       return $html;
   }
   
   public function getMessagesLink()
   {
       $link = '';
       $model = new Model_Members_Members;
       if ($model->checkAcl('sendMessage')) {
           $messages  = $this->view->url(array('controller' => 'users',     'action' => 'messages'), 'default', true);
           $link .= '<li><a href="'. $messages .'">' . $this->view->translate('MESSAGES') . '</a></li>';
       }
       return $link;
   }
   
   public function login()
   {
       $signUp = $this->view->url(array('controller' => '', 'action' => 'signup'), 'default', true);
       $logIn  = $this->view->url(array('controller' => '', 'action' => 'login'),  'default', true);
       
       $html = '<div id="toplinks-wrapper" class="group">'
      		.  '<div class="header-links right">'
			.   '<a id="create-account" href="'. $signUp .'">' . $this->view->translate('CREATE_AN_ACCOUNT') . '</a> &nbsp;&nbsp;&nbsp; ' 
			.   '<a id="login" href="'. $logIn .'">' . $this->view->translate('LOGIN') . '</a> ' 
            .  '</div>'
            . '</div>';
      return $html;
  }
   
   
   public function renderDropDown2()
   {
        if (Ivc_Auth::isLogged()) {
            $user = Ivc::getCurrentUser();
            $html = '<ul id="headerNav">' . '<li title="' . $user->first_name . ' ' . $user->last_name . '">' .
                     '    ' . $this->view->translate('Hi') . ' ' . '<a href="/users/view">' .
                     $user->first_name . ' ' . $user->last_name . '</a></li>' .
                     '<li><a href="/users/edit/">' . $this->view->translate('Edit') . '</a></li>' .
                     '<li><a href="/account/logout/">' . $this->view->translate('Log Out') . '</a></li>' .
                     '</ul>';
        } else {
            $lang = '<a href="' . $this->view->url(array('controller' => 'index', 'action' => 'lang', 'lang' => 'en'), 'default', true) . '">english</a> |
					 <a href="' . $this->view->url(array('controller' => 'index', 'action' => 'lang', 'lang' => 'fr'), 'default', true) . '">français</a>';
            $html = '<ul id="headerNav">' . $lang .  '<li title="Anonymous">' . '    ' .
                     $this->view->translate('Hi') . ' Guest' . '</li>' .
                     '<li><a href="/account/signup">' . $this->view->translate('Register') . '</a></li>' .
                     '<li><a href="/account/login">' . $this->view->translate('Log In') . '</a></li>' . '</ul>';
        }
        return $html;
 
   }
}